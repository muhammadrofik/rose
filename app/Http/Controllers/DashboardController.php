<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Sale;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $user = auth()->user();
        $today = Carbon::today();
        $thisMonth = Carbon::now()->startOfMonth();

        // 1. Sales & Profit Stats
        $salesTodayQuery = Sale::whereDate('sale_date', $today);
        $salesMonthQuery = Sale::whereBetween('sale_date', [$thisMonth->toDateString(), Carbon::now()->endOfMonth()->toDateString()]);

        if ($user->isKasir()) {
            $salesTodayQuery->where('user_id', $user->id);
            $salesMonthQuery->where('user_id', $user->id);
        }

        $totalSalesToday = (float) $salesTodayQuery->sum('grand_total');
        $totalOrdersToday = (int) $salesTodayQuery->count();

        $totalSalesMonth = (float) $salesMonthQuery->sum('grand_total');
        $totalOrdersMonth = (int) $salesMonthQuery->count();

        $grossProfitToday = (float) $salesTodayQuery->sum('gross_profit');
        $grossProfitMonth = (float) $salesMonthQuery->sum('gross_profit');

        // 2. Inventory Stats
        $totalActiveProducts = Product::where('is_active', true)->count();
        $totalStockUnits = (int) ProductVariant::sum('stock');

        // Total inventory valuation based on HPP
        $totalInventoryValuation = ProductVariant::all()->reduce(function ($carry, $v) {
            return $carry + ($v->stock * (float) $v->current_hpp);
        }, 0.0);

        // Low stock products count
        $lowStockVariants = ProductVariant::with('product.category')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->whereRaw('stock <= (SELECT min_stock FROM products WHERE products.id = product_variants.product_id)')
            ->orderBy('stock', 'asc')
            ->get();

        $lowStockCount = $lowStockVariants->count();

        // 3. Recent 5 Sales
        $recentSalesQuery = Sale::with(['user', 'items.variant.product'])
            ->orderBy('created_at', 'desc')
            ->limit(5);

        if ($user->isKasir()) {
            $recentSalesQuery->where('user_id', $user->id);
        }

        $recentSales = $recentSalesQuery->get();

        // 4. Top 5 Best Selling Products (This month)
        $topProducts = $this->reportService->getTopSellingProducts(5, $thisMonth, Carbon::now());

        // 5. Monthly Revenue Chart Data (Last 6-12 months)
        $monthlyRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth()->toDateString();
            $monthEnd = $month->copy()->endOfMonth()->toDateString();

            $revQuery = Sale::whereBetween('sale_date', [$monthStart, $monthEnd]);
            if ($user->isKasir()) {
                $revQuery->where('user_id', $user->id);
            }

            $monthlyRevenue[] = [
                'label' => $month->translatedFormat('M Y'),
                'total' => (float) $revQuery->sum('grand_total'),
                'profit' => (float) $revQuery->sum('gross_profit'),
            ];
        }

        // 6. Category Stock Breakdown for Inventory Trends
        $categoryStock = Category::withCount(['products'])
            ->with(['products.variants'])
            ->get()
            ->map(function ($cat) {
                $units = $cat->products->sum(fn ($p) => $p->variants->sum('stock'));

                return [
                    'name' => $cat->name,
                    'units' => $units,
                ];
            });

        return view('dashboard.index', compact(
            'totalSalesToday',
            'totalOrdersToday',
            'totalSalesMonth',
            'totalOrdersMonth',
            'grossProfitToday',
            'grossProfitMonth',
            'totalActiveProducts',
            'totalStockUnits',
            'totalInventoryValuation',
            'lowStockCount',
            'lowStockVariants',
            'recentSales',
            'topProducts',
            'monthlyRevenue',
            'categoryStock'
        ));
    }
}
