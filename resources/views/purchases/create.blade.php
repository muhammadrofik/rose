@php
    $variantsJson = $variants->map(function ($v) {
        return [
            'id' => (string) $v->id,
            'name' => $v->product->name . ' (' . $v->variant_name . ')',
            'product_name' => $v->product->name,
            'variant_name' => $v->variant_name,
            'sku' => $v->sku,
            'barcode' => $v->barcode ?: $v->product->barcode,
            'product_sku' => $v->product->sku,
            'current_hpp' => (float) ($v->current_hpp ?: 0),
            'stock' => $v->stock,
        ];
    });
@endphp

<x-layouts.app>
    <x-slot:title>Penerimaan Barang Masuk — Rose Colection</x-slot:title>

    <div class="max-w-5xl mx-auto space-y-6" x-data="purchaseForm()">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('purchases.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Riwayat PO</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Penerimaan Barang Masuk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Catat faktur pengadaan barang dari supplier. Sistem akan memperbarui stok fisik dan HPP Moving Weighted Average secara otomatis.</p>
            </div>
        </div>

        <!-- Formula Information Alert -->
        <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 text-xs flex items-start gap-3">
            <x-icon name="profit" class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" />
            <div>
                <h4 class="font-bold">Metode Moving Weighted Average Cost (Rata-Rata Tertimbang Bergerak)</h4>
                <p class="mt-0.5 text-rose-800 leading-relaxed">
                    HPP baru dihitung otomatis: <code class="bg-rose-100 px-1 py-0.5 rounded font-mono font-bold">((Stok Lama × HPP Lama) + (Qty Masuk × Biaya Bersih Baru)) / (Stok Lama + Qty Masuk)</code>. Biaya tambahan ongkos kirim akan dialokasikan secara proporsional.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('purchases.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Header Pembelian -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Informasi Faktur Supplier</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Supplier *</label>
                        <select name="supplier_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Penerimaan *</label>
                        <input type="date" name="purchase_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Biaya Tambahan / Ongkir (Rp)</label>
                        <input type="number" name="additional_cost" x-model.number="additionalCost" min="0" step="1000" placeholder="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:border-rose-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Penerimaan / No. Surat Jalan Supplier</label>
                        <input type="text" name="notes" placeholder="No. Resi pengiriman, kondisi barang, dll..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Diskon Global Faktur (Rp)</label>
                        <input type="number" name="discount_amount" x-model.number="discountAmount" min="0" step="1000" placeholder="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold focus:border-rose-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Section 2: Daftar Barang Diterima -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Rincian Barang & Varian Masuk</h2>
                        <p class="text-xs text-slate-400">Pindai barcode pakaian atau pilih dari daftar untuk menambahkan stok.</p>
                    </div>
                    <button type="button" @click="addItem()" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center gap-1 transition-colors self-start sm:self-auto">
                        <x-icon name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Baris Manual</span>
                    </button>
                </div>

                <!-- SCAN BARCODE BOX (Scanner Hardware Integration) -->
                <div class="p-4 bg-gradient-to-r from-rose-50 to-pink-50/50 rounded-2xl border border-rose-200 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-600 text-white flex items-center justify-center shadow-sm shadow-rose-200 shrink-0">
                            <x-icon name="barcode" class="w-5 h-5" />
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 flex items-center gap-2">
                                <span>Scan Barcode Barang untuk Tambah Stok</span>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                    Siap Scan
                                </span>
                            </h4>
                            <p class="text-[11px] text-slate-500">Tembakkan scanner barcode ke label pakaian, produk akan otomatis muncul di tabel bawah.</p>
                        </div>
                    </div>

                    <div class="relative w-full sm:w-80">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-rose-500">
                            <x-icon name="search" class="w-4 h-4" />
                        </span>
                        <input type="text" x-model="barcodeScanInput" @keydown.enter.prevent="handleBarcodeScan()"
                            placeholder="Arahkan scanner ke barcode..."
                            class="w-full pl-9 pr-3 py-2.5 rounded-xl border border-rose-300 focus:border-rose-600 focus:ring-4 focus:ring-rose-500/10 text-xs font-mono font-bold bg-white outline-none placeholder:font-sans placeholder:font-normal placeholder:text-rose-400">
                    </div>
                </div>

                <!-- Scan Feedback Toast -->
                <div x-show="scanAlert" x-transition style="display: none;"
                    :class="scanAlertType === 'success' ? 'bg-slate-900 text-white' : 'bg-rose-600 text-white'"
                    class="p-3 rounded-2xl text-xs font-bold flex items-center justify-between shadow-lg">
                    <div class="flex items-center gap-2">
                        <x-icon name="check" class="w-4 h-4 text-emerald-400" />
                        <span x-text="scanAlert"></span>
                    </div>
                    <button type="button" @click="scanAlert = ''" class="text-slate-400 hover:text-white">
                        <x-icon name="x" class="w-3.5 h-3.5" />
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Produk & Varian *</th>
                                <th class="py-2.5 px-3 text-center">Qty Masuk *</th>
                                <th class="py-2.5 px-3">Harga Beli Satuan (Rp) *</th>
                                <th class="py-2.5 px-3">Diskon Item (Rp)</th>
                                <th class="py-2.5 px-3 text-right">Subtotal</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="p-2.5 w-72">
                                        <select :name="`items[${index}][product_variant_id]`" x-model="item.product_variant_id" @change="onVariantChange(item)" required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                            <option value="">-- Pilih Barang / Varian --</option>
                                            @foreach($variants as $var)
                                                <option value="{{ $var->id }}">{{ $var->product->name }} ({{ $var->variant_name }}) - Stok: {{ $var->stock }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-2.5 w-24">
                                        <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-bold focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5 w-36">
                                        <input type="number" :name="`items[${index}][purchase_price]`" x-model.number="item.purchase_price" min="0" step="500" required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5 w-28">
                                        <input type="number" :name="`items[${index}][discount_amount]`" x-model.number="item.discount_amount" min="0" step="500" placeholder="0"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5 text-right font-bold text-slate-900 font-mono">
                                        Rp <span x-text="lineTotal(item).toLocaleString('id-ID')"></span>
                                    </td>
                                    <td class="p-2.5 text-center">
                                        <button type="button" @click="removeItem(index)" :disabled="items.length === 1"
                                            class="p-1 rounded text-slate-400 hover:text-rose-600 disabled:opacity-30">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Financial calculation box -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-1.5 text-xs max-w-sm ml-auto">
                    <div class="flex justify-between text-slate-600">
                        <span>Total Nilai Barang:</span>
                        <span class="font-bold font-mono">Rp <span x-text="itemsTotal().toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-slate-600">
                        <span>Biaya Tambahan (Ongkir):</span>
                        <span class="font-bold font-mono">+ Rp <span x-text="(additionalCost || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between text-rose-600">
                        <span>Diskon Faktur:</span>
                        <span class="font-bold font-mono">- Rp <span x-text="(discountAmount || 0).toLocaleString('id-ID')"></span></span>
                    </div>
                    <div class="flex justify-between font-black text-sm text-slate-900 pt-1 border-t border-slate-200">
                        <span>Grand Total Pengadaan:</span>
                        <span class="text-rose-600 font-mono">Rp <span x-text="grandTotal().toLocaleString('id-ID')"></span></span>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('purchases.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all">
                    Konfirmasi Penerimaan Barang & Perbarui HPP
                </button>
            </div>
        </form>
    </div>

    <script>
        function purchaseForm() {
            return {
                additionalCost: 0,
                discountAmount: 0,
                barcodeScanInput: '',
                scanAlert: '',
                scanAlertType: 'success',
                availableVariants: @json($variantsJson),
                items: [
                    { product_variant_id: '', quantity: 1, purchase_price: 0, discount_amount: 0 }
                ],
                addItem() {
                    this.items.push({
                        product_variant_id: '',
                        quantity: 1,
                        purchase_price: 0,
                        discount_amount: 0
                    });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                },
                onVariantChange(item) {
                    if (!item.product_variant_id) return;
                    const v = this.availableVariants.find(x => String(x.id) === String(item.product_variant_id));
                    if (v && (!item.purchase_price || item.purchase_price == 0)) {
                        item.purchase_price = v.current_hpp || 0;
                    }
                },
                handleBarcodeScan() {
                    const raw = (this.barcodeScanInput || '').trim();
                    if (!raw) return;

                    const query = raw.toLowerCase();
                    const found = this.availableVariants.find(v => 
                        (v.barcode && v.barcode.toLowerCase() === query) ||
                        (v.sku && v.sku.toLowerCase() === query) ||
                        (v.product_sku && v.product_sku.toLowerCase() === query)
                    );

                    if (!found) {
                        this.scanAlertType = 'error';
                        this.scanAlert = `Barcode / SKU "${raw}" tidak ditemukan di database produk!`;
                        this.barcodeScanInput = '';
                        setTimeout(() => { if (this.scanAlert.includes(raw)) this.scanAlert = ''; }, 4000);
                        return;
                    }

                    // Check if item already exists in items list
                    const existing = this.items.find(i => String(i.product_variant_id) === String(found.id));
                    if (existing) {
                        existing.quantity = (parseInt(existing.quantity) || 0) + 1;
                        this.scanAlertType = 'success';
                        this.scanAlert = `Berhasil menambah kuantitas (+1) untuk "${found.name}". Total masuk: ${existing.quantity}`;
                    } else {
                        // If single row is empty, fill it
                        if (this.items.length === 1 && !this.items[0].product_variant_id) {
                            this.items[0].product_variant_id = String(found.id);
                            this.items[0].quantity = 1;
                            this.items[0].purchase_price = found.current_hpp || 0;
                            this.items[0].discount_amount = 0;
                        } else {
                            this.items.push({
                                product_variant_id: String(found.id),
                                quantity: 1,
                                purchase_price: found.current_hpp || 0,
                                discount_amount: 0
                            });
                        }
                        this.scanAlertType = 'success';
                        this.scanAlert = `Berhasil menambahkan: "${found.name}" (HPP Estimasi: Rp ${(found.current_hpp || 0).toLocaleString('id-ID')})`;
                    }

                    this.barcodeScanInput = '';
                    setTimeout(() => { this.scanAlert = ''; }, 5000);
                },
                lineTotal(item) {
                    const gross = (item.quantity || 0) * (item.purchase_price || 0);
                    return Math.max(0, gross - (item.discount_amount || 0));
                },
                itemsTotal() {
                    return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
                },
                grandTotal() {
                    return Math.max(0, this.itemsTotal() + (this.additionalCost || 0) - (this.discountAmount || 0));
                }
            }
        }
    </script>
</x-layouts.app>
