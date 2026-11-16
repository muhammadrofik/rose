<x-layouts.app>
    <x-slot:title>Buku Ledger Mutasi Stok — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <a href="{{ route('inventory.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Stok Barang</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Buku Ledger Mutasi Stok</h1>
                <p class="text-xs text-slate-500 mt-0.5">Catatan audit riwayat seluruh pergerakan barang masuk, penjualan kasir, dan penyesuaian stok.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchases.create') }}" class="px-3.5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-1.5 transition-all">
                    <x-icon name="in" class="w-4 h-4" />
                    <span>Barang Masuk (PO)</span>
                </a>
                <a href="{{ route('inventory.adjustments.create') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                    <x-icon name="adjust" class="w-4 h-4 text-slate-500" />
                    <span>Penyesuaian Stok</span>
                </a>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('inventory.movements') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari SKU atau nama produk..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 text-xs transition-all outline-none">
                </div>

                <div class="flex flex-wrap gap-2">
                    <select name="movement_type" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none">
                        <option value="">Semua Jenis Mutasi</option>
                        <option value="PURCHASE_RECEIPT" {{ request('movement_type') === 'PURCHASE_RECEIPT' ? 'selected' : '' }}>Barang Masuk (Pembelian)</option>
                        <option value="SALE" {{ request('movement_type') === 'SALE' ? 'selected' : '' }}>Penjualan (Kasir)</option>
                        <option value="ADJUSTMENT_DAMAGE" {{ request('movement_type') === 'ADJUSTMENT_DAMAGE' ? 'selected' : '' }}>Penyesuaian (Barang Rusak)</option>
                        <option value="ADJUSTMENT_LOST" {{ request('movement_type') === 'ADJUSTMENT_LOST' ? 'selected' : '' }}>Penyesuaian (Barang Hilang)</option>
                        <option value="ADJUSTMENT_INTERNAL" {{ request('movement_type') === 'ADJUSTMENT_INTERNAL' ? 'selected' : '' }}>Penggunaan Internal</option>
                        <option value="ADJUSTMENT_CORRECTION" {{ request('movement_type') === 'ADJUSTMENT_CORRECTION' ? 'selected' : '' }}>Koreksi Opname Fisik</option>
                    </select>

                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                        Cari
                    </button>

                    @if(request()->anyFilled(['search', 'movement_type', 'start_date', 'end_date']))
                        <a href="{{ route('inventory.movements') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Ledger Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Waktu Mutasi</th>
                            <th class="py-3.5 px-6">Produk & Varian</th>
                            <th class="py-3.5 px-6">Jenis Mutasi</th>
                            <th class="py-3.5 px-6 text-center">Stok Awal</th>
                            <th class="py-3.5 px-6 text-center">Perubahan</th>
                            <th class="py-3.5 px-6 text-center">Stok Akhir</th>
                            @can('view-hpp')
                            <th class="py-3.5 px-6 text-right">Biaya Satuan</th>
                            @endcan
                            <th class="py-3.5 px-6">Petugas</th>
                            <th class="py-3.5 px-6">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($movements as $m)
                            @php
                                $isPositive = $m->quantity_change > 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 text-slate-500 whitespace-nowrap">
                                    {{ $m->created_at->format('d M Y') }}
                                    <span class="text-[10px] text-slate-400 block">{{ $m->created_at->format('H:i:s') }} WITA</span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900">{{ $m->variant->product->name ?? 'Produk Dihapus' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $m->variant->variant_name ?? '' }} ({{ $m->variant->sku ?? '-' }})</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-block px-2.5 py-1 rounded-full text-[10px] font-bold {{ $isPositive ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800' }}">
                                        {{ $m->movement_type_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center font-mono font-semibold text-slate-500">
                                    {{ $m->quantity_before }}
                                </td>
                                <td class="py-4 px-6 text-center font-mono font-black text-sm {{ $isPositive ? 'text-emerald-600' : 'text-rose-600' }}">
                                    {{ $isPositive ? '+' . $m->quantity_change : $m->quantity_change }}
                                </td>
                                <td class="py-4 px-6 text-center font-mono font-extrabold text-slate-900">
                                    {{ $m->quantity_after }}
                                </td>
                                @can('view-hpp')
                                <td class="py-4 px-6 text-right font-mono text-slate-600">
                                    Rp {{ number_format($m->unit_cost, 0, ',', '.') }}
                                </td>
                                @endcan
                                <td class="py-4 px-6 text-slate-600">
                                    {{ $m->user->name ?? 'Sistem' }}
                                </td>
                                <td class="py-4 px-6 text-slate-500 max-w-xs truncate">
                                    {{ $m->notes ?? '-' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    Belum ada catatan mutasi pergerakan stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
