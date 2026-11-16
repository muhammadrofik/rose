<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use App\Models\User;
use App\Services\HppService;
use App\Services\InventoryService;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HppCalculationTest extends TestCase
{
    use DatabaseTransactions;

    protected HppService $hppService;

    protected InventoryService $inventoryService;

    protected SalesService $salesService;

    protected User $admin;

    protected Supplier $supplier;

    protected Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->hppService = app(HppService::class);
        $this->inventoryService = app(InventoryService::class);
        $this->salesService = app(SalesService::class);

        $this->admin = User::create([
            'name' => 'Admin HPP Test',
            'email' => 'admin_hpp_'.uniqid().'@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Kategori HPP',
            'slug' => 'cat-hpp-'.uniqid(),
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier HPP',
            'phone' => '081234567',
        ]);
    }

    public function test_moving_weighted_average_formula_calculation(): void
    {
        // Example from prompt specification:
        // Stok lama: 10 pcs, HPP lama: Rp50.000
        // Barang masuk: 10 pcs, Harga perolehan baru: Rp60.000
        // Expected HPP baru = Rp55.000 per pcs.

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'HPP-EX-'.uniqid(),
            'name' => 'Produk Tes Moving Average',
            'selling_price' => 100000,
            'current_hpp' => 50000,
            'min_stock' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HPP-EX-VAR-'.uniqid(),
            'variant_name' => 'Standard',
            'stock' => 10,
            'selling_price' => 100000,
            'current_hpp' => 50000,
        ]);

        $newHpp = $this->hppService->updateHppOnPurchase(
            variant: $variant,
            quantityIn: 10,
            unitNetCost: 60000
        );

        $this->assertEquals(55000.0, $newHpp);
        $this->assertEquals(55000.0, (float) $variant->fresh()->current_hpp);
    }

    public function test_hpp_takes_new_cost_when_current_stock_is_zero(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'HPP-ZERO-'.uniqid(),
            'name' => 'Produk Stok Nol',
            'selling_price' => 150000,
            'current_hpp' => 0,
            'min_stock' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HPP-ZERO-VAR-'.uniqid(),
            'variant_name' => 'Standard',
            'stock' => 0,
            'selling_price' => 150000,
            'current_hpp' => 0,
        ]);

        $newHpp = $this->hppService->updateHppOnPurchase(
            variant: $variant,
            quantityIn: 15,
            unitNetCost: 85000
        );

        $this->assertEquals(85000.0, $newHpp);
        $this->assertEquals(85000.0, (float) $variant->fresh()->current_hpp);
    }

    public function test_purchase_process_allocates_additional_shipping_costs_into_hpp(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'HPP-SHIP-'.uniqid(),
            'name' => 'Produk Ongkir',
            'selling_price' => 200000,
            'current_hpp' => 0,
            'min_stock' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HPP-SHIP-VAR-'.uniqid(),
            'variant_name' => 'Standard',
            'stock' => 0,
            'selling_price' => 200000,
            'current_hpp' => 0,
        ]);

        // 10 pcs at 100.000 = 1.000.000 + 20.000 shipping = 1.020.000 total / 10 = 102.000 net unit cost
        $purchase = $this->inventoryService->processPurchase(
            data: [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => today(),
                'discount_amount' => 0,
                'additional_cost' => 20000,
            ],
            items: [
                [
                    'product_variant_id' => $variant->id,
                    'quantity' => 10,
                    'purchase_price' => 100000,
                    'discount_amount' => 0,
                ],
            ],
            user: $this->admin
        );

        $this->assertEquals(1020000.0, (float) $purchase->grand_total);
        $this->assertEquals(102000.0, (float) $variant->fresh()->current_hpp);
        $this->assertEquals(10, $variant->fresh()->stock);
    }

    public function test_historical_sale_hpp_snapshot_does_not_change_after_new_purchases(): void
    {
        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'HPP-HIST-'.uniqid(),
            'name' => 'Produk Snapshot',
            'selling_price' => 200000,
            'current_hpp' => 100000,
            'min_stock' => 5,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'HPP-HIST-VAR-'.uniqid(),
            'variant_name' => 'Standard',
            'stock' => 10,
            'selling_price' => 200000,
            'current_hpp' => 100000,
        ]);

        // Sale 1 unit at current HPP 100.000
        $sale = $this->salesService->processSale(
            data: [
                'payment_method' => 'cash',
                'amount_paid' => 200000,
            ],
            items: [
                [
                    'product_variant_id' => $variant->id,
                    'quantity' => 1,
                    'unit_price' => 200000,
                ],
            ],
            cashier: $this->admin
        );

        $saleItem = $sale->items->first();
        $this->assertEquals(100000.0, (float) $saleItem->hpp_snapshot);
        $this->assertEquals(100000.0, (float) $saleItem->item_gross_profit);

        // Now, receive a new purchase at a much higher price (e.g. 150.000)
        // Stock remaining was 9, HPP 100.000. New in 10 at 150.000
        $this->inventoryService->processPurchase(
            data: [
                'supplier_id' => $this->supplier->id,
                'purchase_date' => today(),
                'discount_amount' => 0,
                'additional_cost' => 0,
            ],
            items: [
                [
                    'product_variant_id' => $variant->id,
                    'quantity' => 10,
                    'purchase_price' => 150000,
                ],
            ],
            user: $this->admin
        );

        // The variant's current HPP should increase
        $this->assertGreaterThan(100000.0, (float) $variant->fresh()->current_hpp);

        // Crucial test: The past sale snapshot HPP MUST NOT change!
        $this->assertEquals(100000.0, (float) $saleItem->fresh()->hpp_snapshot);
        $this->assertEquals(100000.0, (float) $saleItem->fresh()->item_gross_profit);
    }
}
