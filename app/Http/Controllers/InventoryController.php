<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(Request $request): View
    {
        $categories = Category::orderBy('name')->get();

        $query = ProductVariant::with(['product.category', 'product.supplier']);

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('variant_name', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%")
                    ->orWhere('barcode', 'ilike', "%{$term}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'ilike', "%{$term}%"));
            });
        }

        if ($request->filled('category_id')) {
            $query->whereHas('product', fn ($q) => $q->where('category_id', $request->category_id));
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out') {
                $query->where('stock', '<=', 0);
            } elseif ($request->stock_status === 'low') {
                $query->where('stock', '>', 0)
                    ->whereRaw('stock <= (SELECT min_stock FROM products WHERE products.id = product_variants.product_id)');
            } elseif ($request->stock_status === 'safe') {
                $query->whereRaw('stock > (SELECT min_stock FROM products WHERE products.id = product_variants.product_id)');
            }
        }

        $variants = $query->orderBy('stock', 'asc')->paginate(15)->withQueryString();

        $totalUnits = ProductVariant::sum('stock');
        $totalValuation = ProductVariant::all()->reduce(fn ($c, $v) => $c + ($v->stock * (float) $v->current_hpp), 0.0);

        return view('inventory.index', compact('variants', 'categories', 'totalUnits', 'totalValuation'));
    }

    public function movements(Request $request): View
    {
        $query = StockMovement::with(['variant.product', 'user']);

        if ($request->filled('movement_type')) {
            $query->where('movement_type', $request->movement_type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59']);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->whereHas('variant', function ($vq) use ($term) {
                $vq->where('variant_name', 'ilike', "%{$term}%")
                    ->orWhere('sku', 'ilike', "%{$term}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'ilike', "%{$term}%"));
            });
        }

        $movements = $query->orderBy('id', 'desc')->paginate(20)->withQueryString();

        return view('inventory.movements', compact('movements'));
    }
}
