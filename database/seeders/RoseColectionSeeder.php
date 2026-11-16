<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\Supplier;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\SalesService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RoseColectionSeeder extends Seeder
{
    public function run(): void
    {
        $inventoryService = app(InventoryService::class);
        $salesService = app(SalesService::class);

        // 1. Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@rosecolection.com'],
            [
                'name' => 'Owner Rose',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'phone' => '08114455667',
                'is_active' => true,
            ]
        );

        $kasir = User::firstOrCreate(
            ['email' => 'kasir@rosecolection.com'],
            [
                'name' => 'Kasir Rose',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'phone' => '08129988776',
                'is_active' => true,
            ]
        );

        // 2. Settings
        $settings = [
            'store_name' => 'Rose Colection',
            'store_tagline' => 'Boutique & Modern Fashion',
            'store_address' => 'Jl. Boulevard No. 18, Panakkukang, Makassar',
            'store_phone' => '0812-3456-7890',
            'store_email' => 'info@rosecolection.com',
            'receipt_footer' => "Terima kasih telah berbelanja di Rose Colection!\nBarang yang sudah dibeli dapat ditukar maksimal 2 hari.",
            'currency' => 'IDR',
            'timezone' => 'Asia/Makassar',
            'min_stock_alert_threshold' => '5',
        ];

        foreach ($settings as $key => $val) {
            Setting::set($key, $val);
        }

        // 3. Suppliers
        $suppliers = [
            [
                'name' => 'Konveksi Hijab & Busana Bandung',
                'contact_person' => 'Ibu Siti',
                'phone' => '081322001122',
                'email' => 'bandung@konveksi.id',
                'address' => 'Jl. Cigondewah No. 45, Bandung',
            ],
            [
                'name' => 'Supplier Tekstil & Fashion Surabaya',
                'contact_person' => 'Bpk. Hendra',
                'phone' => '081233445566',
                'email' => 'hendra@tekstilsby.com',
                'address' => 'Komplek Pergudangan Rungkut Blok B3, Surabaya',
            ],
            [
                'name' => 'Grosir Aksesoris Jakarta Pusat',
                'contact_person' => 'Ci Linda',
                'phone' => '081877665544',
                'email' => 'linda@grosirjkt.com',
                'address' => 'Pasar Tanah Abang Blok A Lt. 3, Jakarta',
            ],
        ];

        $supplierModels = [];
        foreach ($suppliers as $s) {
            $supplierModels[] = Supplier::firstOrCreate(['name' => $s['name']], $s);
        }

        // 4. Categories
        $categoriesData = [
            ['name' => 'Dress', 'description' => 'Gamis, Midi Dress, Maxy Dress modis elegan'],
            ['name' => 'Blouse', 'description' => 'Kemeja wanita, tunik, dan atasan kasual & formal'],
            ['name' => 'Celana', 'description' => 'Kulot, Straight Pants, dan rok plisket'],
            ['name' => 'Hijab', 'description' => 'Pashmina silk, segiempat voal premium'],
            ['name' => 'Aksesoris', 'description' => 'Tas wanita, brooch mewah, ikat pinggang'],
        ];

        $categoryModels = [];
        foreach ($categoriesData as $c) {
            $categoryModels[$c['name']] = Category::firstOrCreate(
                ['slug' => Str::slug($c['name'])],
                ['name' => $c['name'], 'description' => $c['description']]
            );
        }

        // 5. Products & Variants Catalog
        $productsSeed = [
            // Dress
            [
                'category' => 'Dress',
                'supplier_idx' => 0,
                'sku' => 'DRS-001',
                'barcode' => '899100100001',
                'name' => 'Floral Silk Maxi Dress',
                'brand' => 'Rose Signature',
                'unit' => 'pcs',
                'selling_price' => 285000,
                'min_stock' => 5,
                'description' => 'Maxi dress berbahan silk premium dengan motif floral lembut, cocok untuk pesta atau acara formal.',
                'variants' => [
                    ['name' => 'Pink - S', 'size' => 'S', 'color' => 'Pink', 'price' => 285000, 'in_qty' => 15, 'buy_price' => 180000],
                    ['name' => 'Pink - M', 'size' => 'M', 'color' => 'Pink', 'price' => 285000, 'in_qty' => 20, 'buy_price' => 180000],
                    ['name' => 'Pink - L', 'size' => 'L', 'color' => 'Pink', 'price' => 285000, 'in_qty' => 15, 'buy_price' => 180000],
                    ['name' => 'Burgundy - M', 'size' => 'M', 'color' => 'Burgundy', 'price' => 295000, 'in_qty' => 12, 'buy_price' => 185000],
                ],
            ],
            [
                'category' => 'Dress',
                'supplier_idx' => 0,
                'sku' => 'DRS-002',
                'barcode' => '899100100002',
                'name' => 'Zahra Abaya French Khimar',
                'brand' => 'Rose Syari',
                'unit' => 'set',
                'selling_price' => 375000,
                'min_stock' => 4,
                'description' => 'Set abaya longgar syari bahan crinkle airflow jatuh dan adem seharian.',
                'variants' => [
                    ['name' => 'Hitam - All Size', 'size' => 'All Size', 'color' => 'Hitam', 'price' => 375000, 'in_qty' => 18, 'buy_price' => 240000],
                    ['name' => 'Mocca - All Size', 'size' => 'All Size', 'color' => 'Mocca', 'price' => 375000, 'in_qty' => 14, 'buy_price' => 240000],
                    ['name' => 'Sage - All Size', 'size' => 'All Size', 'color' => 'Sage', 'price' => 375000, 'in_qty' => 10, 'buy_price' => 240000],
                ],
            ],
            [
                'category' => 'Dress',
                'supplier_idx' => 1,
                'sku' => 'DRS-003',
                'barcode' => '899100100003',
                'name' => 'Tiered Linen Midi Dress',
                'brand' => 'Rose Casual',
                'unit' => 'pcs',
                'selling_price' => 235000,
                'min_stock' => 5,
                'description' => 'Midi dress susun berbahan linen katun premium dengan saku samping.',
                'variants' => [
                    ['name' => 'Broken White - S', 'size' => 'S', 'color' => 'Putih', 'price' => 235000, 'in_qty' => 10, 'buy_price' => 145000],
                    ['name' => 'Broken White - M', 'size' => 'M', 'color' => 'Putih', 'price' => 235000, 'in_qty' => 15, 'buy_price' => 145000],
                    ['name' => 'Dusty Rose - M', 'size' => 'M', 'color' => 'Pink', 'price' => 235000, 'in_qty' => 12, 'buy_price' => 145000],
                ],
            ],

            // Blouse
            [
                'category' => 'Blouse',
                'supplier_idx' => 1,
                'sku' => 'BLS-001',
                'barcode' => '899100200001',
                'name' => 'Geometric Jacquard Blouse',
                'brand' => 'Rose Modern',
                'unit' => 'pcs',
                'selling_price' => 195000,
                'min_stock' => 6,
                'description' => 'Blouse jacquard bertekstur geometris dengan aksen kancing mutiara.',
                'variants' => [
                    ['name' => 'Putih - S', 'size' => 'S', 'color' => 'Putih', 'price' => 195000, 'in_qty' => 20, 'buy_price' => 115000],
                    ['name' => 'Putih - M', 'size' => 'M', 'color' => 'Putih', 'price' => 195000, 'in_qty' => 25, 'buy_price' => 115000],
                    ['name' => 'Burgundy - M', 'size' => 'M', 'color' => 'Burgundy', 'price' => 195000, 'in_qty' => 18, 'buy_price' => 115000],
                    ['name' => 'Burgundy - L', 'size' => 'L', 'color' => 'Burgundy', 'price' => 195000, 'in_qty' => 15, 'buy_price' => 115000],
                ],
            ],
            [
                'category' => 'Blouse',
                'supplier_idx' => 1,
                'sku' => 'BLS-002',
                'barcode' => '899100200002',
                'name' => 'Cotton Linen Oversize Shirt',
                'brand' => 'Rose Casual',
                'unit' => 'pcs',
                'selling_price' => 175000,
                'min_stock' => 5,
                'description' => 'Kemeja oversize berbahan katun linen alami yang sejuk dan breathable.',
                'variants' => [
                    ['name' => 'Sky Blue - M', 'size' => 'M', 'color' => 'Biru', 'price' => 175000, 'in_qty' => 15, 'buy_price' => 105000],
                    ['name' => 'Sky Blue - L', 'size' => 'L', 'color' => 'Biru', 'price' => 175000, 'in_qty' => 12, 'buy_price' => 105000],
                    ['name' => 'Olive Green - M', 'size' => 'M', 'color' => 'Hijau', 'price' => 175000, 'in_qty' => 15, 'buy_price' => 105000],
                ],
            ],
            [
                'category' => 'Blouse',
                'supplier_idx' => 0,
                'sku' => 'BLS-003',
                'barcode' => '899100200003',
                'name' => 'Ruffle Sleeve Silk Tunik',
                'brand' => 'Rose Signature',
                'unit' => 'pcs',
                'selling_price' => 210000,
                'min_stock' => 4,
                'description' => 'Tunik panjang dengan aksen kerut ruffle pada lengan yang feminin.',
                'variants' => [
                    ['name' => 'Rose Gold - S', 'size' => 'S', 'color' => 'Pink', 'price' => 210000, 'in_qty' => 10, 'buy_price' => 130000],
                    ['name' => 'Rose Gold - M', 'size' => 'M', 'color' => 'Pink', 'price' => 210000, 'in_qty' => 14, 'buy_price' => 130000],
                    ['name' => 'Black Diamond - M', 'size' => 'M', 'color' => 'Hitam', 'price' => 210000, 'in_qty' => 12, 'buy_price' => 130000],
                ],
            ],

            // Celana & Bawahan
            [
                'category' => 'Celana',
                'supplier_idx' => 1,
                'sku' => 'CLN-001',
                'barcode' => '899100300001',
                'name' => 'Highwaist Loose Kulot Pants',
                'brand' => 'Rose Modern',
                'unit' => 'pcs',
                'selling_price' => 165000,
                'min_stock' => 6,
                'description' => 'Celana kulot bahan semi-wool berpotongan lebar yang membuat siluet jenjang.',
                'variants' => [
                    ['name' => 'Black - M', 'size' => 'M', 'color' => 'Hitam', 'price' => 165000, 'in_qty' => 25, 'buy_price' => 95000],
                    ['name' => 'Black - L', 'size' => 'L', 'color' => 'Hitam', 'price' => 165000, 'in_qty' => 20, 'buy_price' => 95000],
                    ['name' => 'Black - XL', 'size' => 'XL', 'color' => 'Hitam', 'price' => 165000, 'in_qty' => 15, 'buy_price' => 95000],
                    ['name' => 'Latte - M', 'size' => 'M', 'color' => 'Mocca', 'price' => 165000, 'in_qty' => 18, 'buy_price' => 95000],
                    ['name' => 'Latte - L', 'size' => 'L', 'color' => 'Mocca', 'price' => 165000, 'in_qty' => 15, 'buy_price' => 95000],
                ],
            ],
            [
                'category' => 'Celana',
                'supplier_idx' => 1,
                'sku' => 'CLN-002',
                'barcode' => '899100300002',
                'name' => 'Straight Fit Denim Pants',
                'brand' => 'Rose Casual',
                'unit' => 'pcs',
                'selling_price' => 220000,
                'min_stock' => 5,
                'description' => 'Jeans washed premium dengan potongan lurus nyaman sepanjang hari.',
                'variants' => [
                    ['name' => 'Washed Denim - 28', 'size' => '28', 'color' => 'Biru', 'price' => 220000, 'in_qty' => 12, 'buy_price' => 135000],
                    ['name' => 'Washed Denim - 29', 'size' => '29', 'color' => 'Biru', 'price' => 220000, 'in_qty' => 15, 'buy_price' => 135000],
                    ['name' => 'Washed Denim - 30', 'size' => '30', 'color' => 'Biru', 'price' => 220000, 'in_qty' => 15, 'buy_price' => 135000],
                ],
            ],
            [
                'category' => 'Celana',
                'supplier_idx' => 1,
                'sku' => 'CLN-003',
                'barcode' => '899100300003',
                'name' => 'Pleated Flare Skirt',
                'brand' => 'Rose Modern',
                'unit' => 'pcs',
                'selling_price' => 185000,
                'min_stock' => 4,
                'description' => 'Rok plisket premium dengan lipatan rapi tidak mudah pudar atau kusut.',
                'variants' => [
                    ['name' => 'Rose Gold - All Size', 'size' => 'All Size', 'color' => 'Pink', 'price' => 185000, 'in_qty' => 16, 'buy_price' => 110000],
                    ['name' => 'Charcoal - All Size', 'size' => 'All Size', 'color' => 'Hitam', 'price' => 185000, 'in_qty' => 14, 'buy_price' => 110000],
                ],
            ],

            // Hijab
            [
                'category' => 'Hijab',
                'supplier_idx' => 0,
                'sku' => 'HJB-001',
                'barcode' => '899100400001',
                'name' => 'Pashmina Silk Cradle Glow',
                'brand' => 'Rose Hijab',
                'unit' => 'pcs',
                'selling_price' => 85000,
                'min_stock' => 10,
                'description' => 'Pashmina silk bertekstur dengan kilau anggun natural dan mudah dibentuk.',
                'variants' => [
                    ['name' => 'Blush Pink', 'size' => '180x75', 'color' => 'Pink', 'price' => 85000, 'in_qty' => 30, 'buy_price' => 48000],
                    ['name' => 'Burgundy Wine', 'size' => '180x75', 'color' => 'Burgundy', 'price' => 85000, 'in_qty' => 25, 'buy_price' => 48000],
                    ['name' => 'Pearl White', 'size' => '180x75', 'color' => 'Putih', 'price' => 85000, 'in_qty' => 25, 'buy_price' => 48000],
                    ['name' => 'Sage Frost', 'size' => '180x75', 'color' => 'Sage', 'price' => 85000, 'in_qty' => 20, 'buy_price' => 48000],
                ],
            ],
            [
                'category' => 'Hijab',
                'supplier_idx' => 0,
                'sku' => 'HJB-002',
                'barcode' => '899100400002',
                'name' => 'Segiempat Voal Ultrafine Laser Cut',
                'brand' => 'Rose Hijab',
                'unit' => 'pcs',
                'selling_price' => 75000,
                'min_stock' => 10,
                'description' => 'Hijab segiempat voal ultrafine finishing lasercut rapi, tegak di dahi sepanjang hari.',
                'variants' => [
                    ['name' => 'Rose Nude', 'size' => '115x115', 'color' => 'Pink', 'price' => 75000, 'in_qty' => 40, 'buy_price' => 42000],
                    ['name' => 'Midnight Black', 'size' => '115x115', 'color' => 'Hitam', 'price' => 75000, 'in_qty' => 35, 'buy_price' => 42000],
                    ['name' => 'Warm Cream', 'size' => '115x115', 'color' => 'Mocca', 'price' => 75000, 'in_qty' => 30, 'buy_price' => 42000],
                ],
            ],
            [
                'category' => 'Hijab',
                'supplier_idx' => 0,
                'sku' => 'HJB-003',
                'barcode' => '899100400003',
                'name' => 'Instant Bergo Maryam Diamond',
                'brand' => 'Rose Hijab',
                'unit' => 'pcs',
                'selling_price' => 55000,
                'min_stock' => 8,
                'description' => 'Bergo instan tali belakang bahan diamond crepe stretch ringan dan anti gerah.',
                'variants' => [
                    ['name' => 'Dusty Pink', 'size' => 'M', 'color' => 'Pink', 'price' => 55000, 'in_qty' => 30, 'buy_price' => 30000],
                    ['name' => 'Hitam', 'size' => 'M', 'color' => 'Hitam', 'price' => 55000, 'in_qty' => 30, 'buy_price' => 30000],
                ],
            ],

            // Aksesoris
            [
                'category' => 'Aksesoris',
                'supplier_idx' => 2,
                'sku' => 'AKS-001',
                'barcode' => '899100500001',
                'name' => 'Fabric Minimalist Shoulder Bag',
                'brand' => 'Rose Accs',
                'unit' => 'pcs',
                'selling_price' => 189000,
                'min_stock' => 3,
                'description' => 'Tas bahu minimalis kanvas premium furing satin dengan resleting kuat.',
                'variants' => [
                    ['name' => 'Cream Khaki', 'size' => 'Medium', 'color' => 'Mocca', 'price' => 189000, 'in_qty' => 12, 'buy_price' => 110000],
                    ['name' => 'Deep Rose', 'size' => 'Medium', 'color' => 'Pink', 'price' => 189000, 'in_qty' => 10, 'buy_price' => 110000],
                ],
            ],
            [
                'category' => 'Aksesoris',
                'supplier_idx' => 2,
                'sku' => 'AKS-002',
                'barcode' => '899100500002',
                'name' => 'Crystal Rose Gold Brooch',
                'brand' => 'Rose Accs',
                'unit' => 'pcs',
                'selling_price' => 65000,
                'min_stock' => 5,
                'description' => 'Bros hijab kristal zirkon lapis emas mawar mewah tidak mudah berkarat.',
                'variants' => [
                    ['name' => 'Rose Gold Crystal', 'size' => 'Standard', 'color' => 'Pink', 'price' => 65000, 'in_qty' => 25, 'buy_price' => 32000],
                    ['name' => 'Silver Crystal', 'size' => 'Standard', 'color' => 'Putih', 'price' => 65000, 'in_qty' => 20, 'buy_price' => 32000],
                ],
            ],
            [
                'category' => 'Aksesoris',
                'supplier_idx' => 2,
                'sku' => 'AKS-003',
                'barcode' => '899100500003',
                'name' => 'Leather Slim Waist Belt',
                'brand' => 'Rose Accs',
                'unit' => 'pcs',
                'selling_price' => 79000,
                'min_stock' => 4,
                'description' => 'Ikat pinggang wanita kulit sintetis halus dengan buckle emas elegan.',
                'variants' => [
                    ['name' => 'Black Gold Buckle', 'size' => 'Free', 'color' => 'Hitam', 'price' => 79000, 'in_qty' => 15, 'buy_price' => 42000],
                    ['name' => 'Brown Gold Buckle', 'size' => 'Free', 'color' => 'Mocca', 'price' => 79000, 'in_qty' => 15, 'buy_price' => 42000],
                ],
            ],
            [
                'category' => 'Aksesoris',
                'supplier_idx' => 2,
                'sku' => 'AKS-004',
                'barcode' => '899100500004',
                'name' => 'Scrunchie Silk Jumbo Premium',
                'brand' => 'Rose Accs',
                'unit' => 'pcs',
                'selling_price' => 25000,
                'min_stock' => 10,
                'description' => 'Ikat rambut scrunchie jumbo bahan satin silk tidak membuat pusing dan rambut rontok.',
                'variants' => [
                    ['name' => 'Rose Dust', 'size' => 'Jumbo', 'color' => 'Pink', 'price' => 25000, 'in_qty' => 40, 'buy_price' => 11000],
                    ['name' => 'Champagne', 'size' => 'Jumbo', 'color' => 'Putih', 'price' => 25000, 'in_qty' => 35, 'buy_price' => 11000],
                ],
            ],
        ];

        // 6. Insert Products, Variants, and Receive Initial Stocks via InventoryService to calculate HPP!
        $allCreatedVariants = [];

        foreach ($productsSeed as $pData) {
            $cat = $categoryModels[$pData['category']];
            $supp = $supplierModels[$pData['supplier_idx']];

            $product = Product::create([
                'category_id' => $cat->id,
                'supplier_id' => $supp->id,
                'sku' => $pData['sku'],
                'barcode' => $pData['barcode'],
                'name' => $pData['name'],
                'slug' => Str::slug($pData['name'].'-'.$pData['sku']),
                'brand' => $pData['brand'],
                'unit' => $pData['unit'],
                'selling_price' => $pData['selling_price'],
                'current_hpp' => 0,
                'min_stock' => $pData['min_stock'],
                'description' => $pData['description'],
                'is_active' => true,
            ]);

            $purchaseItems = [];

            foreach ($pData['variants'] as $vIdx => $vData) {
                $variantSku = $pData['sku'].'-V'.($vIdx + 1);
                $variantBarcode = $pData['barcode'].($vIdx + 1);

                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $variantSku,
                    'barcode' => $variantBarcode,
                    'variant_name' => $vData['name'],
                    'size' => $vData['size'] ?? null,
                    'color' => $vData['color'] ?? null,
                    'stock' => 0, // initially 0, will receive through purchase below
                    'selling_price' => $vData['price'],
                    'current_hpp' => $vData['buy_price'],
                ]);

                $allCreatedVariants[] = $variant;

                $purchaseItems[] = [
                    'product_variant_id' => $variant->id,
                    'quantity' => $vData['in_qty'],
                    'purchase_price' => $vData['buy_price'],
                    'discount_amount' => 0,
                ];
            }

            // Create initial purchase record via InventoryService to properly calculate HPP and establish ledger
            $inventoryService->processPurchase(
                data: [
                    'supplier_id' => $supp->id,
                    'purchase_date' => now()->subDays(rand(5, 20))->toDateString(),
                    'discount_amount' => 0,
                    'additional_cost' => 15000, // standard shipping fee allocated
                    'notes' => "Stok awal penerimaan barang produk {$product->name}",
                ],
                items: $purchaseItems,
                user: $admin
            );
        }

        // 7. Generate a few realistic sales via SalesService
        $salesToCreate = [
            [
                'days_ago' => 2,
                'customer' => 'Nurul Aulia',
                'discount' => 10000,
                'payment_method' => 'cash',
                'amount_paid' => 500000,
                'items' => [
                    ['variant_index' => 0, 'qty' => 1], // Floral silk pink S
                    ['variant_index' => 15, 'qty' => 1], // Pashmina blush pink
                ],
            ],
            [
                'days_ago' => 1,
                'customer' => 'Ibu Rahmawati',
                'discount' => 0,
                'payment_method' => 'qris',
                'amount_paid' => 375000,
                'items' => [
                    ['variant_index' => 4, 'qty' => 1], // Zahra Abaya Hitam
                ],
            ],
            [
                'days_ago' => 0, // Today
                'customer' => 'Siti Khadijah',
                'discount' => 15000,
                'payment_method' => 'transfer',
                'amount_paid' => 415000,
                'items' => [
                    ['variant_index' => 7, 'qty' => 1], // Jacquard Blouse Putih M
                    ['variant_index' => 11, 'qty' => 1], // Highwaist Kulot Black M
                ],
            ],
            [
                'days_ago' => 0, // Today
                'customer' => 'Dinda Maharani',
                'discount' => 0,
                'payment_method' => 'cash',
                'amount_paid' => 500000,
                'items' => [
                    ['variant_index' => 19, 'qty' => 2], // items
                ],
            ],
        ];

        foreach ($salesToCreate as $sOrder) {
            $orderItems = [];
            foreach ($sOrder['items'] as $itemRef) {
                if (isset($allCreatedVariants[$itemRef['variant_index']])) {
                    $v = $allCreatedVariants[$itemRef['variant_index']];
                    $orderItems[] = [
                        'product_variant_id' => $v->id,
                        'quantity' => $itemRef['qty'],
                        'unit_price' => $v->selling_price,
                        'unit_discount' => 0,
                    ];
                }
            }

            if (! empty($orderItems)) {
                $salesService->processSale(
                    data: [
                        'sale_date' => now()->subDays($sOrder['days_ago'])->toDateString(),
                        'discount_amount' => $sOrder['discount'],
                        'customer_name' => $sOrder['customer'],
                        'payment_method' => $sOrder['payment_method'],
                        'amount_paid' => $sOrder['amount_paid'],
                        'notes' => 'Transaksi kasir '.$sOrder['customer'],
                    ],
                    items: $orderItems,
                    cashier: $kasir
                );
            }
        }
    }
}
