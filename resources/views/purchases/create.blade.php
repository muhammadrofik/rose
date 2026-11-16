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
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Rincian Barang & Varian Masuk</h2>
                        <p class="text-xs text-slate-400">Pilih varian produk, kuantitas yang diterima, dan harga beli per unit.</p>
                    </div>
                    <button type="button" @click="addItem()" class="px-3.5 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold flex items-center gap-1 transition-colors">
                        <x-icon name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Baris</span>
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
                                        <select :name="`items[${index}][product_variant_id]`" x-model="item.product_variant_id" required
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
                items: [
                    { product_variant_id: '', quantity: 10, purchase_price: 100000, discount_amount: 0 }
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
