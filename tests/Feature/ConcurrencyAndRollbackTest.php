<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use App\Services\SalesService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ConcurrencyAndRollbackTest extends TestCase
{
    use DatabaseTransactions;

    protected SalesService $salesService;

    protected User $cashier;

    protected ProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->salesService = app(SalesService::class);

        $this->cashier = User::create([
            'name' => 'Kasir Concurrency',
            'email' => 'concurrency_'.uniqid().'@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'kasir',
            'is_active' => true,
        ]);

        $cat = Category::create([
            'name' => 'Kategori Concurrency',
            'slug' => 'cat-conc-'.uniqid(),
        ]);

        $product = Product::create([
            'category_id' => $cat->id,
            'sku' => 'CONC-P-'.uniqid(),
            'name' => 'Gamis Silk Concurrency',
            'selling_price' => 250000,
            'current_hpp' => 150000,
            'min_stock' => 2,
        ]);

        $this->variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'CONC-V-'.uniqid(),
            'variant_name' => 'Standard',
            'stock' => 1, // Only 1 in stock!
            'selling_price' => 250000,
            'current_hpp' => 150000,
        ]);
    }

    public function test_concurrent_or_subsequent_sale_cannot_overdraw_stock(): void
    {
        // First customer buys 1 item (stock becomes 0)
        $sale1 = $this->salesService->processSale(
            data: [
                'payment_method' => 'cash',
                'amount_paid' => 250000,
            ],
            items: [
                [
                    'product_variant_id' => $this->variant->id,
                    'quantity' => 1,
                    'unit_price' => 250000,
                ],
            ],
            cashier: $this->cashier
        );

        $this->assertEquals(0, $this->variant->fresh()->stock);

        // Second customer attempts to buy the same item immediately
        $secondSaleFailed = false;
        try {
            $this->salesService->processSale(
                data: [
                    'payment_method' => 'cash',
                    'amount_paid' => 250000,
                ],
                items: [
                    [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 1,
                        'unit_price' => 250000,
                    ],
                ],
                cashier: $this->cashier
            );
        } catch (\InvalidArgumentException $e) {
            $secondSaleFailed = true;
            $this->assertStringContainsString('Stok tidak mencukupi', $e->getMessage());
        }

        $this->assertTrue($secondSaleFailed, 'Second sale must fail when stock is zero.');
        $this->assertEquals(0, $this->variant->fresh()->stock);
    }

    public function test_database_transaction_rolls_back_completely_on_failure(): void
    {
        $salesCountBefore = Sale::count();
        $movementsCountBefore = StockMovement::count();
        $stockBefore = $this->variant->stock;

        try {
            // Force an error by passing invalid amount paid
            $this->salesService->processSale(
                data: [
                    'payment_method' => 'cash',
                    'amount_paid' => 100000, // Insufficient: requires 250.000
                ],
                items: [
                    [
                        'product_variant_id' => $this->variant->id,
                        'quantity' => 1,
                        'unit_price' => 250000,
                    ],
                ],
                cashier: $this->cashier
            );
        } catch (\InvalidArgumentException $e) {
            // Expected
        }

        // Entire transaction must have rolled back
        $this->assertEquals($salesCountBefore, Sale::count());
        $this->assertEquals($movementsCountBefore, StockMovement::count());
        $this->assertEquals($stockBefore, $this->variant->fresh()->stock);
    }
}
