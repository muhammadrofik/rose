<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Parse date range preset or custom dates.
     *
     * @return array [Carbon $startDate, Carbon $endDate]
     */
    public function resolveDateRange(?string $preset, ?string $start = null, ?string $end = null): array
    {
        return match ($preset) {
            'today' => [Carbon::today()->startOfDay(), Carbon::today()->endOfDay()],
            'yesterday' => [Carbon::yesterday()->startOfDay(), Carbon::yesterday()->endOfDay()],
            'last_7_days' => [Carbon::today()->subDays(6)->startOfDay(), Carbon::today()->endOfDay()],
            'last_30_days' => [Carbon::today()->subDays(29)->startOfDay(), Carbon::today()->endOfDay()],
            'this_month' => [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfMonth()->endOfDay()],
            'last_month' => [Carbon::now()->subMonth()->startOfMonth()->startOfDay(), Carbon::now()->subMonth()->endOfMonth()->endOfDay()],
            'this_year' => [Carbon::now()->startOfYear()->startOfDay(), Carbon::now()->endOfYear()->endOfDay()],
            'custom' => [
                $start ? Carbon::parse($start)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay(),
                $end ? Carbon::parse($end)->endOfDay() : Carbon::now()->endOfDay(),
            ],
            default => [Carbon::now()->startOfMonth()->startOfDay(), Carbon::now()->endOfDay()],
        };
    }

    /**
     * Sales Report data.
     */
    public function getSalesReport(Carbon $start, Carbon $end, ?int $userId = null): array
    {
        $query = Sale::with(['user', 'items.variant.product', 'payments'])
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()]);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        $sales = $query->orderBy('sale_date', 'desc')->orderBy('id', 'desc')->get();

        $totalGross = $sales->sum('total_amount');
        $totalDiscount = $sales->sum('discount_amount');
        $totalNet = $sales->sum('grand_total');
        $totalHpp = $sales->sum('total_hpp');
        $totalGrossProfit = $sales->sum('gross_profit');
        $margin = $totalNet > 0 ? round(($totalGrossProfit / $totalNet) * 100, 2) : 0;

        return [
            'sales' => $sales,
            'summary' => [
                'count' => $sales->count(),
                'total_gross' => $totalGross,
                'total_discount' => $totalDiscount,
                'total_net' => $totalNet,
                'total_hpp' => $totalHpp,
                'total_gross_profit' => $totalGrossProfit,
                'margin_percentage' => $margin,
            ],
        ];
    }

    /**
     * Stock Valuation Report (Current snapshot of on-hand inventory valued at moving average HPP).
     */
    public function getInventoryValuationReport(?int $categoryId = null): array
    {
        $query = ProductVariant::with(['product.category']);

        if ($categoryId) {
            $query->whereHas('product', fn ($q) => $q->where('category_id', $categoryId));
        }

        $variants = $query->orderBy('product_id')->get();

        $totalUnits = $variants->sum('stock');
        $totalValuation = $variants->reduce(fn ($carry, $v) => $carry + ($v->stock * (float) $v->current_hpp), 0.0);
        $totalPotentialRevenue = $variants->reduce(fn ($carry, $v) => $carry + ($v->stock * (float) $v->selling_price), 0.0);

        return [
            'variants' => $variants,
            'summary' => [
                'total_sku_count' => $variants->count(),
                'total_units' => $totalUnits,
                'total_valuation' => $totalValuation,
                'total_potential_revenue' => $totalPotentialRevenue,
                'potential_profit' => $totalPotentialRevenue - $totalValuation,
            ],
        ];
    }

    /**
     * Purchases / Barang Masuk Report.
     */
    public function getPurchasesReport(Carbon $start, Carbon $end, ?int $supplierId = null): array
    {
        $query = Purchase::with(['supplier', 'user', 'items.variant.product'])
            ->whereBetween('purchase_date', [$start->toDateString(), $end->toDateString()]);

        if ($supplierId) {
            $query->where('supplier_id', $supplierId);
        }

        $purchases = $query->orderBy('purchase_date', 'desc')->get();

        return [
            'purchases' => $purchases,
            'summary' => [
                'count' => $purchases->count(),
                'total_items_cost' => $purchases->sum('total_items_cost'),
                'total_discount' => $purchases->sum('discount_amount'),
                'total_additional_cost' => $purchases->sum('additional_cost'),
                'grand_total' => $purchases->sum('grand_total'),
            ],
        ];
    }

    /**
     * Stock Movement Ledger Report.
     */
    public function getStockMovementsReport(Carbon $start, Carbon $end, ?string $movementType = null, ?int $variantId = null): array
    {
        $query = StockMovement::with(['variant.product', 'user'])
            ->whereBetween('created_at', [$start, $end]);

        if ($movementType) {
            $query->where('movement_type', $movementType);
        }

        if ($variantId) {
            $query->where('product_variant_id', $variantId);
        }

        $movements = $query->orderBy('id', 'desc')->get();

        return [
            'movements' => $movements,
            'summary' => [
                'total_records' => $movements->count(),
                'total_in' => $movements->where('quantity_change', '>', 0)->sum('quantity_change'),
                'total_out' => abs($movements->where('quantity_change', '<', 0)->sum('quantity_change')),
            ],
        ];
    }

    /**
     * Profit & Loss by Product.
     */
    public function getProfitByProduct(Carbon $start, Carbon $end): array
    {
        $items = SaleItem::with(['product.category', 'variant'])
            ->whereHas('sale', fn ($q) => $q->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()]))
            ->get();

        $grouped = $items->groupBy('product_id')->map(function ($productItems) {
            $first = $productItems->first();
            $qty = $productItems->sum('quantity');
            $subtotal = $productItems->sum('subtotal');
            $hppTotal = $productItems->reduce(fn ($carry, $i) => $carry + ($i->quantity * (float) $i->hpp_snapshot), 0.0);
            $grossProfit = $productItems->sum('item_gross_profit');
            $margin = $subtotal > 0 ? round(($grossProfit / $subtotal) * 100, 2) : 0;

            return [
                'product_name' => $first->product->name ?? 'Produk Dihapus',
                'sku' => $first->product->sku ?? '-',
                'category' => $first->product->category->name ?? '-',
                'quantity_sold' => $qty,
                'total_sales' => $subtotal,
                'total_hpp' => $hppTotal,
                'gross_profit' => $grossProfit,
                'margin_percentage' => $margin,
            ];
        })->sortByDesc('gross_profit')->values();

        return [
            'products' => $grouped,
            'summary' => [
                'total_sales' => $grouped->sum('total_sales'),
                'total_hpp' => $grouped->sum('total_hpp'),
                'total_gross_profit' => $grouped->sum('gross_profit'),
                'margin_percentage' => $grouped->sum('total_sales') > 0
                    ? round(($grouped->sum('gross_profit') / $grouped->sum('total_sales')) * 100, 2)
                    : 0,
            ],
        ];
    }

    /**
     * Top selling products.
     */
    public function getTopSellingProducts(int $limit = 5, ?Carbon $start = null, ?Carbon $end = null): array
    {
        $start = $start ?? Carbon::now()->startOfMonth();
        $end = $end ?? Carbon::now()->endOfMonth();

        $rows = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->leftJoin('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'products.id',
                'products.name',
                'products.sku',
                'products.image_path',
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_revenue'),
                DB::raw('SUM(sale_items.item_gross_profit) as total_profit')
            )
            ->groupBy('products.id', 'products.name', 'products.sku', 'products.image_path', 'categories.name')
            ->orderByDesc('total_qty')
            ->limit($limit)
            ->get();

        return $rows->toArray();
    }
}
