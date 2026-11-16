<x-layouts.app>
    <x-slot:title>Faktur Penjualan — {{ $sale->invoice_number }}</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Riwayat Penjualan</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Detail Transaksi Penjualan</h1>
                <p class="text-xs font-mono text-slate-500">Invoice: {{ $sale->invoice_number }}</p>
            </div>
            <a href="{{ route('sales.receipt', $sale->id) }}" target="_blank" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition-all">
                <x-icon name="printer" class="w-4 h-4" />
                <span>Cetak Struk Thermal</span>
            </a>
        </div>

        <!-- Transaction Overview Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 space-y-6">
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-4 rounded-2xl bg-slate-50 text-xs">
                <div>
                    <span class="text-slate-400 block">Tanggal & Waktu</span>
                    <strong class="text-slate-900">{{ $sale->sale_date->translatedFormat('d F Y') }}</strong>
                    <span class="text-[10px] text-slate-500 block">{{ $sale->created_at->format('H:i:s') }} WITA</span>
                </div>
                <div>
                    <span class="text-slate-400 block">Petugas Kasir</span>
                    <strong class="text-slate-900">{{ $sale->user->name ?? '-' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Nama Pelanggan</span>
                    <strong class="text-slate-900">{{ $sale->customer_name ?? 'Umum' }}</strong>
                </div>
                <div>
                    <span class="text-slate-400 block">Metode Pembayaran</span>
                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $sale->payment_method === 'cash' ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800' }}">
                        {{ $sale->payment_method }}
                    </span>
                </div>
            </div>

            <!-- Items Table -->
            <div class="border border-slate-200 rounded-2xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4">Barang & Varian</th>
                            <th class="py-3 px-4 text-center">Jumlah</th>
                            <th class="py-3 px-4 text-right">Harga Satuan</th>
                            @can('view-profit')
                            <th class="py-3 px-4 text-right">HPP Snapshot</th>
                            @endcan
                            <th class="py-3 px-4 text-right">Alokasi Diskon</th>
                            <th class="py-3 px-4 text-right">Subtotal</th>
                            @can('view-profit')
                            <th class="py-3 px-4 text-right">Keuntungan Item</th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($sale->items as $item)
                            <tr>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900">{{ $item->product->name ?? 'Produk' }}</div>
                                    <div class="text-[11px] text-slate-400">Varian: {{ $item->variant->variant_name ?? '-' }} ({{ $item->variant->sku ?? '-' }})</div>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    {{ $item->quantity }}
                                </td>
                                <td class="py-3 px-4 text-right">
                                    Rp {{ number_format($item->unit_price, 0, ',', '.') }}
                                </td>
                                @can('view-profit')
                                <td class="py-3 px-4 text-right font-mono text-slate-500">
                                    Rp {{ number_format($item->hpp_snapshot, 0, ',', '.') }}
                                </td>
                                @endcan
                                <td class="py-3 px-4 text-right text-rose-600">
                                    {{ $item->allocated_transaction_discount > 0 ? '- Rp ' . number_format($item->allocated_transaction_discount, 0, ',', '.') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-right font-bold text-slate-900">
                                    Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                                </td>
                                @can('view-profit')
                                <td class="py-3 px-4 text-right font-extrabold text-emerald-600">
                                    Rp {{ number_format($item->item_gross_profit, 0, ',', '.') }}
                                </td>
                                @endcan
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Financial Calculation Summary -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                <div class="space-y-2 text-xs">
                    <h4 class="font-bold text-slate-700 uppercase tracking-wider">Catatan Transaksi</h4>
                    <p class="text-slate-500 p-3 rounded-xl bg-slate-50 border border-slate-100">
                        {{ $sale->notes ?: 'Tidak ada catatan tambahan untuk transaksi ini.' }}
                    </p>

                    @php
                        $payment = $sale->payments->first();
                    @endphp
                    @if($payment)
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 space-y-1">
                            <span class="font-bold text-slate-700 block">Rincian Pembayaran:</span>
                            <div class="flex justify-between text-slate-600">
                                <span>Nominal Bayar:</span>
                                <strong class="text-slate-900">Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</strong>
                            </div>
                            <div class="flex justify-between text-slate-600">
                                <span>Kembalian:</span>
                                <strong class="text-emerald-700">Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 text-xs">
                    <div class="flex justify-between text-slate-500">
                        <span>Total Nilai Barang (Gross):</span>
                        <span class="font-bold text-slate-800">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
                    </div>
                    @if($sale->discount_amount > 0)
                        <div class="flex justify-between text-rose-600 font-semibold">
                            <span>Diskon Penjualan:</span>
                            <span>- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between font-extrabold text-sm text-slate-900 pt-1 border-t border-slate-200">
                        <span>Total Bersih (Grand Total):</span>
                        <span class="text-rose-600 text-base">Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
                    </div>

                    @can('view-profit')
                    <div class="pt-3 mt-3 border-t border-slate-200/80 space-y-1.5">
                        <div class="flex justify-between text-slate-500">
                            <span>Total HPP Terjual (Cost):</span>
                            <span class="font-bold font-mono text-slate-700">Rp {{ number_format($sale->total_hpp, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-emerald-700 font-black text-sm">
                            <span>Keuntungan Kotor (Gross Profit):</span>
                            <span>Rp {{ number_format($sale->gross_profit, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-slate-500 text-[11px]">
                            <span>Margin Keuntungan Kotor:</span>
                            <span class="font-bold text-emerald-600">{{ $sale->gross_margin_percentage }}%</span>
                        </div>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
