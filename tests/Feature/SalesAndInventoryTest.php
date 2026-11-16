<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;
use Tests\TestCase;

class SalesAndInventoryTest extends TestCase
{
    use DatabaseTransactions;

    protected SalesService $salesService;

    protected InventoryService $inventoryService;

    protected User $cashier;

    protected ProductVariant $variant;

    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesService = app(SalesService::class);
        $this->inventoryService = app(InventoryService::class);

        $this->cashier = User::create([
            'name' => 'Kasir POS Test',
            'email' => 'kasir_pos_'.uniqid().'@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'name' => 'Kategori Penjualan',
            'slug' => 'cat-sale-'.uniqid(),
        ]);

        $this->product = Product::create([
            'category_id' => $cat->id,
            'sku' => 'POS-PROD-'.uniqid(),
            'name' => 'Dress Pesta POS',
            'selling_price' => 200000,
            'current_hpp' => 120000,
            'min_stock' => 5,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $this->product->id,
            'sku' => 'POS-VAR-'.uniqid(),
            'variant_name' => 'Hitam - M',
            'stock' => 5, // 5 pcs available
            'selling_price' => 200000,
            'current_hpp' => 120000,
        ]);
    }

    public function test_sale_automatically_reduces_stock_and_creates_movement_ledger(): void
    {
        $initialStock = $this->variant->stock; // 5

        $sale = $this->salesService->processSale(
            data: [
                'payment_method' => 'cash',
                'amount_paid' => 400000,
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'unit_price' => 200000,
                ],
            ],
            cashier: $this->cashier
        );

        // Variant stock should decrease from 5 to 3
        $this->assertEquals(3, $this->variant->fresh()->stock);

        // Check stock movement ledger record
        $movement = StockMovement::where('product_variant_id', $this->variant->id)
            ->where('movement_type', 'SALE')
            ->where('reference_id', $sale->id)
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(5, $movement->quantity_before);
        $this->assertEquals(-2, $movement->quantity_change);
        $this->assertEquals(3, $movement->quantity_after);
        $this->assertEquals($this->cashier->id, $movement->user_id);
    }

    public function test_sale_rejected_if_stock_insufficient(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/Stok tidak mencukupi/');

        // Requested 10 when stock is only 5
        $this->salesService->processSale(
            data: [
                'payment_method' => 'cash',
                'amount_paid' => 2000000,
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 10,
                    'unit_price' => 200000,
                ],
            ],
            cashier: $this->cashier
        );
    }

    public function test_sale_rejected_if_payment_amount_less_than_grand_total(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/kurang dari total belanja/');

        // Total is 200.000, but amount paid is only 150.000
        $this->salesService->processSale(
            data: [
                'payment_method' => 'cash',
                'amount_paid' => 150000,
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 200000,
                ],
            ],
            cashier: $this->cashier
        );
    }

    public function test_proportional_transaction_discount_and_profit_calculation(): void
    {
        // 2 items of 200.000 = 400.000 gross
        // Discount: 40.000
        // Net: 360.000
        // Allocated discount per item = 40.000
        // HPP = 2 * 120.000 = 240.000
        // Expected gross profit = 360.000 - 240.000 = 120.000
        // Expected margin = (120.000 / 360.000) * 100 = 33.33%

        $sale = $this->salesService->processSale(
            data: [
                'discount_amount' => 40000,
                'payment_method' => 'cash',
                'amount_paid' => 400000,
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 2,
                    'unit_price' => 200000,
                ],
            ],
            cashier: $this->cashier
        );

        $this->assertEquals(400000.0, (float) $sale->total_amount);
        $this->assertEquals(40000.0, (float) $sale->discount_amount);
        $this->assertEquals(360000.0, (float) $sale->grand_total);
        $this->assertEquals(240000.0, (float) $sale->total_hpp);
        $this->assertEquals(120000.0, (float) $sale->gross_profit);
        $this->assertEquals(33.33, $sale->gross_margin_percentage);

        $item = $sale->items->first();
        $this->assertEquals(40000.0, (float) $item->allocated_transaction_discount);
        $this->assertEquals(360000.0, (float) $item->subtotal);
        $this->assertEquals(120000.0, (float) $item->item_gross_profit);
    }

    public function test_inventory_adjustment_updates_stock_and_logs_audit_movement(): void
    {
        // System stock is 5. Physical opname found only 3 (2 missing/damaged)
        $adjustment = $this->inventoryService->processAdjustment(
            data: [
                'adjustment_date' => today(),
                'type' => 'damage',
                'notes' => 'Pemeriksaan fisik rak display',
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'physical_stock' => 3,
                    'reason' => 'Rusak jahitan robek',
                ],
            ],
            user: $this->cashier
        );

        $this->assertEquals(3, $this->variant->fresh()->stock);

        $movement = StockMovement::where('product_variant_id', $this->variant->id)
            ->where('movement_type', 'ADJUSTMENT_DAMAGE')
            ->first();

        $this->assertNotNull($movement);
        $this->assertEquals(5, $movement->quantity_before);
        $this->assertEquals(-2, $movement->quantity_change);
        $this->assertEquals(3, $movement->quantity_after);
    }
}
