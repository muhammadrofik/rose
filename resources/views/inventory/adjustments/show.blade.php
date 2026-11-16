<x-layouts.app>
    <x-slot:title>Rincian Penyesuaian — {{ $adjustment->adjustment_number }}</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('inventory.adjustments.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Riwayat Penyesuaian</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rincian Penyesuaian Stok</h1>
                <p class="text-xs font-mono text-slate-500">Nomor: {{ $adjustment->adjustment_number }}</p>
            </div>
            <a href="{{ route('inventory.movements') }}" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all">
                <x-icon name="history" class="w-4 h-4" />
                <span>Lihat di Ledger Mutasi</span>
            </a>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                <div>
                    <span class="text-slate-400 block">Jenis Penyesuaian</span>
                    <strong class="text-slate-900">{{ $adjustment->type_label }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Tanggal</span>
                    <strong class="text-slate-900">{{ $adjustment->adjustment_date->translatedFormat('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Petugas Opname</span>
                    <strong class="text-slate-900">{{ $adjustment->user->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Waktu Tercatat</span>
                    <strong class="text-slate-900">{{ $adjustment->created_at->format('H:i:s') }} WITA</strong>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Barang & Varian</th>
                            <th class="py-3 px-4 text-center">Stok Sistem</th>
                            <th class="py-3 px-4 text-center">Stok Fisik Opname</th>
                            <th class="py-3 px-4 text-center">Selisih Penyesuaian</th>
                            @can('view-hpp')
                            <th class="py-3 px-4 text-right">Biaya Satuan (HPP)</th>
                            @endcan
                            <th class="py-3 px-4">Alasan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($adjustment->items as $item)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $item->variant->product->name ?? 'Produk' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->variant->variant_name ?? '-' }} ({{ $item->variant->sku ?? '-' }})</div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold font-mono text-slate-500">
                                    {{ $item->system_stock }}
                                </td>
                                <td class="py-3 px-4 text-center font-extrabold font-mono text-slate-900">
                                    {{ $item->physical_stock }}
                                </td>
                                <td class="py-3 px-4 text-center font-black font-mono text-sm {{ $item->difference < 0 ? 'text-rose-600' : ($item->difference > 0 ? 'text-emerald-600' : 'text-slate-400') }}">
                                    {{ $item->difference > 0 ? '+' . $item->difference : $item->difference }}
                                </td>
                                @can('view-hpp')
                                <td class="py-3 px-4 text-right font-mono text-slate-600">
                                    Rp {{ number_format($item->unit_cost, 0, ',', '.') }}
                                </td>
                                @endcan
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $item->reason ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($adjustment->notes)
                <div class="text-xs p-4 rounded-2xl bg-slate-50 border border-slate-100">
                    <span class="font-bold text-slate-700 block mb-1">Catatan Tambahan:</span>
                    <p class="text-slate-600">{{ $adjustment->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
