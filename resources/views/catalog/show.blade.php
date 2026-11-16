<x-layouts.app>
    <x-slot:title>{{ $product->name }} — Rose Colection</x-slot:title>

    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Breadcrumb & Back -->
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('catalog.index') }}" class="hover:text-rose-600 flex items-center gap-1 font-semibold">
                <x-icon name="chevron-left" class="w-4 h-4" />
                <span>Kembali ke Katalog</span>
            </a>
            <span>/</span>
            <span class="text-slate-400">{{ $product->category->name ?? 'Kategori' }}</span>
            <span>/</span>
            <span class="text-slate-800 font-bold truncate">{{ $product->name }}</span>
        </div>

        <!-- Product Main Showcase Card -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Left: Product Image & Badges -->
                <div class="bg-gradient-to-b from-rose-50/60 to-slate-50 rounded-2xl p-8 flex items-center justify-center border border-rose-100/60 relative min-h-[360px]">
                    <div class="w-36 h-36 rounded-3xl bg-white shadow-md border border-rose-100 flex items-center justify-center text-rose-500">
                        <x-icon name="catalog" class="w-20 h-20" />
                    </div>

                    <div class="absolute top-4 left-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-white text-slate-800 shadow-sm border border-slate-100">
                            {{ $product->category->name ?? 'Kategori' }}
                        </span>
                    </div>

                    <div class="absolute top-4 right-4">
                        @if($product->total_stock == 0)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-rose-500 text-white shadow-sm">Stok Habis</span>
                        @elseif($product->is_low_stock)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-500 text-white shadow-sm">Stok Menipis ({{ $product->total_stock }})</span>
                        @else
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-500 text-white shadow-sm">Stok Tersedia ({{ $product->total_stock }})</span>
                        @endif
                    </div>
                </div>

                <!-- Right: Information & Variants -->
                <div class="flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-mono text-slate-400 mb-1">
                            <span>SKU: {{ $product->sku }}</span>
                            @if($product->barcode)
                                <span>• Barcode: {{ $product->barcode }}</span>
                            @endif
                        </div>

                        <h1 class="text-2xl font-black text-slate-900 tracking-tight">{{ $product->name }}</h1>
                        <p class="text-xs text-rose-600 font-bold mt-0.5">{{ $product->brand ?? 'Rose Signature' }}</p>

                        <!-- Price display -->
                        <div class="mt-4 p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-baseline justify-between">
                            <div>
                                <span class="text-xs text-slate-400 block font-medium">Harga Retail</span>
                                <span class="text-3xl font-extrabold text-slate-900">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </span>
                            </div>
                            @can('view-hpp')
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block font-medium">HPP Berjalan</span>
                                <span class="text-sm font-bold text-slate-600">
                                    Rp {{ number_format($product->current_hpp, 0, ',', '.') }}
                                </span>
                            </div>
                            @endcan
                        </div>

                        <!-- Description -->
                        <div class="mt-4">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi Produk</h3>
                            <p class="text-xs text-slate-600 leading-relaxed">{{ $product->description ?? 'Tidak ada deskripsi produk.' }}</p>
                        </div>

                        <!-- Varian Table -->
                        <div class="mt-6">
                            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Pilihan Varian & Stok</h3>
                            <div class="border border-slate-100 rounded-2xl overflow-hidden text-xs">
                                <table class="w-full text-left">
                                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                                        <tr>
                                            <th class="py-2.5 px-4">Nama Varian</th>
                                            <th class="py-2.5 px-4">Ukuran</th>
                                            <th class="py-2.5 px-4">Warna</th>
                                            <th class="py-2.5 px-4 text-center">Stok</th>
                                            <th class="py-2.5 px-4 text-right">Harga</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        @foreach($product->variants as $var)
                                            <tr>
                                                <td class="py-2.5 px-4 font-bold text-slate-800">{{ $var->variant_name }}</td>
                                                <td class="py-2.5 px-4 text-slate-600">{{ $var->size ?? '-' }}</td>
                                                <td class="py-2.5 px-4 text-slate-600">{{ $var->color ?? '-' }}</td>
                                                <td class="py-2.5 px-4 text-center">
                                                    <span class="font-bold {{ $var->stock <= 0 ? 'text-rose-600' : ($var->stock <= $product->min_stock ? 'text-amber-600' : 'text-emerald-600') }}">
                                                        {{ $var->stock }}
                                                    </span>
                                                </td>
                                                <td class="py-2.5 px-4 text-right font-semibold text-slate-800">
                                                    Rp {{ number_format($var->selling_price, 0, ',', '.') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="mt-8 pt-6 border-t border-slate-100 flex items-center gap-3">
                        <a href="{{ route('pos.index') }}" class="flex-1 py-3 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold text-center shadow-md shadow-rose-200 transition-all flex items-center justify-center gap-2">
                            <x-icon name="cart" class="w-4 h-4" />
                            <span>Buka di Kasir POS</span>
                        </a>

                        @can('manage-products')
                        <a href="{{ route('products.edit', $product->id) }}" class="py-3 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-all flex items-center gap-1.5">
                            <x-icon name="edit" class="w-4 h-4" />
                            <span>Edit Produk</span>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>

        <!-- Related Products in Same Category -->
        @if($relatedProducts->isNotEmpty())
            <div class="pt-4">
                <h2 class="text-base font-bold text-slate-900 mb-4">Produk Terkait dalam Kategori {{ $product->category->name }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach($relatedProducts as $rel)
                        <a href="{{ route('catalog.show', $rel->id) }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 hover:border-rose-300 transition-all group block">
                            <div class="h-32 bg-rose-50/50 rounded-xl flex items-center justify-center text-rose-400 mb-3 group-hover:scale-105 transition-transform">
                                <x-icon name="catalog" class="w-10 h-10" />
                            </div>
                            <h4 class="text-xs font-bold text-slate-900 group-hover:text-rose-600 truncate">{{ $rel->name }}</h4>
                            <p class="text-[11px] font-extrabold text-slate-800 mt-1">Rp {{ number_format($rel->selling_price, 0, ',', '.') }}</p>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-layouts.app>
