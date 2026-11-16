<x-layouts.app>
    <x-slot:title>Riwayat Penjualan — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Riwayat Penjualan</h1>
                <p class="text-xs text-slate-500 mt-0.5">Daftar seluruh faktur transaksi kasir, metode pembayaran, dan status.</p>
            </div>
            <a href="{{ route('pos.index') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="cart" class="w-4 h-4" />
                <span>Transaksi Baru (POS)</span>
            </a>
        </div>

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('sales.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor invoice, nama pelanggan..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 text-xs transition-all outline-none">
                </div>

                <div class="flex flex-wrap gap-2">
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">

                    <select name="payment_method" class="px-3 py-2 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none">
                        <option value="">Semua Metode</option>
                        <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Tunai (Cash)</option>
                        <option value="transfer" {{ request('payment_method') === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                        <option value="qris" {{ request('payment_method') === 'qris' ? 'selected' : '' }}>QRIS</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                        Cari
                    </button>

                    @if(request()->anyFilled(['search', 'start_date', 'end_date', 'payment_method']))
                        <a href="{{ route('sales.index') }}" class="px-3 py-2.5 bg-slate-100 text-slate-600 rounded-xl text-xs font-semibold flex items-center">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Sales Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">No. Invoice</th>
                            <th class="py-3.5 px-6">Tanggal & Waktu</th>
                            <th class="py-3.5 px-6">Kasir</th>
                            <th class="py-3.5 px-6">Pelanggan</th>
                            <th class="py-3.5 px-6">Metode</th>
                            <th class="py-3.5 px-6 text-right">Total Belanja</th>
                            @can('view-profit')
                            <th class="py-3.5 px-6 text-right">Keuntungan Kotor</th>
                            @endcan
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($sales as $sale)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <a href="{{ route('sales.show', $sale->id) }}" class="text-rose-600 hover:text-rose-700">
                                        {{ $sale->invoice_number }}
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    {{ $sale->sale_date->translatedFormat('d M Y') }}
                                    <span class="text-[10px] text-slate-400 block">{{ $sale->created_at->format('H:i') }} WITA</span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-800">
                                    {{ $sale->user->name ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    {{ $sale->customer_name ?? 'Umum' }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ $sale->payment_method }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right font-black text-slate-900 text-sm">
                                    Rp {{ number_format($sale->grand_total, 0, ',', '.') }}
                                </td>
                                @can('view-profit')
                                <td class="py-4 px-6 text-right font-bold text-emerald-600">
                                    Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}
                                    <span class="text-[10px] text-slate-400 block font-normal">({{ $sale->gross_margin_percentage }}%)</span>
                                </td>
                                @endcan
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Lunas
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('sales.show', $sale->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Lihat Detail">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('sales.receipt', $sale->id) }}" target="_blank" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Cetak Struk">
                                            <x-icon name="printer" class="w-4 h-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    Belum ada transaksi penjualan yang tercatat.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $sales->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
