<x-layouts.app>
    <x-slot:title>Katalog Produk — Rose Colection</x-slot:title>

    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Katalog Produk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Jelajahi koleksi busana, dress, hijab, dan aksesoris Rose Colection.</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('pos.index') }}" class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                    <x-icon name="cart" class="w-4 h-4" />
                    <span>Buka POS Kasir</span>
                </a>
                @can('manage-products')
                <a href="{{ route('products.create') }}" class="px-3.5 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition-all shadow-sm">
                    <x-icon name="plus" class="w-4 h-4 text-slate-500" />
                    <span>Tambah Produk</span>
                </a>
                @endcan
            </div>
        </div>

        <!-- SEARCH & CATEGORY PILLS FILTER (Image 2 POS / Catalog Style) -->
        <div class="bg-white p-4 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
            <form method="GET" action="{{ route('catalog.index') }}" class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <x-icon name="search" class="w-4 h-4" />
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari berdasarkan nama produk, SKU, barcode..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 text-xs transition-all outline-none">
                </div>

                <div class="flex gap-2">
                    <select name="availability" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none focus:border-rose-500">
                        <option value="">Semua Ketersediaan</option>
                        <option value="in_stock" {{ request('availability') === 'in_stock' ? 'selected' : '' }}>Stok Tersedia</option>
                        <option value="low_stock" {{ request('availability') === 'low_stock' ? 'selected' : '' }}>Stok Menipis</option>
                        <option value="out_of_stock" {{ request('availability') === 'out_of_stock' ? 'selected' : '' }}>Stok Habis</option>
                    </select>

                    <select name="sort" class="px-3 py-2.5 rounded-xl border border-slate-200 text-xs font-medium text-slate-700 outline-none focus:border-rose-500">
                        <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Terbaru</option>
                        <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Harga: Rendah ke Tinggi</option>
                        <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Harga: Tinggi ke Rendah</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Nama A - Z</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-all">
                        Terapkan
                    </button>

                    @if(request()->anyFilled(['search', 'category', 'availability', 'sort']))
                        <a href="{{ route('catalog.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold flex items-center transition-all">
                            Reset
                        </a>
                    @endif
                </div>
            </form>

            <!-- Horizontal Category Filter Pills -->
            <div class="flex items-center gap-2 overflow-x-auto pb-1 pt-1">
                <a href="{{ route('catalog.index', array_merge(request()->except('category', 'page'))) }}"
                   class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all {{ !request('category') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                    Semua Kategori
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('catalog.index', array_merge(request()->except('category', 'page'), ['category' => $cat->id])) }}"
                       class="px-4 py-2 rounded-xl text-xs font-bold shrink-0 transition-all flex items-center gap-1.5 {{ request('category') == $cat->id ? 'bg-rose-500 text-white shadow-sm shadow-rose-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        <span>{{ $cat->name }}</span>
                        <span class="text-[10px] px-1.5 py-0.2 rounded-full {{ request('category') == $cat->id ? 'bg-rose-700 text-white' : 'bg-slate-200 text-slate-600' }}">
                            {{ $cat->products_count }}
                        </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- PRODUCT GRID -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-5">
            @forelse($products as $prod)
                @php
                    $totalStock = $prod->total_stock;
                    $isLowStock = $prod->is_low_stock;
                    $distinctSizes = $prod->variants->pluck('size')->filter()->unique();
                    $distinctColors = $prod->variants->pluck('color')->filter()->unique();
                @endphp
                <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden flex flex-col justify-between group hover:border-rose-300 hover:shadow-md transition-all">
                    <!-- Image & Badges -->
                    <div class="relative bg-gradient-to-b from-rose-50/50 to-slate-50 h-52 flex items-center justify-center p-4 overflow-hidden">
                        <div class="w-24 h-24 rounded-2xl bg-white/80 border border-rose-100 shadow-sm flex items-center justify-center text-rose-500 group-hover:scale-110 transition-transform">
                            <x-icon name="catalog" class="w-12 h-12" />
                        </div>

                        <!-- Top Tag: Category -->
                        <div class="absolute top-3 left-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-white/90 backdrop-blur-sm text-slate-700 shadow-xs border border-slate-100">
                                {{ $prod->category->name ?? 'Kategori' }}
                            </span>
                        </div>

                        <!-- Stock Status Badge -->
                        <div class="absolute top-3 right-3">
                            @if($totalStock == 0)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-500 text-white shadow-xs">Habis</span>
                            @elseif($isLowStock)
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-500 text-white shadow-xs">Stok Sisa {{ $totalStock }}</span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-500 text-white shadow-xs">Stok: {{ $totalStock }}</span>
                            @endif
                        </div>
                    </div>

                    <!-- Details -->
                    <div class="p-5 flex-1 flex flex-col justify-between">
                        <div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400 mb-1">
                                <span class="font-mono">{{ $prod->sku }}</span>
                                <span>{{ $prod->brand ?? 'Rose Brand' }}</span>
                            </div>

                            <a href="{{ route('catalog.show', $prod->id) }}" class="font-bold text-slate-900 text-sm hover:text-rose-600 transition-colors line-clamp-1">
                                {{ $prod->name }}
                            </a>

                            <!-- Sizes & Colors preview -->
                            <div class="mt-2.5 flex flex-wrap items-center gap-1">
                                @if($distinctSizes->isNotEmpty())
                                    <span class="text-[10px] font-semibold text-slate-400">Size:</span>
                                    @foreach($distinctSizes->take(4) as $sz)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700">{{ $sz }}</span>
                                    @endforeach
                                @endif
                            </div>

                            <div class="mt-1 flex flex-wrap items-center gap-1">
                                @if($distinctColors->isNotEmpty())
                                    <span class="text-[10px] font-semibold text-slate-400">Warna:</span>
                                    @foreach($distinctColors->take(3) as $cl)
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-rose-50 text-rose-700">{{ $cl }}</span>
                                    @endforeach
                                @endif
                            </div>
                        </div>

                        <!-- Price & CTA -->
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <div>
                                <span class="text-[10px] text-slate-400 block font-medium">Harga Jual</span>
                                <span class="text-base font-black text-slate-900">
                                    Rp {{ number_format($prod->selling_price, 0, ',', '.') }}
                                </span>
                            </div>

                            <a href="{{ route('catalog.show', $prod->id) }}" class="p-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 transition-all" title="Lihat Detail Produk">
                                <x-icon name="eye" class="w-4 h-4" />
                            </a>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200">
                    <x-icon name="products" class="w-12 h-12 text-slate-300 mx-auto mb-3" />
                    <h3 class="text-base font-bold text-slate-800">Tidak ada produk yang ditemukan</h3>
                    <p class="text-xs text-slate-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter kategori Anda.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $products->links() }}
        </div>
    </div>
</x-layouts.app>
