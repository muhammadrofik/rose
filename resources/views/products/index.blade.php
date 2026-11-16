<x-layouts.app>
    <x-slot:title>Data Barang — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Data Barang & Produk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola daftar produk, SKU, varian ukuran/warna, dan batas stok minimum.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('products.create') }}" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                    <x-icon name="plus" class="w-4 h-4" />
                    <span>Tambah Barang Baru</span>
                </a>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm">
            <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang, SKU, varian, atau barcode..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 text-xs transition-all outline-none">
                </div>

                <div class="flex flex-wrap gap-2">
                    <select name="category_id" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none focus:border-rose-500">
                        <option value="">Semua Kategori</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>

                    <select name="status" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none focus:border-rose-500">
                        <option value="">Semua Status</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Nonaktif / Arsip</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
                        Filter
                    </button>

                    @if(request()->anyFilled(['search', 'category_id', 'status']))
                        <a href="{{ route('products.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">SKU / Barcode</th>
                            <th class="py-3.5 px-6">Produk & Merek</th>
                            <th class="py-3.5 px-6">Kategori</th>
                            <th class="py-3.5 px-6">Varian</th>
                            <th class="py-3.5 px-6 text-right">Harga Jual</th>
                            @can('view-hpp')
                            <th class="py-3.5 px-6 text-right">HPP Berjalan</th>
                            @endcan
                            <th class="py-3.5 px-6 text-center">Total Stok</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($products as $prod)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6">
                                    <span class="font-mono font-bold text-slate-900 block">{{ $prod->sku }}</span>
                                    @if($prod->barcode)
                                        <span class="text-[10px] text-slate-400 font-mono">{{ $prod->barcode }}</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm">{{ $prod->name }}</div>
                                    <div class="text-[11px] text-slate-400">{{ $prod->brand ?? 'Rose Brand' }} • {{ $prod->unit }}</div>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                        {{ $prod->category->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @foreach($prod->variants->take(3) as $v)
                                            <span class="px-1.5 py-0.5 rounded bg-rose-50 text-rose-700 text-[10px] font-semibold">
                                                {{ $v->variant_name }} ({{ $v->stock }})
                                            </span>
                                        @endforeach
                                        @if($prod->variants->count() > 3)
                                            <span class="text-[10px] text-slate-400 font-semibold self-center">+{{ $prod->variants->count() - 3 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-right font-extrabold text-slate-900">
                                    Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                </td>
                                @can('view-hpp')
                                <td class="py-4 px-6 text-right font-bold text-slate-600">
                                    Rp {{ number_format($prod->current_hpp, 0, ',', '.') }}
                                </td>
                                @endcan
                                <td class="py-4 px-6 text-center">
                                    <span class="font-extrabold text-sm {{ $prod->total_stock == 0 ? 'text-rose-600' : ($prod->is_low_stock ? 'text-amber-600' : 'text-emerald-600') }}">
                                        {{ $prod->total_stock }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 block font-normal">Min: {{ $prod->min_stock }}</span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($prod->is_active)
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <a href="{{ route('catalog.show', $prod->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-slate-800 hover:bg-slate-100 transition-colors" title="Lihat">
                                            <x-icon name="eye" class="w-4 h-4" />
                                        </a>
                                        <a href="{{ route('products.edit', $prod->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Edit">
                                            <x-icon name="edit" class="w-4 h-4" />
                                        </a>
                                        <form method="POST" action="{{ route('products.destroy', $prod->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus atau mengarsipkan produk ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                                <x-icon name="trash" class="w-4 h-4" />
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-12 text-center text-slate-400">
                                    Belum ada data produk yang cocok dengan pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $products->links() }}
            </div>
        </div>
    </div>
</x-layouts.app>
