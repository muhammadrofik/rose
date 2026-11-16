<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfitController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $preset = $request->get('preset', 'this_month');
        [$start, $end] = $this->reportService->resolveDateRange(
            $preset,
            $request->get('start_date'),
            $request->get('end_date')
        );

        // 1. Overall Profit Summary
        $salesReport = $this->reportService->getSalesReport($start, $end);
        $summary = $salesReport['summary'];

        // 2. Profit by Product
        $profitByProduct = $this->reportService->getProfitByProduct($start, $end);

        // 3. Profit by Category
        $profitByCategory = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->whereBetween('sales.sale_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'categories.name as category_name',
                DB::raw('SUM(sale_items.quantity) as total_qty'),
                DB::raw('SUM(sale_items.subtotal) as total_sales'),
                DB::raw('SUM(sale_items.quantity * sale_items.hpp_snapshot) as total_hpp'),
                DB::raw('SUM(sale_items.item_gross_profit) as total_gross_profit')
            )
            ->groupBy('categories.name')
            ->orderByDesc('total_gross_profit')
            ->get();

        // 4. Daily Profit Trends in Range
        $dailyTrends = DB::table('sales')
            ->whereBetween('sale_date', [$start->toDateString(), $end->toDateString()])
            ->select(
                'sale_date',
                DB::raw('COUNT(*) as total_orders'),
                DB::raw('SUM(grand_total) as daily_sales'),
                DB::raw('SUM(total_hpp) as daily_hpp'),
                DB::raw('SUM(gross_profit) as daily_profit')
            )
            ->groupBy('sale_date')
            ->orderBy('sale_date', 'asc')
            ->get();

        // 5. Products list with current Moving Average HPP
        $productsHpp = Product::with(['category', 'variants'])
            ->active()
            ->orderBy('name')
            ->paginate(15);

        return view('profit.index', compact(
            'summary',
            'profitByProduct',
            'profitByCategory',
            'dailyTrends',
            'productsHpp',
            'preset',
            'start',
            'end'
        ));
    }
}
