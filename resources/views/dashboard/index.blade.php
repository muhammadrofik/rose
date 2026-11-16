<x-layouts.app>
    <x-slot:title>Dashboard — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <!-- Page Title & Quick Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Ringkasan Toko</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pantau kinerja penjualan, stok barang, dan profitabilitas real-time.</p>
            </div>
            <div class="flex items-center gap-2.5">
                <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                    <x-icon name="cart" class="w-4 h-4" />
                    <span>Transaksi Baru (POS)</span>
                </a>
                @can('manage-inventory')
                <a href="{{ route('purchases.create') }}" class="px-3.5 py-2.5 bg-white border border-slate-200 hover:border-slate-300 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                    <x-icon name="in" class="w-4 h-4 text-slate-500" />
                    <span>Barang Masuk</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- 8 STAT CARDS GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Card 1: Penjualan Bulan Ini -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-rose-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Penjualan Bulan Ini</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">
                        <x-icon name="trending-up" class="w-3 h-3" />
                        {{ $totalOrdersMonth }} Order
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    Rp {{ number_format($totalSalesMonth, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-slate-400 flex items-center gap-1">
                    <span>Hari ini:</span>
                    <strong class="text-slate-700">Rp {{ number_format($totalSalesToday, 0, ',', '.') }}</strong>
                    <span class="text-slate-400">({{ $totalOrdersToday }} transaksi)</span>
                </div>
            </div>

            <!-- Card 2: Keuntungan Kotor Bulan Ini (Admin) -->
            @can('view-profit')
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Keuntungan Kotor Bulan Ini</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700">
                        <x-icon name="profit" class="w-3 h-3" />
                        {{ $totalSalesMonth > 0 ? round(($grossProfitMonth / $totalSalesMonth) * 100, 1) : 0 }}% Margin
                    </span>
                </div>
                <div class="text-2xl font-black text-emerald-600 tracking-tight">
                    Rp {{ number_format($grossProfitMonth, 0, ',', '.') }}
                </div>
                <div class="mt-2 text-xs text-slate-400 flex items-center gap-1">
                    <span>Hari ini:</span>
                    <strong class="text-emerald-700">Rp {{ number_format($grossProfitToday, 0, ',', '.') }}</strong>
                </div>
            </div>
            @else
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Pesanan Selesai</span>
                    <span class="p-2 rounded-xl bg-slate-100 text-slate-600">
                        <x-icon name="check" class="w-4 h-4" />
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    {{ $totalOrdersMonth }}
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Hari ini: <strong class="text-slate-700">{{ $totalOrdersToday }} pesanan</strong>
                </div>
            </div>
            @endcan

            <!-- Card 3: Stok Menipis (Needs Attention) -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Menipis</span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold {{ $lowStockCount > 0 ? 'bg-amber-100 text-amber-800' : 'bg-emerald-50 text-emerald-700' }}">
                        <x-icon name="alert" class="w-3 h-3" />
                        {{ $lowStockCount }} Item
                    </span>
                </div>
                <div class="text-2xl font-black {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-slate-900' }} tracking-tight">
                    {{ $lowStockCount }}
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Perlu pengadaan ulang supplier
                </div>
            </div>

            <!-- Card 4: Total Nilai Persediaan / Produk Aktif -->
            <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm relative overflow-hidden group hover:border-rose-300 transition-all">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">
                        @can('view-hpp') Nilai Persediaan HPP @else Total Unit Stok @endcan
                    </span>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                        <x-icon name="products" class="w-3 h-3" />
                        {{ $totalActiveProducts }} Produk
                    </span>
                </div>
                <div class="text-2xl font-black text-slate-900 tracking-tight">
                    @can('view-hpp')
                        Rp {{ number_format($totalInventoryValuation, 0, ',', '.') }}
                    @else
                        {{ number_format($totalStockUnits, 0, ',', '.') }} Pcs
                    @endcan
                </div>
                <div class="mt-2 text-xs text-slate-400">
                    Total unit tersedia: <strong class="text-slate-700">{{ number_format($totalStockUnits, 0, ',', '.') }} pcs</strong>
                </div>
            </div>
        </div>

        <!-- CHARTS SECTION (Revenue Bar & Category Breakdown) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Monthly Revenue Chart -->
            <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Performa Penjualan & Pendapatan</h2>
                        <p class="text-xs text-slate-400">Tren penjualan 6 bulan terakhir</p>
                    </div>
                    <span class="text-xs font-semibold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg">WITA</span>
                </div>
                <div class="h-64 relative">
                    <canvas id="monthlyRevenueChart"></canvas>
                </div>
            </div>

            <!-- Category Stock Breakdown Chart -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900 mb-1">Distribusi Stok per Kategori</h2>
                    <p class="text-xs text-slate-400 mb-4">Proporsi unit produk di toko</p>
                    <div class="h-48 relative">
                        <canvas id="categoryStockChart"></canvas>
                    </div>
                </div>
                <div class="pt-4 border-t border-slate-100 grid grid-cols-2 gap-2 text-xs text-slate-600">
                    @foreach($categoryStock as $cat)
                        <div class="flex items-center justify-between">
                            <span class="truncate">{{ $cat['name'] }}:</span>
                            <strong class="font-bold text-slate-800">{{ $cat['units'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- NEEDS ATTENTION TABLE (Image 1 Style) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center font-bold">
                        <x-icon name="alert" class="w-4 h-4" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-slate-900">Perlu Perhatian (Stok Menipis)</h2>
                        <p class="text-xs text-slate-400">Varian produk yang mencapai batas minimum pemesanan ulang</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-800">
                    {{ $lowStockCount }} item
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50/60 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3 px-6">SKU</th>
                            <th class="py-3 px-6">Nama Produk & Varian</th>
                            <th class="py-3 px-6">Kategori</th>
                            <th class="py-3 px-6 text-center">Stok Saat Ini</th>
                            <th class="py-3 px-6 text-center">Batas Minimum</th>
                            <th class="py-3 px-6 text-center">Status</th>
                            @can('manage-inventory')
                            <th class="py-3 px-6 text-right">Aksi</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($lowStockVariants->take(5) as $v)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono text-[11px] text-slate-500">{{ $v->sku }}</td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900">{{ $v->product->name }}</div>
                                    <div class="text-[11px] text-slate-400">Varian: {{ $v->variant_name }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-600 text-[11px]">
                                        {{ $v->product->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="font-extrabold text-sm {{ $v->stock == 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                        {{ $v->stock }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center text-slate-400 font-semibold">
                                    {{ $v->product->min_stock }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($v->stock == 0)
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700">Habis</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-800">Menipis</span>
                                    @endif
                                </td>
                                @can('manage-inventory')
                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('purchases.create') }}" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 font-bold text-xs transition-colors">
                                        <x-icon name="plus" class="w-3.5 h-3.5" />
                                        <span>Restock</span>
                                    </a>
                                </td>
                                @endcan
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-icon name="check" class="w-8 h-8 text-emerald-500 mb-1" />
                                        <p class="font-semibold text-slate-700">Semua stok berada dalam batas aman</p>
                                        <p class="text-[11px] text-slate-400">Tidak ada produk dengan stok menipis saat ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2 COLUMNS: RECENT TRANSACTIONS & TOP SELLING PRODUCTS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 5 Transaksi Terbaru -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">5 Transaksi Kasir Terbaru</h2>
                        <p class="text-xs text-slate-400">Penjualan terakhir yang tercatat di sistem</p>
                    </div>
                    <a href="{{ route('sales.index') }}" class="text-xs font-bold text-rose-600 hover:text-rose-700">Lihat Semua</a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentSales as $sale)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 font-bold">
                                    <x-icon name="receipt" class="w-4 h-4 text-slate-500" />
                                </div>
                                <div>
                                    <a href="{{ route('sales.show', $sale->id) }}" class="font-bold text-slate-900 hover:text-rose-600 transition-colors">
                                        {{ $sale->invoice_number }}
                                    </a>
                                    <p class="text-[11px] text-slate-400">
                                        {{ $sale->sale_date->translatedFormat('d M Y') }} • Kasir: {{ $sale->user->name ?? '-' }}
                                    </p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-extrabold text-slate-900">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </div>
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                    {{ $sale->payment_method }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">Belum ada transaksi penjualan</div>
                    @endforelse
                </div>
            </div>

            <!-- 5 Produk Terlaris -->
            <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-900">5 Produk Terlaris (Bulan Ini)</h2>
                        <p class="text-xs text-slate-400">Paling banyak terjual berdasarkan kuantitas</p>
                    </div>
                    @can('view-reports')
                    <a href="{{ route('reports.index', ['tab' => 'profit']) }}" class="text-xs font-bold text-rose-600 hover:text-rose-700">Analisis Profit</a>
                    @endcan
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($topProducts as $idx => $prod)
                        <div class="py-3 flex items-center justify-between text-xs">
                            <div class="flex items-center gap-3">
                                <div class="w-7 h-7 rounded-full bg-rose-100 text-rose-700 font-extrabold flex items-center justify-center text-xs">
                                    #{{ $idx + 1 }}
                                </div>
                                <div>
                                    <div class="font-bold text-slate-900">{{ $prod->name }}</div>
                                    <p class="text-[11px] text-slate-400">{{ $prod->category_name ?? '-' }} • SKU: {{ $prod->sku }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="font-bold text-slate-900">{{ $prod->total_qty }} pcs</div>
                                <p class="text-[11px] text-slate-500">Rp {{ number_format($prod->total_revenue, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="py-6 text-center text-slate-400 text-xs">Belum ada data penjualan produk bulan ini</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Chart.js Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const monthlyData = @json($monthlyRevenue);
            const categoryData = @json($categoryStock);

            // 1. Revenue Chart
            const revCtx = document.getElementById('monthlyRevenueChart');
            if (revCtx) {
                new Chart(revCtx, {
                    type: 'bar',
                    data: {
                        labels: monthlyData.map(d => d.label),
                        datasets: [
                            {
                                label: 'Pendapatan (Rp)',
                                data: monthlyData.map(d => d.total),
                                backgroundColor: '#e11d48',
                                borderRadius: 8,
                                maxBarThickness: 32,
                            },
                            @can('view-profit')
                            {
                                label: 'Keuntungan Kotor (Rp)',
                                data: monthlyData.map(d => d.profit),
                                backgroundColor: '#10b981',
                                borderRadius: 8,
                                maxBarThickness: 32,
                            }
                            @endcan
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'top', labels: { boxWidth: 12, font: { family: 'Plus Jakarta Sans', size: 11 } } },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        let val = context.raw || 0;
                                        return context.dataset.label + ': Rp ' + val.toLocaleString('id-ID');
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Plus Jakarta Sans', size: 10 },
                                    callback: function(value) {
                                        if (value >= 1000000) return 'Rp ' + (value / 1000000).toFixed(1) + 'M';
                                        if (value >= 1000) return 'Rp ' + (value / 1000).toFixed(0) + 'K';
                                        return 'Rp ' + value;
                                    }
                                }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Plus Jakarta Sans', size: 11 } }
                            }
                        }
                    }
                });
            }

            // 2. Category Stock Doughnut Chart
            const catCtx = document.getElementById('categoryStockChart');
            if (catCtx) {
                new Chart(catCtx, {
                    type: 'doughnut',
                    data: {
                        labels: categoryData.map(c => c.name),
                        datasets: [{
                            data: categoryData.map(c => c.units),
                            backgroundColor: ['#e11d48', '#f43f5e', '#fb7185', '#fda4af', '#cbd5e1'],
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '70%',
                        plugins: {
                            legend: { display: false }
                        }
                    }
                });
            }
        });
    </script>
</x-layouts.app>
