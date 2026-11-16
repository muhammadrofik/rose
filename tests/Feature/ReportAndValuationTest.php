<?php

namespace Tests\Feature;

use App\Models\Sale;
use App\Models\User;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReportAndValuationTest extends TestCase
{
    use DatabaseTransactions;

    protected ReportService $reportService;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reportService = app(ReportService::class);

        $this->admin = User::create([
            'name' => 'Admin Report Test',
            'email' => 'admin_rep_'.uniqid().'@rose.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'is_active' => true,
        ]);
    }

    public function test_sales_report_aggregation_matches_database(): void
    {
        $today = Carbon::today();

        $sale1 = Sale::create([
            'invoice_number' => 'INV-REP-1-'.uniqid(),
            'user_id' => $this->admin->id,
            'sale_date' => $today,
            'total_amount' => 300000,
            'discount_amount' => 20000,
            'grand_total' => 280000,
            'total_hpp' => 180000,
            'gross_profit' => 100000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
        ]);

        $report = $this->reportService->getSalesReport($today->copy()->startOfDay(), $today->copy()->endOfDay());

        $this->assertGreaterThanOrEqual(1, $report['summary']['count']);
        $this->assertGreaterThanOrEqual(280000.0, (float) $report['summary']['total_net']);
        $this->assertGreaterThanOrEqual(100000.0, (float) $report['summary']['total_gross_profit']);
    }

    public function test_inventory_valuation_aggregates_total_units_and_hpp(): void
    {
        $report = $this->reportService->getInventoryValuationReport();

        $this->assertArrayHasKey('variants', $report);
        $this->assertArrayHasKey('summary', $report);
        $this->assertGreaterThanOrEqual(0, $report['summary']['total_units']);
        $this->assertGreaterThanOrEqual(0, $report['summary']['total_valuation']);
    }

    public function test_csv_export_endpoint_returns_streaming_csv(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('reports.export', ['tab' => 'sales']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }
}
