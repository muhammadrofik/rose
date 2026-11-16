<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    public function __construct(
        protected HppService $hppService
    ) {}

    /**
     * Atomically record a stock movement with row-level locking.
     */
    public function recordMovement(
        int $variantId,
        string $movementType,
        int $quantityChange,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
        float|string $unitCost = 0,
        ?string $notes = null
    ): StockMovement {
        /** @var ProductVariant $variant */
        $variant = ProductVariant::where('id', $variantId)
            ->lockForUpdate()
            ->firstOrFail();

        $before = $variant->stock;
        $after = $before + $quantityChange;

        if ($after < 0) {
            throw new InvalidArgumentException(
                "Stok tidak mencukupi untuk varian {$variant->variant_name} ({$variant->sku}). Stok saat ini: {$before}, dibutuhkan: ".abs($quantityChange)
            );
        }

        $variant->stock = $after;
        $variant->save();

        return StockMovement::create([
            'product_variant_id' => $variantId,
            'movement_type' => $movementType,
            'quantity_before' => $before,
            'quantity_change' => $quantityChange,
            'quantity_after' => $after,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'user_id' => $userId,
            'unit_cost' => $unitCost,
            'notes' => $notes,
            'created_at' => now(),
        ]);
    }

    /**
     * Process incoming purchase (Barang Masuk) with Moving Average HPP and Stock updates.
     */
    public function processPurchase(
        array $data,
        array $items,
        User $user
    ): Purchase {
        return DB::transaction(function () use ($data, $items, $user) {
            // Generate PO number: PO-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $countToday = Purchase::whereDate('created_at', today())->count() + 1;
            $purchaseNumber = 'PO-'.$datePrefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $discountAmount = (float) ($data['discount_amount'] ?? 0);
            $additionalCost = (float) ($data['additional_cost'] ?? 0);

            // Compute total item purchase prices to proportionally allocate additional costs
            $grossItemsTotal = 0;
            foreach ($items as $item) {
                $grossItemsTotal += ((float) $item['purchase_price'] * (int) $item['quantity']) - (float) ($item['discount_amount'] ?? 0);
            }

            if ($grossItemsTotal <= 0) {
                $grossItemsTotal = 1; // avoid division by zero
            }

            $purchase = Purchase::create([
                'purchase_number' => $purchaseNumber,
                'supplier_id' => $data['supplier_id'],
                'user_id' => $user->id,
                'purchase_date' => $data['purchase_date'] ?? today(),
                'status' => 'received',
                'total_items_cost' => $grossItemsTotal,
                'discount_amount' => $discountAmount,
                'additional_cost' => $additionalCost,
                'grand_total' => max(0, $grossItemsTotal - $discountAmount + $additionalCost),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $itemData) {
                $variant = ProductVariant::findOrFail($itemData['product_variant_id']);
                $qty = (int) $itemData['quantity'];
                $price = (float) $itemData['purchase_price'];
                $itemDiscount = (float) ($itemData['discount_amount'] ?? 0);

                $itemSubtotalBeforeFee = ($price * $qty) - $itemDiscount;
                // Proportionally allocate global additional costs (like shipping)
                $allocatedFee = ($itemSubtotalBeforeFee / $grossItemsTotal) * $additionalCost;
                // Net cost per unit
                $unitNetCost = ($itemSubtotalBeforeFee + $allocatedFee) / ($qty > 0 ? $qty : 1);
                $subtotal = $unitNetCost * $qty;

                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'purchase_price' => $price,
                    'discount_amount' => $itemDiscount,
                    'allocated_additional_cost' => round($allocatedFee, 2),
                    'unit_net_cost' => round($unitNetCost, 2),
                    'subtotal' => round($subtotal, 2),
                ]);

                // 1. Update HPP via Moving Weighted Average Cost
                $this->hppService->updateHppOnPurchase($variant, $qty, $unitNetCost);

                // 2. Increase stock & record stock movement
                $this->recordMovement(
                    variantId: $variant->id,
                    movementType: 'PURCHASE_RECEIPT',
                    quantityChange: $qty,
                    referenceType: Purchase::class,
                    referenceId: $purchase->id,
                    userId: $user->id,
                    unitCost: $unitNetCost,
                    notes: "Penerimaan pembelian {$purchase->purchase_number}"
                );
            }

            return $purchase;
        });
    }

    /**
     * Process Inventory Adjustment (Stock Opname / Penyesuaian Stok).
     */
    public function processAdjustment(
        array $data,
        array $items,
        User $user
    ): InventoryAdjustment {
        return DB::transaction(function () use ($data, $items, $user) {
            $datePrefix = date('Ymd');
            $countToday = InventoryAdjustment::whereDate('created_at', today())->count() + 1;
            $adjustmentNumber = 'ADJ-'.$datePrefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            $adjustment = InventoryAdjustment::create([
                'adjustment_number' => $adjustmentNumber,
                'user_id' => $user->id,
                'adjustment_date' => $data['adjustment_date'] ?? today(),
                'type' => $data['type'] ?? 'correction',
                'notes' => $data['notes'] ?? null,
            ]);

            $movementType = match ($adjustment->type) {
                'damage' => 'ADJUSTMENT_DAMAGE',
                'lost' => 'ADJUSTMENT_LOST',
                'internal_use' => 'ADJUSTMENT_INTERNAL',
                default => 'ADJUSTMENT_CORRECTION',
            };

            foreach ($items as $itemData) {
                $variant = ProductVariant::where('id', $itemData['product_variant_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                $systemStock = (int) $variant->stock;
                $physicalStock = (int) $itemData['physical_stock'];
                $diff = $physicalStock - $systemStock;

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'product_variant_id' => $variant->id,
                    'system_stock' => $systemStock,
                    'physical_stock' => $physicalStock,
                    'difference' => $diff,
                    'unit_cost' => $variant->current_hpp,
                    'reason' => $itemData['reason'] ?? $adjustment->notes,
                ]);

                if ($diff !== 0) {
                    $this->recordMovement(
                        variantId: $variant->id,
                        movementType: $movementType,
                        quantityChange: $diff,
                        referenceType: InventoryAdjustment::class,
                        referenceId: $adjustment->id,
                        userId: $user->id,
                        unitCost: $variant->current_hpp,
                        notes: "Penyesuaian stok {$adjustment->adjustment_number}: {$itemData['reason']}"
                    );
                }
            }

            return $adjustment;
        });
    }
}
