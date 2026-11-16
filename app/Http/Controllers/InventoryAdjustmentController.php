<?php

namespace App\Http\Controllers;

use App\Models\InventoryAdjustment;
use App\Models\ProductVariant;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function __construct(
        protected InventoryService $inventoryService
    ) {}

    public function index(Request $request): View
    {
        $query = InventoryAdjustment::with(['user', 'items.variant.product']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('adjustment_date', [$request->start_date, $request->end_date]);
        }

        $adjustments = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();

        return view('inventory.adjustments.index', compact('adjustments'));
    }

    public function create(): View
    {
        $variants = ProductVariant::with('product')
            ->whereHas('product', fn ($q) => $q->where('is_active', true))
            ->orderBy('product_id')
            ->get();

        return view('inventory.adjustments.create', compact('variants'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'adjustment_date' => ['required', 'date'],
            'type' => ['required', 'in:damage,lost,internal_use,correction'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_variant_id' => ['required', 'exists:product_variants,id'],
            'items.*.physical_stock' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $adjustment = $this->inventoryService->processAdjustment(
                data: [
                    'adjustment_date' => $validated['adjustment_date'],
                    'type' => $validated['type'],
                    'notes' => $validated['notes'] ?? null,
                ],
                items: $validated['items'],
                user: auth()->user()
            );

            return redirect()->route('inventory.adjustments.index')
                ->with('success', "Penyesuaian stok {$adjustment->adjustment_number} berhasil dicatat dan pergerakan stok telah tercatat di ledger.");
        } catch (Exception $e) {
            return back()->withInput()->with('error', 'Gagal memproses penyesuaian: '.$e->getMessage());
        }
    }

    public function show(InventoryAdjustment $adjustment): View
    {
        $adjustment->load(['user', 'items.variant.product']);

        return view('inventory.adjustments.show', compact('adjustment'));
    }
}
