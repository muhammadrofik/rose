<x-layouts.app>
    <x-slot:title>Laporan Lengkap — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 no-print">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pusat Laporan Toko</h1>
                <p class="text-xs text-slate-500 mt-0.5">Laporan audit terpadu: penjualan, penerimaan barang, mutasi persediaan, valuasi HPP, dan keuntungan kotor.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="window.print()" class="px-3.5 py-2.5 rounded-xl bg-white border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                    <x-icon name="printer" class="w-4 h-4 text-slate-500" />
                    <span>Cetak Laporan</span>
                </button>
                <a href="{{ route('reports.export', request()->all()) }}" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                    <x-icon name="download" class="w-4 h-4" />
                    <span>Export Excel / CSV</span>
                </a>
            </div>
        </div>

        <!-- Filter Presets & Date Toolbar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm no-print space-y-3">
            <form method="GET" action="{{ route('reports.index') }}" class="flex flex-wrap items-center gap-3">
                <input type="hidden" name="tab" value="{{ $tab }}">

                <div class="flex items-center gap-2">
                    <label class="text-xs font-bold text-slate-700">Periode:</label>
                    <select name="preset" onchange="this.form.submit()" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 outline-none">
                        <option value="today" {{ $preset === 'today' ? 'selected' : '' }}>Hari Ini</option>
                        <option value="yesterday" {{ $preset === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                        <option value="last_7_days" {{ $preset === 'last_7_days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                        <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                        <option value="custom" {{ $preset === 'custom' ? 'selected' : '' }}>Rentang Kustom</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="{{ $start->format('Y-m-d') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">
                    <span class="text-slate-400 text-xs">s/d</span>
                    <input type="date" name="end_date" value="{{ $end->format('Y-m-d') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">
                    <button type="submit" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold transition-all">
                        Terapkan
                    </button>
                </div>
            </form>
        </div>

        <!-- REPORT NAVIGATION TABS -->
        <div class="flex items-center gap-2 border-b border-slate-200/80 pb-2 overflow-x-auto no-print">
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'sales'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'sales' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                1. Laporan Penjualan
            </a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'purchases'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'purchases' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                2. Laporan Barang Masuk (PO)
            </a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'movements'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'movements' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                3. Laporan Mutasi Stok (Ledger)
            </a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'valuation'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'valuation' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                4. Laporan Nilai Persediaan HPP
            </a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'profit'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'profit' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                5. Laporan Keuntungan Kotor
            </a>
            <a href="{{ route('reports.index', array_merge(request()->except('tab'), ['tab' => 'top_products'])) }}"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ $tab === 'top_products' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-100' }}">
                6. Produk Terlaris
            </a>
        </div>

        <!-- REPORT TABLES BY TAB -->

        <!-- TAB 1: SALES REPORT -->
        @if($tab === 'sales')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total Faktur Penjualan</span>
                        <strong class="text-slate-900 text-base">{{ $data['summary']['count'] }} Transaksi</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Penjualan Bersih</span>
                        <strong class="text-rose-600 text-base">Rp {{ number_format($data['summary']['total_net'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total HPP Terjual</span>
                        <strong class="text-slate-700 text-base font-mono">Rp {{ number_format($data['summary']['total_hpp'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Keuntungan Kotor (Margin)</span>
                        <strong class="text-emerald-600 text-base">Rp {{ number_format($data['summary']['total_gross_profit'], 0, ',', '.') }} ({{ $data['summary']['margin_percentage'] }}%)</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. Faktur</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Kasir</th>
                                <th class="py-3 px-4">Pelanggan</th>
                                <th class="py-3 px-4">Metode</th>
                                <th class="py-3 px-4 text-right">Penjualan Bersih</th>
                                <th class="py-3 px-4 text-right">HPP Terjual</th>
                                <th class="py-3 px-4 text-right">Keuntungan Kotor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['sales'] as $sale)
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $sale->invoice_number }}</td>
                                    <td class="py-3 px-4">{{ $sale->sale_date->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4">{{ $sale->user->name ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $sale->customer_name ?? 'Umum' }}</td>
                                    <td class="py-3 px-4 uppercase font-semibold text-[10px]">{{ $sale->payment_method }}</td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600">Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-extrabold text-emerald-600">Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 2: PURCHASES REPORT -->
        @elseif($tab === 'purchases')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total PO Barang Masuk</span>
                        <strong class="text-slate-900 text-base">{{ $data['summary']['count'] }} Faktur</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Nilai Barang Supplier</span>
                        <strong class="text-slate-900 text-base">Rp {{ number_format($data['summary']['total_items_cost'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Biaya Ongkir/Handling</span>
                        <strong class="text-slate-700 text-base font-mono">Rp {{ number_format($data['summary']['total_additional_cost'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Pengadaan (Grand Total)</span>
                        <strong class="text-rose-600 text-base">Rp {{ number_format($data['summary']['grand_total'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">No. PO</th>
                                <th class="py-3 px-4">Tanggal</th>
                                <th class="py-3 px-4">Supplier</th>
                                <th class="py-3 px-4">Petugas</th>
                                <th class="py-3 px-4 text-center">Unit Masuk</th>
                                <th class="py-3 px-4 text-right">Ongkir / Handling</th>
                                <th class="py-3 px-4 text-right">Total Pembelian</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['purchases'] as $p)
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $p->purchase_number }}</td>
                                    <td class="py-3 px-4">{{ $p->purchase_date->format('d/m/Y') }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-800">{{ $p->supplier->name ?? '-' }}</td>
                                    <td class="py-3 px-4">{{ $p->user->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center font-bold">{{ $p->items->sum('quantity') }} pcs</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-500">Rp {{ number_format($p->additional_cost, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-black text-slate-900">Rp {{ number_format($p->grand_total, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 3: MOVEMENTS REPORT -->
        @elseif($tab === 'movements')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total Baris Mutasi Ledger</span>
                        <strong class="text-slate-900 text-base">{{ $data['summary']['total_records'] }} Catatan</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Unit Barang Masuk</span>
                        <strong class="text-emerald-600 text-base">+{{ $data['summary']['total_in'] }} pcs</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Unit Barang Keluar</span>
                        <strong class="text-rose-600 text-base">-{{ $data['summary']['total_out'] }} pcs</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Waktu</th>
                                <th class="py-3 px-4">Barang & Varian</th>
                                <th class="py-3 px-4">Jenis Pergerakan</th>
                                <th class="py-3 px-4 text-center">Stok Awal</th>
                                <th class="py-3 px-4 text-center">Perubahan</th>
                                <th class="py-3 px-4 text-center">Stok Akhir</th>
                                <th class="py-3 px-4">Petugas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['movements'] as $m)
                                <tr>
                                    <td class="py-3 px-4 text-slate-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900">{{ $m->variant->product->name ?? '' }}</div>
                                        <span class="text-[10px] text-slate-400">{{ $m->variant->variant_name ?? '' }} ({{ $m->variant->sku ?? '' }})</span>
                                    </td>
                                    <td class="py-3 px-4 font-semibold text-[11px]">{{ $m->movement_type_label }}</td>
                                    <td class="py-3 px-4 text-center font-mono text-slate-500">{{ $m->quantity_before }}</td>
                                    <td class="py-3 px-4 text-center font-mono font-black {{ $m->quantity_change > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                        {{ $m->quantity_change > 0 ? '+' . $m->quantity_change : $m->quantity_change }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-mono font-extrabold text-slate-900">{{ $m->quantity_after }}</td>
                                    <td class="py-3 px-4 text-slate-600">{{ $m->user->name ?? 'Sistem' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 4: VALUATION REPORT -->
        @elseif($tab === 'valuation')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total SKU Varian Aktif</span>
                        <strong class="text-slate-900 text-base">{{ $data['summary']['total_sku_count'] }} SKU</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Unit Tersedia</span>
                        <strong class="text-slate-900 text-base">{{ number_format($data['summary']['total_units'], 0, ',', '.') }} pcs</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Valuasi HPP</span>
                        <strong class="text-rose-600 text-base">Rp {{ number_format($data['summary']['total_valuation'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Potensi Omzet Penjualan</span>
                        <strong class="text-emerald-600 text-base">Rp {{ number_format($data['summary']['total_potential_revenue'], 0, ',', '.') }}</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4">Nama Produk & Varian</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Stok</th>
                                <th class="py-3 px-4 text-right">HPP Berjalan</th>
                                <th class="py-3 px-4 text-right">Total Valuasi HPP</th>
                                <th class="py-3 px-4 text-right">Harga Jual</th>
                                <th class="py-3 px-4 text-right">Potensi Omzet</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['variants'] as $v)
                                <tr>
                                    <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $v->sku }}</td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-900">{{ $v->product->name ?? '' }}</div>
                                        <span class="text-[10px] text-slate-400">{{ $v->variant_name }}</span>
                                    </td>
                                    <td class="py-3 px-4">{{ $v->product->category->name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center font-extrabold text-slate-900">{{ $v->stock }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-600">Rp {{ number_format($v->current_hpp, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-slate-900">Rp {{ number_format($v->stock * (float)$v->current_hpp, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-700">Rp {{ number_format($v->selling_price, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-emerald-700">Rp {{ number_format($v->stock * (float)$v->selling_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 5: PROFIT REPORT -->
        @elseif($tab === 'profit')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                    <div>
                        <span class="text-slate-400 block">Total Omzet Penjualan</span>
                        <strong class="text-slate-900 text-base">Rp {{ number_format($data['summary']['total_sales'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Beban HPP (COGS)</span>
                        <strong class="text-slate-700 text-base font-mono">Rp {{ number_format($data['summary']['total_hpp'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Total Keuntungan Kotor</span>
                        <strong class="text-emerald-600 text-base">Rp {{ number_format($data['summary']['total_gross_profit'], 0, ',', '.') }}</strong>
                    </div>
                    <div>
                        <span class="text-slate-400 block">Rata-Rata Margin Kotor</span>
                        <strong class="text-rose-600 text-base">{{ $data['summary']['margin_percentage'] }}%</strong>
                    </div>
                </div>

                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Qty Terjual</th>
                                <th class="py-3 px-4 text-right">Total Penjualan</th>
                                <th class="py-3 px-4 text-right">Total HPP Terjual</th>
                                <th class="py-3 px-4 text-right">Keuntungan Kotor</th>
                                <th class="py-3 px-4 text-center">Margin (%)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['products'] as $p)
                                <tr>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $p['product_name'] }}</td>
                                    <td class="py-3 px-4">{{ $p['category'] }}</td>
                                    <td class="py-3 px-4 text-center font-extrabold text-slate-900">{{ $p['quantity_sold'] }} pcs</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($p['total_sales'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono text-slate-500">Rp {{ number_format($p['total_hpp'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-emerald-600">Rp {{ number_format($p['gross_profit'], 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-700">
                                            {{ $p['margin_percentage'] }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

        <!-- TAB 6: TOP SELLING PRODUCTS -->
        @elseif($tab === 'top_products')
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-4">
                <div class="overflow-x-auto border border-slate-100 rounded-2xl">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="py-3 px-4 text-center">Peringkat</th>
                                <th class="py-3 px-4">Nama Produk</th>
                                <th class="py-3 px-4">SKU</th>
                                <th class="py-3 px-4">Kategori</th>
                                <th class="py-3 px-4 text-center">Total Kuantitas Terjual</th>
                                <th class="py-3 px-4 text-right">Total Pendapatan (Omzet)</th>
                                <th class="py-3 px-4 text-right">Keuntungan Kotor</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                            @foreach($data['products'] as $idx => $tp)
                                <tr>
                                    <td class="py-3 px-4 text-center font-extrabold text-rose-600">#{{ $idx + 1 }}</td>
                                    <td class="py-3 px-4 font-bold text-slate-900">{{ $tp->name }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-500">{{ $tp->sku }}</td>
                                    <td class="py-3 px-4">{{ $tp->category_name ?? '-' }}</td>
                                    <td class="py-3 px-4 text-center font-black text-slate-900 text-sm">{{ $tp->total_qty }} pcs</td>
                                    <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">Rp {{ number_format($tp->total_revenue, 0, ',', '.') }}</td>
                                    <td class="py-3 px-4 text-right font-mono font-black text-emerald-600">Rp {{ number_format($tp->total_profit, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
