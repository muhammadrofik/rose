<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk — {{ $sale->invoice_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        @page {
            margin: 0;
            size: auto;
        }
        body {
            background-color: #f1f5f9;
        }
        @media print {
            body {
                background-color: #ffffff;
            }
            .no-print {
                display: none !important;
            }
            #thermal-paper {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="p-4 flex flex-col items-center justify-center min-h-screen text-slate-900" x-data="{ size: '58mm' }">
    <!-- Controls (No Print) -->
    <div class="mb-4 flex items-center gap-2 no-print bg-white p-2 rounded-2xl shadow-sm border border-slate-200 text-xs">
        <label class="font-bold text-slate-600 ml-2">Lebar Kertas:</label>
        <button type="button" @click="size = '58mm'" :class="size === '58mm' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-xl font-bold transition-all">58 mm</button>
        <button type="button" @click="size = '80mm'" :class="size === '80mm' ? 'bg-rose-500 text-white' : 'bg-slate-100 text-slate-700'" class="px-3 py-1.5 rounded-xl font-bold transition-all">80 mm</button>
        <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl font-bold flex items-center gap-1.5 ml-2 transition-all">
            <x-icon name="printer" class="w-3.5 h-3.5" />
            <span>Cetak Sekarang</span>
        </button>
    </div>

    <!-- Thermal Paper Box -->
    <div id="thermal-paper" :class="size === '58mm' ? 'w-[280px] text-[11px]' : 'w-[360px] text-xs'" class="bg-white p-5 rounded-2xl shadow-md border border-slate-200 font-mono leading-tight">
        <!-- Header -->
        <div class="text-center space-y-1 border-b border-dashed border-slate-400 pb-3">
            <h1 class="font-extrabold text-sm uppercase tracking-wide">{{ \App\Models\Setting::get('store_name', 'Rose Colection') }}</h1>
            <p class="text-[10px] text-slate-600">{{ \App\Models\Setting::get('store_address', 'Jl. Boulevard No. 18, Panakkukang, Makassar') }}</p>
            <p class="text-[10px] text-slate-600">Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
        </div>

        <!-- Meta -->
        <div class="py-2 border-b border-dashed border-slate-400 space-y-0.5 text-[10px]">
            <div class="flex justify-between">
                <span>No. Invoice:</span>
                <span class="font-bold">{{ $sale->invoice_number }}</span>
            </div>
            <div class="flex justify-between">
                <span>Waktu:</span>
                <span>{{ $sale->created_at->format('d/m/Y H:i') }} WITA</span>
            </div>
            <div class="flex justify-between">
                <span>Kasir:</span>
                <span>{{ $sale->user->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pelanggan:</span>
                <span>{{ $sale->customer_name ?? 'Umum' }}</span>
            </div>
        </div>

        <!-- Items Table -->
        <div class="py-2.5 border-b border-dashed border-slate-400 space-y-2">
            @foreach($sale->items as $item)
                <div>
                    <div class="font-bold truncate">{{ $item->product->name ?? '' }}</div>
                    <div class="text-[10px] text-slate-500">Var: {{ $item->variant->variant_name ?? '-' }}</div>
                    <div class="flex justify-between text-[10px] pt-0.5">
                        <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                        <span class="font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Financial Summary -->
        <div class="py-2.5 border-b border-dashed border-slate-400 space-y-1 text-[10px]">
            <div class="flex justify-between">
                <span>Total Belanja:</span>
                <span>Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</span>
            </div>
            @if($sale->discount_amount > 0)
                <div class="flex justify-between">
                    <span>Diskon:</span>
                    <span>- Rp {{ number_format($sale->discount_amount, 0, ',', '.') }}</span>
                </div>
            @endif
            <div class="flex justify-between font-black text-xs pt-1 border-t border-slate-300">
                <span>GRAND TOTAL:</span>
                <span>Rp {{ number_format($sale->grand_total, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Metode Bayar:</span>
                <span class="uppercase font-bold">{{ $sale->payment_method }}</span>
            </div>
            @php
                $payment = $sale->payments->first();
            @endphp
            @if($payment)
                <div class="flex justify-between">
                    <span>Tunai / Diterima:</span>
                    <span>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Kembalian:</span>
                    <span>Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</span>
                </div>
            @endif
        </div>

        <!-- Footer Policy -->
        <div class="pt-3 text-center text-[9px] text-slate-500 whitespace-pre-line leading-normal">
            {{ \App\Models\Setting::get('receipt_footer', "Terima kasih telah berbelanja di Rose Colection!\nBarang yang sudah dibeli tidak dapat ditukar kecuali terdapat cacat produksi.") }}
        </div>
    </div>
</body>
</html>
