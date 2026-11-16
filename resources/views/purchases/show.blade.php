<x-layouts.app>
    <x-slot:title>Rincian PO — {{ $purchase->purchase_number }}</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('purchases.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Riwayat PO</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rincian Penerimaan Barang (PO)</h1>
                <p class="text-xs font-mono text-slate-500">Nomor: {{ $purchase->purchase_number }}</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">
                    Stok Diterima
                </span>
            </div>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <!-- Metadata Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                <div>
                    <span class="text-slate-400 block">Supplier</span>
                    <strong class="text-slate-900 text-sm">{{ $purchase->supplier->name ?? '-' }}</strong>
                    <span class="text-[10px] text-slate-500 block">{{ $purchase->supplier->phone ?? '' }}</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Tanggal Penerimaan</span>
                    <strong class="text-slate-900">{{ $purchase->purchase_date->translatedFormat('d F Y') }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Petugas Penerima</span>
                    <strong class="text-slate-900">{{ $purchase->user->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Total Unit Diterima</span>
                    <strong class="text-slate-900 text-sm">{{ $purchase->items->sum('quantity') }} pcs</strong>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Nama Barang & Varian</th>
                            <th class="py-3 px-4 text-center">Qty Diterima</th>
                            <th class="py-3 px-4 text-right">Harga Beli Beli</th>
                            <th class="py-3 px-4 text-right">Diskon Item</th>
                            <th class="py-3 px-4 text-right">Alokasi Ongkir</th>
                            <th class="py-3 px-4 text-right">Biaya Bersih Satuan</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($purchase->items as $item)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $item->variant->variant_name ?? '-' }} ({{ $item->variant->sku ?? '-' }})</div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono">
                                    Rp {{ number_format($item->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-rose-600">
                                    {{ $item->discount_amount > 0 ? '- Rp ' . number_format($item->discount_amount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono text-slate-500">
                                    + Rp {{ number_format($item->allocated_additional_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900 bg-rose-50/30">
                                    Rp {{ number_format($item->unit_net_cost, 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Summary Box -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-2">
                <div class="text-xs space-y-2">
                    <h4 class="font-bold text-slate-700 uppercase tracking-wider">Catatan Pengadaan</h4>
                    <p class="text-slate-500 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        {{ $purchase->notes ?: 'Tidak ada catatan tambahan untuk penerimaan barang ini.' }}
                    </p>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Total Nilai Barang (Gross):</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($purchase->total_items_cost, 0, ',', '.') }}</span>
                    </div>
                    @if($purchase->discount_amount > 0)
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>Diskon Faktur Global:</span>
                            <span>- Rp {{ number_format($purchase->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-slate-500">
                        <span>Biaya Tambahan / Ongkos Kirim:</span>
                        <span class="font-bold text-slate-800">+ Rp {{ number_format($purchase->additional_cost, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between font-black text-sm text-slate-900 pt-2 border-t border-slate-200">
                        <span>Total Pembelian (Grand Total):</span>
                        <span class="text-rose-600 text-base">Rp {{ number_format($purchase->grand_total, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
