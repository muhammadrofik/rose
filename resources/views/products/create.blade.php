<x-layouts.app>
    <x-slot:title>Tambah Barang Baru — Rose Colection</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6" x-data="productForm()">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('products.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Data Barang</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Tambah Barang & Varian Baru</h1>
            </div>
        </div>

        <form method="POST" action="{{ route('products.store') }}" class="space-y-6">
            @csrf

            <!-- Section 1: Informasi Utama Produk -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Informasi Utama Produk</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Produk *</label>
                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Contoh: Floral Silk Maxi Dress"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kode / SKU Induk *</label>
                        <input type="text" name="sku" value="{{ old('sku') }}" required placeholder="Contoh: DRS-004"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono uppercase focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Barcode (Opsional)</label>
                        <input type="text" name="barcode" value="{{ old('barcode') }}" placeholder="899..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-mono focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kategori *</label>
                        <select name="category_id" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="">Pilih Kategori</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Supplier (Opsional)</label>
                        <select name="supplier_id" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="">Pilih Supplier</option>
                            @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Merek / Brand</label>
                        <input type="text" name="brand" value="{{ old('brand', 'Rose Signature') }}" placeholder="Rose Signature"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Satuan</label>
                        <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" placeholder="pcs, set, lembar"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Harga Jual Default (Rp) *</label>
                        <input type="number" name="selling_price" value="{{ old('selling_price') }}" required min="0" step="500" placeholder="250000"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs font-bold text-slate-900 focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok (Peringatan)</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', 5) }}" required min="0"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi Produk</label>
                        <textarea name="description" rows="3" placeholder="Informasi bahan, karakteristik, dan keunggulan produk..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">{{ old('description') }}</textarea>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-slate-700">Produk Aktif (Dapat Dijual di Kasir POS)</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 2: Varian Produk (Ukuran, Warna, SKU) -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Varian Produk</h2>
                        <p class="text-xs text-slate-400">Setiap varian memiliki SKU unik tersendiri. Stok awal akan dicatat melalui penerimaan barang masuk.</p>
                    </div>
                    <button type="button" @click="addVariant()" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold flex items-center gap-1 transition-colors">
                        <x-icon name="plus" class="w-3.5 h-3.5" />
                        <span>Tambah Varian</span>
                    </button>
                </div>

                <div class="border border-slate-200 rounded-2xl overflow-hidden">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">Nama Varian *</th>
                                <th class="py-2.5 px-3">SKU Varian *</th>
                                <th class="py-2.5 px-3">Ukuran</th>
                                <th class="py-2.5 px-3">Warna</th>
                                <th class="py-2.5 px-3">Harga Jual (Rp)</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(v, index) in variants" :key="index">
                                <tr>
                                    <td class="p-2.5">
                                        <input type="text" :name="`variants[${index}][variant_name]`" x-model="v.variant_name" required placeholder="Standard / Pink - M"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5">
                                        <input type="text" :name="`variants[${index}][sku]`" x-model="v.sku" required placeholder="SKU-VAR-1"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono uppercase focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5">
                                        <input type="text" :name="`variants[${index}][size]`" x-model="v.size" placeholder="S, M, All Size"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5">
                                        <input type="text" :name="`variants[${index}][color]`" x-model="v.color" placeholder="Hitam, Pink"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5">
                                        <input type="number" :name="`variants[${index}][selling_price]`" x-model="v.selling_price" placeholder="Sesuai Induk"
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-bold focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5 text-center">
                                        <button type="button" @click="removeVariant(index)" :disabled="variants.length === 1"
                                            class="p-1 rounded text-slate-400 hover:text-rose-600 disabled:opacity-30">
                                            <x-icon name="trash" class="w-4 h-4" />
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('products.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all">
                    Simpan Produk & Varian
                </button>
            </div>
        </form>
    </div>

    <script>
        function productForm() {
            return {
                variants: [
                    { variant_name: 'Standard', sku: '', size: '', color: '', selling_price: '' }
                ],
                addVariant() {
                    this.variants.push({
                        variant_name: '',
                        sku: '',
                        size: '',
                        color: '',
                        selling_price: ''
                    });
                },
                removeVariant(index) {
                    if (this.variants.length > 1) {
                        this.variants.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-layouts.app>
