<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SalesService
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    /**
     * Process POS sale transaction with row locking, atomic stock reduction,
     * HPP snapshotting, proportional discount allocation, and payment verification.
     *
     * @param  array  $data  ['sale_date', 'discount_amount', 'customer_name', 'notes', 'payment_method', 'amount_paid', 'payment_reference']
     * @param  array  $items  [ ['product_variant_id', 'quantity', 'unit_price', 'unit_discount'] ]
     */
    public function processSale(array $data, array $items, User $cashier): Sale
    {
        if (empty($items)) {
            throw new InvalidArgumentException('Keranjang belanja tidak boleh kosong.');
        }

        return DB::transaction(function () use ($data, $items, $cashier) {
            // 1. Calculate items subtotal and validate items
            $totalAmount = 0.0;
            $preparedItems = [];

            // Sort variant IDs to prevent database deadlocks on concurrent sales
            usort($items, fn ($a, $b) => $a['product_variant_id'] <=> $b['product_variant_id']);

            foreach ($items as $item) {
                $variantId = (int) $item['product_variant_id'];
                $qty = (int) $item['quantity'];

                if ($qty <= 0) {
                    throw new InvalidArgumentException('Jumlah barang harus minimal 1.');
                }

                // Row lock variant for concurrency protection
                /** @var ProductVariant $variant */
                $variant = ProductVariant::with('product')
                    ->where('id', $variantId)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($variant->stock < $qty) {
                    throw new InvalidArgumentException(
                        "Stok tidak mencukupi untuk {$variant->full_name}. Tersedia: {$variant->stock}, diminta: {$qty}."
                    );
                }

                $unitPrice = isset($item['unit_price']) ? (float) $item['unit_price'] : (float) $variant->selling_price;
                $unitDiscount = (float) ($item['unit_discount'] ?? 0);
                $lineTotal = max(0, ($unitPrice - $unitDiscount) * $qty);

                $totalAmount += $lineTotal;

                $preparedItems[] = [
                    'variant' => $variant,
                    'quantity' => $qty,
                    'unit_price' => $unitPrice,
                    'unit_discount' => $unitDiscount,
                    'line_total' => $lineTotal,
                    'hpp_snapshot' => (float) $variant->current_hpp,
                ];
            }

            $transactionDiscount = (float) ($data['discount_amount'] ?? 0);
            if ($transactionDiscount > $totalAmount) {
                $transactionDiscount = $totalAmount;
            }

            $grandTotal = round(max(0, $totalAmount - $transactionDiscount), 2);
            $amountPaid = (float) ($data['amount_paid'] ?? $grandTotal);
            $paymentMethod = $data['payment_method'] ?? 'cash';

            // Verify payment
            if ($amountPaid < $grandTotal) {
                throw new InvalidArgumentException(
                    'Jumlah pembayaran (Rp '.number_format($amountPaid, 0, ',', '.').') kurang dari total belanja (Rp '.number_format($grandTotal, 0, ',', '.').').'
                );
            }

            $changeAmount = round($amountPaid - $grandTotal, 2);

            // Generate invoice number: INV-YYYYMMDD-XXXX
            $datePrefix = date('Ymd');
            $countToday = Sale::whereDate('created_at', today())->count() + 1;
            $invoiceNumber = 'INV-'.$datePrefix.'-'.str_pad((string) $countToday, 4, '0', STR_PAD_LEFT);

            // Create Sale record
            $sale = Sale::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $cashier->id,
                'sale_date' => $data['sale_date'] ?? today(),
                'total_amount' => $totalAmount,
                'discount_amount' => $transactionDiscount,
                'grand_total' => $grandTotal,
                'total_hpp' => 0, // calculated below
                'gross_profit' => 0, // calculated below
                'payment_status' => 'paid',
                'payment_method' => $paymentMethod,
                'customer_name' => $data['customer_name'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $totalHpp = 0.0;
            $totalGrossProfit = 0.0;

            foreach ($preparedItems as $prep) {
                /** @var ProductVariant $variant */
                $variant = $prep['variant'];
                $qty = $prep['quantity'];
                $lineTotal = $prep['line_total'];
                $hppSnapshot = $prep['hpp_snapshot'];

                // Proportional discount allocation
                $allocatedDiscount = $totalAmount > 0
                    ? round(($lineTotal / $totalAmount) * $transactionDiscount, 2)
                    : 0.0;

                $itemNetSubtotal = round($lineTotal - $allocatedDiscount, 2);
                $itemTotalHpp = round($qty * $hppSnapshot, 2);
                $itemGrossProfit = round($itemNetSubtotal - $itemTotalHpp, 2);

                $totalHpp += $itemTotalHpp;
                $totalGrossProfit += $itemGrossProfit;

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $prep['unit_price'],
                    'unit_discount' => $prep['unit_discount'],
                    'allocated_transaction_discount' => $allocatedDiscount,
                    'subtotal' => $itemNetSubtotal,
                    'hpp_snapshot' => $hppSnapshot,
                    'item_gross_profit' => $itemGrossProfit,
                ]);

                // Deduct stock atomically and write to stock movement ledger
                $this->inventoryService->recordMovement(
                    variantId: $variant->id,
                    movementType: 'SALE',
                    quantityChange: -$qty,
                    referenceType: Sale::class,
                    referenceId: $sale->id,
                    userId: $cashier->id,
                    unitCost: $hppSnapshot,
                    notes: "Penjualan faktur {$sale->invoice_number}"
                );
            }

            // Update sale financial totals
            $sale->update([
                'total_hpp' => round($totalHpp, 2),
                'gross_profit' => round($totalGrossProfit, 2),
            ]);

            // Record payment
            Payment::create([
                'sale_id' => $sale->id,
                'payment_method' => $paymentMethod,
                'amount_paid' => $amountPaid,
                'change_amount' => $changeAmount,
                'payment_reference' => $data['payment_reference'] ?? null,
            ]);

            return $sale->load(['items.variant.product', 'items.product', 'payments', 'user']);
        });
    }
}
