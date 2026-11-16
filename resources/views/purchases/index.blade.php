<x-layouts.app>
    <x-slot:title>Barang Masuk (Pembelian) — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Penerimaan Barang Masuk (PO)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Catat faktur pembelian dari supplier, penambahan stok, dan pembaruan HPP Moving Average.</p>
            </div>
            <a href="{{ route('purchases.create') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Penerimaan Barang Baru</span>
            </a>
        </div>

        <!-- Filter bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('purchases.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor PO..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 text-xs transition-all outline-none">
                </div>

                <div class="flex flex-wrap gap-2">
                    <select name="supplier_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none">
                        <option value="">Semua Supplier</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="px-3 py-2 rounded-xl border border-slate-200 text-xs text-slate-700 outline-none">

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 text-white rounded-xl text-xs font-bold transition-all">
                        Cari
                    </button>
                </div>
            </form>
        </div>

        <!-- Purchases Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">No. Pembelian (PO)</th>
                            <th class="py-3.5 px-6">Tanggal Terima</th>
                            <th class="py-3.5 px-6">Supplier</th>
                            <th class="py-3.5 px-6">Petugas</th>
                            <th class="py-3.5 px-6 text-center">Jumlah Item</th>
                            <th class="py-3.5 px-6 text-right">Biaya Tambahan</th>
                            <th class="py-3.5 px-6 text-right">Total Tagihan</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($purchases as $p)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <a href="{{ route('purchases.show', $p->id) }}" class="text-rose-600 hover:text-rose-700">
                                        {{ $p->purchase_number }}
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    {{ $p->purchase_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-800">
                                    {{ $p->supplier->name ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    {{ $p->user->name ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold">
                                        {{ $p->items->count() }} Varian ({{ $p->items->sum('quantity') }} pcs)
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right font-mono text-slate-500">
                                    Rp {{ number_format($p->additional_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-right font-black text-slate-900 text-sm">
                                    Rp {{ number_format($p->grand_total, 0, ',', '.') }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                        Diterima & Stok Masuk
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('purchases.show', $p->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors inline-block" title="Lihat Rincian">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-400">
                                    Belum ada transaksi penerimaan barang masuk.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $purchases->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
