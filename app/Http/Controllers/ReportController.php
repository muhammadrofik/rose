<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use App\Services\ReportService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function index(Request $request): View
    {
        $tab = $request->get('tab', 'sales'); // 'sales', 'purchases', 'movements', 'valuation', 'profit', 'top_products'
        $preset = $request->get('preset', 'this_month');
        [$start, $end] = $this->reportService->resolveDateRange(
            $preset,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $categories = Category::orderBy('name')->get();
        $suppliers = Supplier::orderBy('name')->get();

        $data = match ($tab) {
            'purchases' => $this->reportService->getPurchasesReport($start, $end, $request->supplier_id),
            'movements' => $this->reportService->getStockMovementsReport($start, $end, $request->movement_type),
            'valuation' => $this->reportService->getInventoryValuationReport($request->category_id),
            'profit' => $this->reportService->getProfitByProduct($start, $end),
            'top_products' => [
                'products' => $this->reportService->getTopSellingProducts(15, $start, $end),
            ],
            default => $this->reportService->getSalesReport($start, $end),
        };

        return view('reports.index', compact(
            'tab',
            'preset',
            'start',
            'end',
            'categories',
            'suppliers',
            'data'
        ));
    }

    /**
     * Export reports to CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        $tab = $request->get('tab', 'sales');
        $preset = $request->get('preset', 'this_month');
        [$start, $end] = $this->reportService->resolveDateRange(
            $preset,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $filename = "laporan_{$tab}_".now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () use ($tab, $start, $end) {
            $handle = fopen('php://output', 'w');
            // Write BOM for UTF-8 Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));

            if ($tab === 'sales') {
                fputcsv($handle, ['No. Faktur', 'Tanggal', 'Kasir', 'Pelanggan', 'Metode Bayar', 'Total Kotor', 'Diskon', 'Grand Total', 'Total HPP', 'Keuntungan Kotor']);
                $sales = $this->reportService->getSalesReport($start, $end)['sales'];
                foreach ($sales as $s) {
                    fputcsv($handle, [
                        $s->invoice_number,
                        $s->sale_date->format('Y-m-d'),
                        $s->user->name ?? '',
                        $s->customer_name ?? 'Umum',
                        $s->payment_method,
                        $s->total_amount,
                        $s->discount_amount,
                        $s->grand_total,
                        $s->total_hpp,
                        $s->gross_profit,
                    ]);
                }
            } elseif ($tab === 'purchases') {
                fputcsv($handle, ['No. PO', 'Tanggal', 'Supplier', 'Petugas', 'Total Nilai Barang', 'Diskon', 'Biaya Ongkir/Handling', 'Grand Total']);
                $purchases = $this->reportService->getPurchasesReport($start, $end)['purchases'];
                foreach ($purchases as $p) {
                    fputcsv($handle, [
                        $p->purchase_number,
                        $p->purchase_date->format('Y-m-d'),
                        $p->supplier->name ?? '',
                        $p->user->name ?? '',
                        $p->total_items_cost,
                        $p->discount_amount,
                        $p->additional_cost,
                        $p->grand_total,
                    ]);
                }
            } elseif ($tab === 'valuation') {
                fputcsv($handle, ['SKU Varian', 'Nama Produk', 'Varian', 'Kategori', 'Stok Fisik', 'HPP Berjalan', 'Total Valuasi HPP', 'Harga Jual', 'Potensi Omzet']);
                $variants = $this->reportService->getInventoryValuationReport()['variants'];
                foreach ($variants as $v) {
                    fputcsv($handle, [
                        $v->sku,
                        $v->product->name ?? '',
                        $v->variant_name,
                        $v->product->category->name ?? '',
                        $v->stock,
                        $v->current_hpp,
                        $v->stock * (float) $v->current_hpp,
                        $v->selling_price,
                        $v->stock * (float) $v->selling_price,
                    ]);
                }
            } elseif ($tab === 'profit') {
                fputcsv($handle, ['SKU', 'Nama Produk', 'Kategori', 'Qty Terjual', 'Total Penjualan', 'Total HPP Terjual', 'Keuntungan Kotor', 'Margin %']);
                $products = $this->reportService->getProfitByProduct($start, $end)['products'];
                foreach ($products as $p) {
                    fputcsv($handle, [
                        $p['sku'],
                        $p['product_name'],
                        $p['category'],
                        $p['quantity_sold'],
                        $p['total_sales'],
                        $p['total_hpp'],
                        $p['gross_profit'],
                        $p['margin_percentage'],
                    ]);
                }
            } else {
                fputcsv($handle, ['ID', 'Waktu', 'SKU Varian', 'Nama Produk', 'Tipe Mutasi', 'Qty Sebelum', 'Perubahan', 'Qty Sesudah', 'Biaya Unit', 'Petugas', 'Keterangan']);
                $movements = $this->reportService->getStockMovementsReport($start, $end)['movements'];
                foreach ($movements as $m) {
                    fputcsv($handle, [
                        $m->id,
                        $m->created_at->format('Y-m-d H:i:s'),
                        $m->variant->sku ?? '',
                        $m->variant->product->name ?? '',
                        $m->movement_type,
                        $m->quantity_before,
                        $m->quantity_change,
                        $m->quantity_after,
                        $m->unit_cost,
                        $m->user->name ?? '',
                        $m->notes,
                    ]);
                }
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
