<div class="h-[calc(100vh-5rem)] flex flex-col lg:flex-row overflow-hidden -m-8">
    <!-- LEFT PANEL: PRODUCT SELECTION & CATEGORY PILLS (Image 2 Style) -->
    <div class="flex-1 flex flex-col min-w-0 bg-[#faf7f5] border-r border-slate-200/80 overflow-hidden">
        <!-- Top Bar: Search & Barcode -->
        <div class="p-6 pb-4 bg-white border-b border-slate-100 flex items-center justify-between gap-4 shrink-0">
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 transition-colors" title="Kembali ke Dashboard">
                    <x-icon name="chevron-left" class="w-5 h-5" />
                </a>
                <div>
                    <h1 class="text-lg font-extrabold text-slate-900 tracking-tight">Kasir POS — Pilih Produk</h1>
                    <p class="text-xs text-slate-400">Pilih pakaian atau pindai barcode untuk menambahkan ke struk.</p>
                </div>
            </div>

            <!-- Search input -->
            <div class="relative w-72">
                <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input type="text" wire:model.live.debounce.300ms="search" placeholder="Cari nama, SKU, barcode..."
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 text-xs transition-all outline-none bg-slate-50/50">
            </div>
        </div>

        <!-- Horizontal Category Pills Filter -->
        <div class="px-6 py-3 bg-white border-b border-slate-100 flex items-center gap-2 overflow-x-auto shrink-0">
            <button type="button" wire:click="selectCategory(null)"
                class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ is_null($selectedCategory) ? 'bg-rose-500 text-white shadow-sm shadow-rose-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                Semua Produk
            </button>
            @foreach($categories as $cat)
                <button type="button" wire:click="selectCategory({{ $cat->id }})"
                    class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all flex items-center gap-1.5 {{ $selectedCategory === $cat->id ? 'bg-rose-500 text-white shadow-sm shadow-rose-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    <span>{{ $cat->name }}</span>
                    <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ $selectedCategory === $cat->id ? 'bg-rose-700 text-white' : 'bg-slate-200 text-slate-600' }}">
                        {{ $cat->products_count }}
                    </span>
                </button>
            @endforeach
        </div>

        <!-- Error Message Alert -->
        @if($errorMessage)
            <div class="mx-6 mt-3 p-3 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs font-bold flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <x-icon name="alert" class="w-4 h-4 text-rose-600" />
                    <span>{{ $errorMessage }}</span>
                </div>
                <button wire:click="$set('errorMessage', '')" class="text-rose-400 hover:text-rose-600"><x-icon name="x" class="w-4 h-4" /></button>
            </div>
        @endif

        <!-- Products Grid Container -->
        <div class="flex-1 overflow-y-auto p-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                @forelse($products as $prod)
                    @php
                        $totalStock = $prod->total_stock;
                        $hasVariants = $prod->variants->count() > 1;
                        $sizes = $prod->variants->pluck('size')->filter()->unique();
                    @endphp
                    <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-rose-300 transition-all flex flex-col justify-between overflow-hidden group">
                        <!-- Garment Card Image -->
                        <div class="h-36 bg-gradient-to-b from-rose-50/40 to-slate-50 flex items-center justify-center relative p-3">
                            <div class="w-16 h-16 rounded-xl bg-white shadow-xs border border-rose-100 flex items-center justify-center text-rose-500 group-hover:scale-105 transition-transform">
                                <x-icon name="catalog" class="w-8 h-8" />
                            </div>

                            <!-- Stock Badge -->
                            <div class="absolute top-2.5 right-2.5">
                                @if($totalStock == 0)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-rose-500 text-white">Habis</span>
                                @elseif($totalStock <= $prod->min_stock)
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500 text-white">Sisa {{ $totalStock }}</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-emerald-500 text-white">{{ $totalStock }} pcs</span>
                                @endif
                            </div>
                        </div>

                        <!-- Info & Action -->
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 uppercase font-bold tracking-wider block truncate">
                                    {{ $prod->category->name ?? 'Kategori' }}
                                </span>
                                <h3 class="font-bold text-slate-900 text-xs mt-0.5 line-clamp-1" title="{{ $prod->name }}">
                                    {{ $prod->name }}
                                </h3>

                                <!-- Sizes preview -->
                                @if($sizes->isNotEmpty())
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach($sizes->take(3) as $s)
                                            <span class="px-1 py-0.5 rounded text-[9px] font-bold bg-slate-100 text-slate-600">{{ $s }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <div class="mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                                <div>
                                    <span class="text-xs font-black text-slate-900 block">
                                        Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                    </span>
                                </div>

                                <button type="button" wire:click="addProductToCart({{ $prod->id }})"
                                    class="w-8 h-8 rounded-xl bg-rose-600 hover:bg-rose-700 text-white flex items-center justify-center shadow-sm shadow-rose-200 transition-all hover:scale-105"
                                    title="Tambah ke Keranjang">
                                    <x-icon name="plus" class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-slate-400">
                        <x-icon name="products" class="w-10 h-10 mx-auto mb-2 text-slate-300" />
                        <p class="font-bold text-slate-600 text-xs">Tidak ada produk yang sesuai</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL: CART DETAILS & CHECKOUT (Image 2 Style) -->
    <div class="w-full lg:w-[420px] bg-white flex flex-col justify-between shrink-0 border-l border-slate-200/80 shadow-lg z-10">
        <!-- Cart Header -->
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h2 class="text-base font-extrabold text-slate-900">Keranjang Belanja</h2>
                <p class="text-xs text-slate-400">{{ count($cart) }} item terpilih</p>
            </div>

            @if(!empty($cart))
                <button type="button" wire:click="clearCart" class="text-xs font-bold text-rose-600 hover:text-rose-700">
                    Hapus Semua
                </button>
            @endif
        </div>

        <!-- Cart Items Scrollable List -->
        <div class="flex-1 overflow-y-auto p-5 divide-y divide-slate-100">
            @forelse($cart as $variantId => $item)
                <div class="py-3 flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-rose-50/50 flex items-center justify-center text-rose-500 shrink-0">
                        <x-icon name="catalog" class="w-6 h-6" />
                    </div>

                    <div class="flex-1 min-w-0">
                        <h4 class="text-xs font-bold text-slate-900 truncate">{{ $item['name'] }}</h4>
                        <div class="flex items-center gap-2 text-[11px] text-slate-500 mt-0.5">
                            <span class="px-1.5 py-0.2 rounded bg-slate-100 font-medium">{{ $item['variant_name'] }}</span>
                            <span>•</span>
                            <span class="font-bold text-slate-800">Rp {{ number_format($item['price'], 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <!-- Quantity Stepper -->
                    <div class="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl shrink-0">
                        <button type="button" wire:click="updateQuantity({{ $variantId }}, -1)" class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-slate-600 hover:text-rose-600 shadow-xs">
                            <x-icon name="minus" class="w-3 h-3" />
                        </button>
                        <span class="w-6 text-center text-xs font-extrabold text-slate-900">{{ $item['quantity'] }}</span>
                        <button type="button" wire:click="updateQuantity({{ $variantId }}, 1)" class="w-6 h-6 rounded-lg bg-white flex items-center justify-center text-slate-600 hover:text-rose-600 shadow-xs">
                            <x-icon name="plus" class="w-3 h-3" />
                        </button>
                    </div>

                    <!-- Delete button -->
                    <button type="button" wire:click="removeFromCart({{ $variantId }})" class="text-slate-300 hover:text-rose-600 p-1">
                        <x-icon name="trash" class="w-4 h-4" />
                    </button>
                </div>
            @empty
                <div class="h-48 flex flex-col items-center justify-center text-center text-slate-400">
                    <x-icon name="cart" class="w-10 h-10 text-slate-200 mb-2" />
                    <p class="font-bold text-xs text-slate-600">Keranjang Masih Kosong</p>
                    <p class="text-[11px] text-slate-400">Klik tombol (+) pada kartu produk di samping untuk menambahkan.</p>
                </div>
            @endforelse
        </div>

        <!-- Checkout & Payment Panel -->
        <div class="p-5 bg-slate-50/70 border-t border-slate-200/80 space-y-3 shrink-0">
            <!-- Customer Name & Payment Method -->
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Pelanggan</label>
                    <input type="text" wire:model.defer="customerName" placeholder="Umum"
                        class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white outline-none focus:border-rose-500">
                </div>

                <div>
                    <label class="block text-[11px] font-bold text-slate-600 mb-1">Metode Bayar</label>
                    <select wire:model.live="paymentMethod" class="w-full px-3 py-1.5 rounded-xl border border-slate-200 text-xs bg-white font-bold text-slate-800 outline-none focus:border-rose-500">
                        <option value="cash">Tunai (Cash)</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS Manual</option>
                    </select>
                </div>
            </div>

            <!-- Discount Input -->
            <div class="flex items-center justify-between text-xs">
                <span class="font-semibold text-slate-600">Diskon Transaksi (Rp):</span>
                <input type="number" wire:model.live.debounce.300ms="discountAmount" min="0" step="1000" placeholder="0"
                    class="w-32 px-3 py-1 text-right rounded-lg border border-slate-200 text-xs font-bold outline-none focus:border-rose-500 bg-white">
            </div>

            <!-- Totals Breakdown -->
            <div class="pt-2 border-t border-slate-200 space-y-1 text-xs">
                <div class="flex justify-between text-slate-500">
                    <span>Total Produk:</span>
                    <span class="font-bold text-slate-800">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                </div>
                @if($discountAmount > 0)
                    <div class="flex justify-between text-rose-600 font-semibold">
                        <span>Diskon:</span>
                        <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="flex justify-between text-base font-black text-slate-900 pt-1">
                    <span>Total Tagihan:</span>
                    <span class="text-rose-600">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Cash Tendered & Change -->
            @if($paymentMethod === 'cash')
                <div class="pt-2 border-t border-slate-200 space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">Jumlah Diterima (Rp):</span>
                        <input type="number" wire:model.live="amountPaid" min="0" step="1000"
                            class="w-36 px-3 py-1 text-right rounded-lg border border-slate-200 text-xs font-extrabold text-slate-900 outline-none focus:border-rose-500 bg-white">
                    </div>

                    <!-- Quick Cash Buttons -->
                    <div class="flex items-center gap-1 overflow-x-auto text-[10px]">
                        <button type="button" wire:click="setExactPayment" class="px-2 py-1 rounded-lg bg-white border border-slate-200 font-bold hover:bg-slate-100">Uang Pas</button>
                        <button type="button" wire:click="setCashTendered(50000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 font-semibold hover:bg-slate-100">50K</button>
                        <button type="button" wire:click="setCashTendered(100000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 font-semibold hover:bg-slate-100">100K</button>
                        <button type="button" wire:click="setCashTendered(200000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 font-semibold hover:bg-slate-100">200K</button>
                        <button type="button" wire:click="setCashTendered(500000)" class="px-2 py-1 rounded-lg bg-white border border-slate-200 font-semibold hover:bg-slate-100">500K</button>
                    </div>

                    <div class="flex justify-between text-xs font-bold text-slate-700 pt-1">
                        <span>Kembalian:</span>
                        <span class="{{ $this->changeAmount >= 0 ? 'text-emerald-600' : 'text-rose-600' }} font-black text-sm">
                            Rp {{ number_format($this->changeAmount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>
            @endif

            <!-- Process Payment CTA Button -->
            <button type="button" wire:click="checkout"
                wire:loading.attr="disabled"
                @if(empty($cart) || $amountPaid < $this->grandTotal) disabled @endif
                class="w-full py-3.5 px-4 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 disabled:opacity-40 text-white rounded-xl text-sm font-bold shadow-md shadow-rose-200 transition-all flex items-center justify-center gap-2">
                <x-icon name="check" class="w-4 h-4" />
                <span wire:loading.remove>Proses Pembayaran</span>
                <span wire:loading>Memproses Transaksi...</span>
            </button>
        </div>
    </div>

    <!-- MODAL: SELECT VARIANT (when product has multiple sizes/colors) -->
    @if($variantModalOpen && $activeProduct)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-extrabold text-slate-900">{{ $activeProduct->name }}</h3>
                        <p class="text-xs text-slate-400">Pilih varian ukuran dan warna yang dipesan</p>
                    </div>
                    <button type="button" wire:click="closeVariantModal" class="text-slate-400 hover:text-slate-600">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-2 max-h-72 overflow-y-auto">
                    @foreach($activeProduct->variants as $var)
                        <button type="button" wire:click="addVariantToCart({{ $var->id }})"
                            @if($var->stock <= 0) disabled @endif
                            class="w-full p-3 rounded-2xl border text-left flex items-center justify-between transition-all {{ $var->stock > 0 ? 'border-slate-200 hover:border-rose-400 hover:bg-rose-50/50' : 'border-slate-100 bg-slate-50 opacity-40 cursor-not-allowed' }}">
                            <div>
                                <div class="font-bold text-xs text-slate-900">{{ $var->variant_name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">SKU: {{ $var->sku }}</div>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-xs text-slate-900">Rp {{ number_format($var->selling_price, 0, ',', '.') }}</div>
                                <span class="text-[10px] font-bold {{ $var->stock > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                    Stok: {{ $var->stock }} pcs
                                </span>
                            </div>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL: THERMAL RECEIPT & PRINT (58mm / 80mm) -->
    @if($receiptModalOpen && $lastSale)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/50 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4 max-h-[90vh] flex flex-col justify-between">
                <!-- Modal Header -->
                <div class="flex items-center justify-between no-print border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center">
                            <x-icon name="check" class="w-4 h-4" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Pembayaran Berhasil!</h3>
                            <p class="text-[11px] text-slate-400">Invoice: {{ $lastSale->invoice_number }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <select wire:model.live="receiptPaperSize" class="text-[10px] font-bold px-2 py-1 rounded border border-slate-200">
                            <option value="58mm">58 mm</option>
                            <option value="80mm">80 mm</option>
                        </select>
                        <button type="button" wire:click="closeReceiptModal" class="text-slate-400 hover:text-slate-600 p-1">
                            <x-icon name="x" class="w-4 h-4" />
                        </button>
                    </div>
                </div>

                <!-- Printable Thermal Receipt Area -->
                <div id="printable-receipt" class="p-4 bg-slate-50 border border-slate-200 rounded-2xl font-mono text-xs overflow-y-auto leading-tight text-slate-900 {{ $receiptPaperSize === '58mm' ? 'max-w-[280px] mx-auto text-[11px]' : 'max-w-sm mx-auto text-xs' }}">
                    <!-- Store Header -->
                    <div class="text-center space-y-0.5 border-b border-dashed border-slate-300 pb-3">
                        <h2 class="font-extrabold text-sm uppercase tracking-wider">{{ \App\Models\Setting::get('store_name', 'Rose Colection') }}</h2>
                        <p class="text-[10px] text-slate-500">{{ \App\Models\Setting::get('store_address', 'Panakkukang, Makassar') }}</p>
                        <p class="text-[10px] text-slate-500">Telp: {{ \App\Models\Setting::get('store_phone', '0812-3456-7890') }}</p>
                    </div>

                    <!-- Transaction Metadata -->
                    <div class="py-2.5 border-b border-dashed border-slate-300 space-y-0.5 text-[10px]">
                        <div class="flex justify-between">
                            <span>No:</span>
                            <span class="font-bold">{{ $lastSale->invoice_number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Tgl:</span>
                            <span>{{ $lastSale->created_at->format('d/m/Y H:i') }} WITA</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Kasir:</span>
                            <span>{{ $lastSale->user->name ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Pelanggan:</span>
                            <span>{{ $lastSale->customer_name ?? 'Umum' }}</span>
                        </div>
                    </div>

                    <!-- Items List -->
                    <div class="py-2.5 border-b border-dashed border-slate-300 space-y-2">
                        @foreach($lastSale->items as $item)
                            <div>
                                <div class="font-bold truncate">{{ $item->product->name ?? '' }}</div>
                                <div class="flex justify-between text-[10px] text-slate-600">
                                    <span>{{ $item->quantity }} x {{ number_format($item->unit_price, 0, ',', '.') }}</span>
                                    <span class="font-bold">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Financial Summary -->
                    <div class="py-2.5 border-b border-dashed border-slate-300 space-y-1 text-[10px]">
                        <div class="flex justify-between">
                            <span>Subtotal:</span>
                            <span>Rp {{ number_format($lastSale->total_amount, 0, ',', '.') }}</span>
                        </div>
                        @if($lastSale->discount_amount > 0)
                            <div class="flex justify-between">
                                <span>Diskon:</span>
                                <span>- Rp {{ number_format($lastSale->discount_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                        <div class="flex justify-between font-extrabold text-xs pt-1 border-t border-slate-200">
                            <span>TOTAL:</span>
                            <span>Rp {{ number_format($lastSale->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Metode:</span>
                            <span class="uppercase font-bold">{{ $lastSale->payment_method }}</span>
                        </div>
                        @php
                            $payment = $lastSale->payments->first();
                        @endphp
                        @if($payment)
                            <div class="flex justify-between">
                                <span>Bayar:</span>
                                <span>Rp {{ number_format($payment->amount_paid, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Kembali:</span>
                                <span>Rp {{ number_format($payment->change_amount, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Footer Note -->
                    <div class="pt-3 text-center text-[9px] text-slate-500 whitespace-pre-line">
                        {{ \App\Models\Setting::get('receipt_footer', "Terima kasih telah berbelanja!\nBarang yang sudah dibeli tidak dapat ditukar.") }}
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center gap-2 pt-2 no-print">
                    <button type="button" onclick="window.print()" class="flex-1 py-3 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold flex items-center justify-center gap-2 transition-all">
                        <x-icon name="printer" class="w-4 h-4" />
                        <span>Cetak Struk</span>
                    </button>
                    <button type="button" wire:click="closeReceiptModal" class="py-3 px-4 bg-rose-50 hover:bg-rose-100 text-rose-700 rounded-xl text-xs font-bold transition-all">
                        Transaksi Baru
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
