<x-layouts.app>
    <x-slot:title>HPP & Keuntungan — Rose Colection</x-slot:title>

    <div class="space-y-6" x-data="{ activeTab: 'products' }">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">HPP & Analisis Keuntungan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau Harga Pokok Penjualan (Moving Average), margin keuntungan per produk, dan profitabilitas transaksi.</p>
            </div>

            <!-- Date Range Presets -->
            <form method="GET" action="{{ route('profit.index') }}" class="flex items-center gap-2">
                <select name="preset" onchange="this.form.submit()" class="px-3.5 py-2 rounded-xl border border-slate-200 text-xs font-bold text-slate-800 bg-white shadow-xs outline-none">
                    <option value="today" {{ $preset === 'today' ? 'selected' : '' }}>Hari Ini</option>
                    <option value="yesterday" {{ $preset === 'yesterday' ? 'selected' : '' }}>Kemarin</option>
                    <option value="last_7_days" {{ $preset === 'last_7_days' ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="this_month" {{ $preset === 'this_month' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="last_month" {{ $preset === 'last_month' ? 'selected' : '' }}>Bulan Lalu</option>
                    <option value="this_year" {{ $preset === 'this_year' ? 'selected' : '' }}>Tahun Ini</option>
                </select>
            </form>
        </div>

        <!-- Accounting Notice Alert -->
        <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2.5">
                <x-icon name="alert" class="w-4 h-4 text-amber-600 shrink-0" />
                <span>
                    <strong>Pemberitahuan Akuntansi:</strong> Angka keuntungan di bawah adalah <strong>Keuntungan Kotor (Gross Profit)</strong> yang dihitung dari Penjualan Bersih dikurangi Total HPP Terjual. Belum mencakup biaya operasional tidak langsung (sewa ruko, listrik, gaji pegawai).
                </span>
            </div>
            <span class="text-[11px] font-mono text-amber-800 shrink-0">
                Periode: {{ $start->format('d M Y') }} - {{ $end->format('d M Y') }}
            </span>
        </div>

        <!-- 4 PROFIT METRIC CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Penjualan Bersih -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Penjualan Bersih</span>
                <div class="text-2xl font-black text-slate-900 tracking-tight mt-1">
                    Rp {{ number_format($summary['total_net'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Diskon: <strong class="text-rose-600">- Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Total HPP Terjual (COGS) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total HPP Terjual (COGS)</span>
                <div class="text-2xl font-black text-slate-700 tracking-tight mt-1">
                    Rp {{ number_format($summary['total_hpp'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Snapshot biaya perolehan transaksi
                </div>
            </div>

            <!-- Keuntungan Kotor -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Keuntungan Kotor (Gross)</span>
                <div class="text-2xl font-black text-emerald-600 tracking-tight mt-1">
                    Rp {{ number_format($summary['total_gross_profit'], 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Penjualan Bersih - HPP Terjual
                </div>
            </div>

            <!-- Margin Kotor (%) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Margin Keuntungan Kotor</span>
                <div class="text-2xl font-black text-rose-600 tracking-tight mt-1">
                    {{ $summary['margin_percentage'] }}%
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Dari {{ $summary['count'] }} transaksi faktur
                </div>
            </div>
        </div>

        <!-- Profit Charts (Daily Trend & Category Distribution) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Grafik Keuntungan Kotor Harian</h2>
                        <p class="text-xs text-slate-400">Laba kotor harian pada periode terpilih</p>
                    </div>
                </div>
                <div class="h-64 relative">
                    <canvas id="profitTrendChart"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Kontribusi Laba per Kategori</h2>
                    <p class="text-xs text-slate-400 mb-4">Proporsi keuntungan kotor per segmen</p>
                    <div class="h-48 relative">
                        <canvas id="categoryProfitChart"></canvas>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 space-y-1.5 text-xs text-slate-600">
                    @foreach($profitByCategory as $cat)
                        <div class="flex items-center justify-between">
                            <span class="truncate">{{ $cat->category_name }}:</span>
                            <strong class="font-bold text-emerald-700">Rp {{ number_format($cat->total_gross_profit, 0, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB BUTTONS -->
        <div class="flex items-center gap-2 border-b border-slate-200 pb-2">
            <button type="button" @click="activeTab = 'products'" :class="activeTab === 'products' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">
                Keuntungan per Produk Terjual
            </button>
            <button type="button" @click="activeTab = 'hpp_catalog'" :class="activeTab === 'hpp_catalog' ? 'bg-rose-500 text-white shadow-sm' : 'bg-white text-slate-700 hover:bg-slate-50'" class="px-4 py-2 rounded-xl text-xs font-bold transition-all">
                HPP Berjalan Produk & Varian (Moving Average)
            </button>
        </div>

        <!-- TAB 1: PROFIT PER PRODUCT TABLE -->
        <div x-show="activeTab === 'products'" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Nama Produk</th>
                            <th class="py-3.5 px-6">SKU</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6 text-center">Qty Terjual</th>
                            <th class="py-3.5 px-6 text-right">Total Penjualan</th>
                            <th class="py-3.5 px-6 text-right">Total HPP Terjual</th>
                            <th class="py-3.5 px-6 text-right">Keuntungan Kotor</th>
                            <th class="py-3.5 px-6 text-center">Margin (%)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($profitByProduct['products'] as $prod)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                                    {{ $prod['product_name'] }}
                                </td>
                                <td class="py-4 px-6 font-mono text-slate-500">
                                    {{ $prod['sku'] }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px]">
                                        {{ $prod['category'] }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-slate-800">
                                    {{ $prod['quantity_sold'] }} pcs
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($prod['total_sales'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono text-slate-500">
                                    Rp {{ number_format($prod['total_hpp'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-black text-emerald-600 text-sm">
                                    Rp {{ number_format($prod['gross_profit'], 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                                        {{ $prod['margin_percentage'] }}%
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-10 text-center text-slate-400">
                                    Belum ada produk yang terjual dalam periode ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: HPP BERJALAN PER PRODUK & VARIAN -->
        <div x-show="activeTab === 'hpp_catalog'" style="display: none;" class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">SKU</th>
                            <th class="py-3.5 px-6">Nama Produk</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6 text-right">Harga Jual</th>
                            <th class="py-3.5 px-6 text-right">HPP Berjalan Rata-Rata</th>
                            <th class="py-3.5 px-6 text-center">Stok Saat Ini</th>
                            <th class="py-3.5 px-6 text-right">Valuasi Stok HPP</th>
                            <th class="py-3.5 px-6 text-center">Potensi Margin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($productsHpp as $p)
                            @php
                                $stock = $p->total_stock;
                                $hpp = (float)$p->current_hpp;
                                $price = (float)$p->selling_price;
                                $val = $stock * $hpp;
                                $potentialMargin = $price > 0 ? round((($price - $hpp) / $price) * 100, 1) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">{{ $p->sku }}</td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $p->name }}</div>
                                    <div class="text-[11px] text-slate-400">
                                        @foreach($p->variants as $var)
                                            <span class="inline-block mr-2">{{ $var->variant_name }}: HPP Rp {{ number_format($var->current_hpp, 0, ',', '.') }}</span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="py-4 px-6">{{ $p->category->name ?? '-' }}</td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($p->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right font-mono font-bold text-slate-600 bg-rose-50/20">
                                    Rp {{ number_format($p->current_hpp, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-center font-bold text-slate-800">{{ $stock }} pcs</td>
                                <td class="py-4 px-6 text-right font-mono font-black text-slate-900">
                                    Rp {{ number_format($val, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $potentialMargin }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $productsHpp->links() }}
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dailyData = @json($dailyTrends);
            const categoryData = @json($profitByCategory);

            // 1. Daily Profit Trend Line Chart
            const trendCtx = document.getElementById('profitTrendChart');
            if (trendCtx) {
                new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: dailyData.map(d => d.sale_date),
                        datasets: [
                            {
                                label: 'Keuntungan Kotor (Rp)',
                                data: dailyData.map(d => d.daily_profit),
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                                tension: 0.3,
                                fill: true,
                            },
                            {
                                label: 'Penjualan Bersih (Rp)',
                                data: dailyData.map(d => d.daily_sales),
                                borderColor: '#e11d48',
                                borderDash: [4, 4],
                                fill: false,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } } },
                        },
                        scales: {
                            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // 2. Category Profit Doughnut
            const catCtx = document.getElementById('categoryProfitChart');
            if (catCtx) {
                new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.map(c => c.category_name),
                        datasets: [{
                            data: categoryData.map(c => c.total_gross_profit),
                            backgroundColor: ['#10b981', '#059669', '#34d399', '#6ee7b7', '#a7f3d0'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: { legend: { display: false } }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
