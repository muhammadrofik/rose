<x-layouts.app>
    <x-slot:title>Stok Barang — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Stok Barang</h1>
                <p class="text-xs text-slate-500 mt-0.5">Monitoring kuantitas fisik persediaan per varian, ambang batas minimum, dan valuasi HPP.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                    <x-icon name="in" class="w-4 h-4" />
                    <span>Penerimaan Barang Masuk</span>
                </a>
                <a href="{{ route('inventory.adjustments.create') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                    <x-icon name="adjust" class="w-4 h-4 text-slate-500" />
                    <span>Penyesuaian Stok / Opname</span>
                </a>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Unit Tersedia</span>
                    <span class="text-2xl font-black text-slate-900 mt-1 block">{{ number_format($totalUnits, 0, ',', '.') }} pcs</span>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700">
                    <x-icon name="inventory" class="w-5 h-5" />
                </div>
            </div>

            @can('view-hpp')
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Nilai Persediaan HPP</span>
                    <span class="text-2xl font-black text-rose-600 mt-1 block">Rp {{ number_format($totalValuation, 0, ',', '.') }}</span>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-rose-50 flex items-center justify-center text-rose-600">
                    <x-icon name="profit" class="w-5 h-5" />
                </div>
            </div>
            @endcan

            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex items-center justify-between">
                <div>
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Buku Mutasi Ledger</span>
                    <a href="{{ route('inventory.movements') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700 mt-1.5 flex items-center gap-1">
                        <span>Lihat Riwayat Pergerakan</span>
                        <x-icon name="chevron-right" class="w-3.5 h-3.5" />
                    </a>
                </div>
                <div class="w-10 h-10 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-700">
                    <x-icon name="history" class="w-5 h-5" />
                </div>
            </div>
        </div>

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, varian, SKU..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 text-xs transition-all outline-none">
                </div>

                <div class="flex flex-wrap gap-2">
                    <select name="category_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select name="stock_status" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none">
                        <option value="">Semua Kondisi Stok</option>
                        <option value="safe" {{ request('stock_status') === 'safe' ? 'selected' : '' }}>Stok Aman</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Menipis (&le; Min)</option>
                        <option value="out" {{ request('stock_status') === 'out' ? 'selected' : '' }}>Stok Habis (0)</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                        Filter
                    </button>

                    @if(request()->anyFilled(['search', 'category_id', 'stock_status']))
                        <a href="{{ route('inventory.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Inventory Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">SKU Varian</th>
                            <th class="py-3.5 px-6">Nama Barang & Varian</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6 text-center">Stok Fisik</th>
                            <th class="py-3.5 px-6 text-center">Batas Minimum</th>
                            @can('view-hpp')
                            <th class="py-3.5 px-6 text-right">HPP Berjalan</th>
                            <th class="py-3.5 px-6 text-right">Valuasi Total HPP</th>
                            @endcan
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($variants as $v)
                            @php
                                $minStock = $v->product->min_stock ?? 5;
                                $isOut = $v->stock <= 0;
                                $isLow = $v->stock > 0 && $v->stock <= $minStock;
                                $valuation = $v->stock * (float)$v->current_hpp;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    {{ $v->sku }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $v->product->name ?? 'Produk' }}</div>
                                    <div class="text-[11px] text-slate-400">Varian: <strong class="text-slate-600">{{ $v->variant_name }}</strong> ({{ $v->size ?? '-' }} / {{ $v->color ?? '-' }})</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $v->product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="font-black text-sm {{ $isOut ? 'text-rose-600' : ($isLow ? 'text-amber-600' : 'text-slate-900') }}">
                                        {{ $v->stock }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-slate-400 font-semibold">
                                    {{ $minStock }}
                                </td>
                                @can('view-hpp')
                                <td class="py-4 px-6 text-right font-mono font-bold text-slate-600">
                                    Rp {{ number_format($v->current_hpp, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-black text-slate-900">
                                    Rp {{ number_format($valuation, 0, ',', '.') }}
                                </td>
                                @endcan
                                <td class="py-4 px-6 text-center">
                                    @if($isOut)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700">Habis</span>
                                    @elseif($isLow)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menipis</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aman</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('inventory.movements', ['search' => $v->sku]) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition-colors" title="Buku Ledger">
                                            Mutasi
                                        </a>
                                        <a href="{{ route('purchases.create') }}" class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-[11px] transition-colors" title="Penerimaan">
                                            Restock
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    Tidak ada data stok yang sesuai dengan filter.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $variants->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
