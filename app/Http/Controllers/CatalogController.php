<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::withCount('products')->orderBy('name')->get();

        $query = Product::with(['category', 'variants'])
            ->active();

        // Search
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Availability filter
        if ($request->filled('availability')) {
            if ($request->availability === 'in_stock') {
                $query->whereHas('variants', fn ($q) => $q->where('stock', '>', 0));
            } elseif ($request->availability === 'low_stock') {
                $query->whereHas('variants', fn ($q) => $q->where('stock', '>', 0))
                    ->whereRaw('(SELECT SUM(stock) FROM product_variants WHERE product_variants.product_id = products.id) <= products.min_stock');
            } elseif ($request->availability === 'out_of_stock') {
                $query->whereDoesntHave('variants', fn ($q) => $q->where('stock', '>', 0));
            }
        }

        // Sort
        match ($request->get('sort', 'latest')) {
            'price_asc' => $query->orderBy('selling_price', 'asc'),
            'price_desc' => $query->orderBy('selling_price', 'desc'),
            'name_asc' => $query->orderBy('name', 'asc'),
            default => $query->orderBy('id', 'desc'),
        };

        $products = $query->paginate(12)->withQueryString();

        return view('catalog.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        $product->load(['category', 'supplier', 'variants']);
        $relatedProducts = Product::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->active()
            ->limit(4)
            ->get();

        return view('catalog.show', compact('product', 'relatedProducts'));
    }
}
