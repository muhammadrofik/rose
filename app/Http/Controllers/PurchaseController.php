<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PurchaseController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $suppliers = Supplier::orderBy('name')->get();

        $query = Purchase::with(['supplier', 'user', 'items.variant.product']);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('purchase_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('search')) {
            $query->where('purchase_number', 'ilike', "%{$request->search}%");
        }

        $purchases = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('purchases.index', compact('purchases', 'suppliers'));
    }

    public function create(): View
    {
        $suppliers = Supplier::orderBy('name')->get();
        $variants = ProductVariant::with('product')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->orderBy('product_id')
            ->get();

        return view('purchases.create', compact('suppliers', 'variants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'purchase_date' => ['required', 'date'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'additional_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.purchase_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        try {
            $purchase = $this->inventoryService->processPurchase(
                data: [
                    'supplier_id' => $validated['supplier_id'],
                    'purchase_date' => $validated['purchase_date'],
                    'discount_amount' => $validated['discount_amount'] ?? 0,
                    'additional_cost' => $validated['additional_cost'] ?? 0,
                    'notes' => $validated['notes'] ?? null,
                ],
                items: $validated['items'],
                user: auth()->user()
            );

            return redirect()->route('purchases.show', $purchase->id)
                ->with('success', "Penerimaan barang {$purchase->purchase_number} berhasil dicatat. Stok bertambah dan HPP Moving Average telah diperbarui otomatis.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses penerimaan barang: '.$e->getMessage());
        }
    }

    public function show(Purchase $purchase): View
    {
        $purchase->load(['supplier', 'user', 'items.variant.product', 'items.product']);

        return view('purchases.show', compact('purchase'));
    }
}
