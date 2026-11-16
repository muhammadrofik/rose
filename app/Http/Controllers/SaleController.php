<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        $query = Sale::with(['user', 'items.variant.product', 'payments']);

        // Cashiers only see their own transactions
        if ($user->isKasir()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'ilike', "%{$term}%")
                    ->orWhere('customer_name', 'ilike', "%{$term}%");
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('sale_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        $sales = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('sales.index', compact('sales'));
    }

    public function show(Sale $sale): View
    {
        $user = auth()->user();

        // Authorization check
        if ($user->isKasir() && $sale->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat transaksi kasir lain.');
        }

        $sale->load(['user', 'items.variant.product', 'items.product', 'payments']);

        return view('sales.show', compact('sale'));
    }

    public function printReceipt(Sale $sale): View
    {
        $user = auth()->user();
        if ($user->isKasir() && $sale->user_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        $sale->load(['user', 'items.variant.product', 'payments']);

        return view('sales.receipt', compact('sale'));
    }
}
