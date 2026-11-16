<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $query = Product::with(['category', 'supplier', 'variants']);

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $products = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('products.index', compact('products', 'categories', 'suppliers'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.create', compact('categories', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'sku' => ['required', 'string', 'max:50', 'unique:products,sku'],
            'barcode' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            // Variants validation
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.variant_name' => ['required', 'string', 'max:100'],
            'variants.*.sku' => ['required', 'string', 'max:50', 'distinct', 'unique:product_variants,sku'],
            'variants.*.barcode' => ['nullable', 'string', 'max:50'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.selling_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request) {
            $product = Product::create([
                'category_id' => $validated['category_id'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'sku' => strtoupper($validated['sku']),
                'barcode' => $validated['barcode'] ?? null,
                'name' => $validated['name'],
                'slug' => Str::slug($validated['name'].'-'.$validated['sku']),
                'brand' => $validated['brand'] ?? 'Rose Signature',
                'unit' => $validated['unit'] ?? 'pcs',
                'selling_price' => $validated['selling_price'],
                'current_hpp' => 0,
                'min_stock' => $validated['min_stock'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            foreach ($validated['variants'] as $v) {
                ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => strtoupper($v['sku']),
                    'barcode' => $v['barcode'] ?? null,
                    'variant_name' => $v['variant_name'],
                    'size' => $v['size'] ?? null,
                    'color' => $v['color'] ?? null,
                    'stock' => 0, // Stock starts at 0, must be received via Barang Masuk
                    'selling_price' => ! empty($v['selling_price']) ? $v['selling_price'] : $product->selling_price,
                    'current_hpp' => 0,
                ]);
            }
        });

        return redirect()->route('products.index')->with('success', 'Produk dan varian baru berhasil ditambahkan.');
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'supplier', 'variants']);
        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'suppliers'));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'sku' => ['required', 'string', 'max:50', Rule::unique('products', 'sku')->ignore($product->id)],
            'barcode' => ['nullable', 'string', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'brand' => ['nullable', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'min_stock' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],

            // Existing & new variants
            'variants' => ['required', 'array', 'min:1'],
            'variants.*.id' => ['nullable', 'exists:product_variants,id'],
            'variants.*.variant_name' => ['required', 'string', 'max:100'],
            'variants.*.sku' => ['required', 'string', 'max:50'],
            'variants.*.barcode' => ['nullable', 'string', 'max:50'],
            'variants.*.size' => ['nullable', 'string', 'max:50'],
            'variants.*.color' => ['nullable', 'string', 'max:50'],
            'variants.*.selling_price' => ['nullable', 'numeric', 'min:0'],
        ]);

        DB::transaction(function () use ($validated, $request, $product) {
            $product->update([
                'category_id' => $validated['category_id'],
                'supplier_id' => $validated['supplier_id'] ?? null,
                'sku' => strtoupper($validated['sku']),
                'barcode' => $validated['barcode'] ?? null,
                'name' => $validated['name'],
                'brand' => $validated['brand'] ?? 'Rose Signature',
                'unit' => $validated['unit'] ?? 'pcs',
                'selling_price' => $validated['selling_price'],
                'min_stock' => $validated['min_stock'],
                'description' => $validated['description'] ?? null,
                'is_active' => $request->boolean('is_active', true),
            ]);

            $submittedVariantIds = [];

            foreach ($validated['variants'] as $vData) {
                if (! empty($vData['id'])) {
                    // Update existing variant (preserve its current stock and HPP!)
                    $variant = ProductVariant::where('id', $vData['id'])
                        ->where('product_id', $product->id)
                        ->first();

                    if ($variant) {
                        $variant->update([
                            'variant_name' => $vData['variant_name'],
                            'sku' => strtoupper($vData['sku']),
                            'barcode' => $vData['barcode'] ?? null,
                            'size' => $vData['size'] ?? null,
                            'color' => $vData['color'] ?? null,
                            'selling_price' => ! empty($vData['selling_price']) ? $vData['selling_price'] : $product->selling_price,
                        ]);
                        $submittedVariantIds[] = $variant->id;
                    }
                } else {
                    // Create new variant
                    $newVariant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => strtoupper($vData['sku']),
                        'barcode' => $vData['barcode'] ?? null,
                        'variant_name' => $vData['variant_name'],
                        'size' => $vData['size'] ?? null,
                        'color' => $vData['color'] ?? null,
                        'stock' => 0,
                        'selling_price' => ! empty($vData['selling_price']) ? $vData['selling_price'] : $product->selling_price,
                        'current_hpp' => 0,
                    ]);
                    $submittedVariantIds[] = $newVariant->id;
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Data produk berhasil diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        // Enforce financial and historical integrity rule
        $hasSales = $product->saleItems()->exists();
        $hasPurchases = $product->purchaseItems()->exists();

        if ($hasSales || $hasPurchases) {
            // Cannot delete permanently, archive instead
            $product->update(['is_active' => false]);

            return back()->with('error', 'Produk tidak dapat dihapus permanen karena terdapat riwayat transaksi penjualan/pembelian. Produk berhasil dinonaktifkan (diarsipkan).');
        }

        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }
}
