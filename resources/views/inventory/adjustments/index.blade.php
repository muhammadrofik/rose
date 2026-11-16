<x-layouts.app>
    <x-slot:title>Penyesuaian Stok & Opname — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Penyesuaian Stok (Stock Opname)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Catat penyesuaian fisik persediaan, barang rusak, hilang, penggunaan internal, atau audit opname.</p>
            </div>
            <a href="{{ route('inventory.adjustments.create') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Buat Penyesuaian Baru</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">No. Penyesuaian</th>
                            <th class="py-3.5 px-6">Tanggal</th>
                            <th class="py-3.5 px-6">Jenis Alasan</th>
                            <th class="py-3.5 px-6">Petugas</th>
                            <th class="py-3.5 px-6 text-center">Jumlah Varian</th>
                            <th class="py-3.5 px-6">Keterangan</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($adjustments as $adj)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-mono font-bold text-slate-900">
                                    <a href="{{ route('inventory.adjustments.show', $adj->id) }}" class="text-rose-600 hover:text-rose-700">
                                        {{ $adj->adjustment_number }}
                                    </a>
                                </td>
                                <td class="py-4 px-6 text-slate-600">
                                    {{ $adj->adjustment_date->translatedFormat('d M Y') }}
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-800">
                                        {{ $adj->type_label }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-semibold text-slate-800">
                                    {{ $adj->user->name ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-bold">
                                        {{ $adj->items->count() }} Item
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-slate-500 max-w-xs truncate">
                                    {{ $adj->notes ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <a href="{{ route('inventory.adjustments.show', $adj->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors inline-block" title="Lihat">
                                        <x-icon name="eye" class="w-4 h-4" />
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    Belum ada catatan penyesuaian stok.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
