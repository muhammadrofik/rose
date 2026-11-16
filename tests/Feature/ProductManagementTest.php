<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $admin;

    protected Category $category;

    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Product Test',
            'email' => 'admin_prod_'.uniqid().'@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Dress Test',
            'slug' => 'dress-test-'.uniqid(),
            'description' => 'Testing Dress',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier Test '.uniqid(),
            'contact_person' => 'Budi',
            'phone' => '0812345678',
        ]);
    }

    public function test_can_create_product_with_variants(): void
    {
        $this->actingAs($this->admin);

        $sku = 'TEST-'.strtoupper(uniqid());

        $response = $this->post(route('products.store'), [
            'name' => 'Abaya Silk Test',
            'category_id' => $this->category->id,
            'supplier_id' => $this->supplier->id,
            'sku' => $sku,
            'barcode' => '89988776655',
            'brand' => 'Rose Test',
            'unit' => 'pcs',
            'selling_price' => 350000,
            'min_stock' => 5,
            'description' => 'Bahan lembut',
            'is_active' => 1,
            'variants' => [
                [
                    'variant_name' => 'Hitam - M',
                    'sku' => $sku.'-BLK-M',
                    'barcode' => '899887766551',
                    'size' => 'M',
                    'color' => 'Hitam',
                    'selling_price' => 350000,
                ],
                [
                    'variant_name' => 'Hitam - L',
                    'sku' => $sku.'-BLK-L',
                    'barcode' => '899887766552',
                    'size' => 'L',
                    'color' => 'Hitam',
                    'selling_price' => 360000,
                ],
            ],
        ]);

        $response->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('products', [
            'sku' => $sku,
            'name' => 'Abaya Silk Test',
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => $sku.'-BLK-M',
            'variant_name' => 'Hitam - M',
            'stock' => 0, // Starts at 0
        ]);

        $this->assertDatabaseHas('product_variants', [
            'sku' => $sku.'-BLK-L',
            'variant_name' => 'Hitam - L',
            'stock' => 0,
        ]);
    }

    public function test_duplicate_product_sku_is_rejected(): void
    {
        $this->actingAs($this->admin);

        $sku = 'DUP-'.strtoupper(uniqid());

        Product::create([
            'category_id' => $this->category->id,
            'sku' => $sku,
            'name' => 'First Product',
            'slug' => 'first-product-'.uniqid(),
            'selling_price' => 100000,
            'min_stock' => 5,
        ]);

        $response = $this->post(route('products.store'), [
            'name' => 'Duplicate Product',
            'category_id' => $this->category->id,
            'sku' => $sku,
            'unit' => 'pcs',
            'selling_price' => 120000,
            'min_stock' => 5,
            'variants' => [
                [
                    'variant_name' => 'Standard',
                    'sku' => $sku.'-V1',
                ],
            ],
        ]);

        $response->assertSessionHasErrors('sku');
    }

    public function test_product_with_sale_transactions_cannot_be_deleted_permanently(): void
    {
        $this->actingAs($this->admin);

        $sku = 'SALETEST-'.strtoupper(uniqid());

        $product = Product::create([
            'category_id' => $this->category->id,
            'sku' => $sku,
            'name' => 'Sold Product',
            'slug' => 'sold-product-'.uniqid(),
            'selling_price' => 150000,
            'min_stock' => 5,
            'is_active' => true,
        ]);

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => $sku.'-V1',
            'variant_name' => 'Standard',
            'stock' => 10,
            'selling_price' => 150000,
            'current_hpp' => 100000,
        ]);

        $sale = Sale::create([
            'invoice_number' => 'INV-TEST-'.uniqid(),
            'user_id' => $this->admin->id,
            'sale_date' => today(),
            'total_amount' => 150000,
            'grand_total' => 150000,
            'total_hpp' => 100000,
            'gross_profit' => 50000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'product_variant_id' => $variant->id,
            'quantity' => 1,
            'unit_price' => 150000,
            'subtotal' => 150000,
            'hpp_snapshot' => 100000,
            'item_gross_profit' => 50000,
        ]);

        $response = $this->delete(route('products.destroy', $product->id));

        // The product should not be deleted, but archived (is_active = false)
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'is_active' => false,
        ]);

        $response->assertSessionHas('error');
    }

    public function test_purchase_reception_page_renders_with_barcode_scanner(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('purchases.create'));

        $response->assertStatus(200);
        $response->assertSee('Scan Barcode Barang untuk Tambah Stok');
        $response->assertSee('handleBarcodeScan()');
    }
}
