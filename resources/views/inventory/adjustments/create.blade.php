<x-layouts.app>
    <x-slot:title>Penyesuaian Stok Baru — Rose Colection</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6" x-data="adjustmentForm()">
        <div class="flex items-center justify-between">
            <div>
                <a href="{{ route('inventory.adjustments.index') }}" class="text-xs font-semibold text-slate-500 hover:text-rose-600 flex items-center gap-1 mb-1">
                    <x-icon name="chevron-left" class="w-4 h-4" />
                    <span>Kembali ke Riwayat Penyesuaian</span>
                </a>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Form Penyesuaian Stok (Stock Opname)</h1>
                <p class="text-xs text-slate-500 mt-0.5">Selisih antara stok sistem dan stok fisik akan dicatat secara permanen di buku ledger pergerakan stok.</p>
            </div>
        </div>

        <form method="POST" action="{{ route('inventory.adjustments.store') }}" class="space-y-6">
            @csrf

            <!-- Header -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Informasi Penyesuaian</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Penyesuaian *</label>
                        <input type="date" name="adjustment_date" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Alasan Penyesuaian *</label>
                        <select name="type" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="correction">Koreksi Stok Opname Fisik</option>
                            <option value="damage">Barang Rusak / Cacat Pabrik</option>
                            <option value="lost">Barang Hilang / Selisih Gudang</option>
                            <option value="internal_use">Penggunaan Internal / Seragam Toko</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Umum</label>
                        <input type="text" name="notes" placeholder="Keterangan stock opname bulanan..."
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Rincian Barang Disesuaikan</h2>
                        <p class="text-xs text-slate-400">Masukkan hasil hitung fisik. Selisih dihitung otomatis.</p>
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
                                <th class="py-2.5 px-3 text-center">Stok Sistem</th>
                                <th class="py-2.5 px-3 text-center">Stok Fisik Real *</th>
                                <th class="py-2.5 px-3 text-center">Selisih</th>
                                <th class="py-2.5 px-3">Alasan Item</th>
                                <th class="py-2.5 px-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(item, index) in items" :key="index">
                                <tr>
                                    <td class="p-2.5 w-64">
                                        <select :name="`items[${index}][product_variant_id]`" x-model="item.product_variant_id" @change="onVariantChange(item)" required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach($variants as $var)
                                                <option value="{{ $var->id }}" data-stock="{{ $var->stock }}">{{ $var->product->name }} ({{ $var->variant_name }}) - Sistem: {{ $var->stock }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td class="p-2.5 text-center font-bold text-slate-500 w-24">
                                        <span x-text="item.system_stock" class="px-2 py-1 bg-slate-100 rounded font-mono"></span>
                                    </td>
                                    <td class="p-2.5 w-28">
                                        <input type="number" :name="`items[${index}][physical_stock]`" x-model.number="item.physical_stock" min="0" required
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs text-center font-bold focus:border-rose-500 outline-none">
                                    </td>
                                    <td class="p-2.5 text-center font-mono font-black text-xs w-24">
                                        <span :class="(item.physical_stock - item.system_stock) < 0 ? 'text-rose-600' : ((item.physical_stock - item.system_stock) > 0 ? 'text-emerald-600' : 'text-slate-400')"
                                            x-text="(item.physical_stock - item.system_stock) > 0 ? '+' + (item.physical_stock - item.system_stock) : (item.physical_stock - item.system_stock)">
                                        </span>
                                    </td>
                                    <td class="p-2.5">
                                        <input type="text" :name="`items[${index}][reason]`" x-model="item.reason" placeholder="Keterangan..."
                                            class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs focus:border-rose-500 outline-none">
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
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('inventory.adjustments.index') }}" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-all">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all">
                    Simpan Penyesuaian Stok
                </button>
            </div>
        </form>
    </div>

    <script>
        function adjustmentForm() {
            return {
                items: [
                    { product_variant_id: '', system_stock: 0, physical_stock: 0, reason: '' }
                ],
                onVariantChange(item) {
                    const sel = event.target;
                    const opt = sel.options[sel.selectedIndex];
                    const st = parseInt(opt.getAttribute('data-stock') || 0);
                    item.system_stock = st;
                    item.physical_stock = st;
                },
                addItem() {
                    this.items.push({
                        product_variant_id: '',
                        system_stock: 0,
                        physical_stock: 0,
                        reason: ''
                    });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-layouts.app>
