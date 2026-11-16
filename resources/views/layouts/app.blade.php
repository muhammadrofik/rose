<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Rose Colection' }} — Sistem Manajemen Toko Retail</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#faf7f5] text-slate-800 antialiased selection:bg-rose-100 selection:text-rose-900 min-h-screen">
    <div class="flex h-screen overflow-hidden">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-white border-r border-slate-200/80 flex flex-col justify-between shrink-0 z-30 transition-all duration-300">
            <!-- Sidebar Header / Brand -->
            <div class="h-20 flex items-center px-6 gap-3 border-b border-slate-100">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-rose-700 to-rose-500 flex items-center justify-center text-white shadow-md shadow-rose-200">
                    <x-icon name="catalog" class="w-5 h-5" />
                </div>
                <div>
                    <h1 class="text-base font-bold tracking-tight text-slate-900 flex items-center gap-1.5">
                        Rose Colection
                    </h1>
                    <p class="text-xs text-rose-600 font-medium tracking-wide">Fashion Boutique POS</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="flex-1 overflow-y-auto px-4 py-5 space-y-6">
                <!-- Group: Utama -->
                <div>
                    <p class="px-3 text-[11px] font-semibold tracking-wider text-slate-400 uppercase mb-2">Utama</p>
                    <nav class="space-y-1">
                        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="dashboard" class="w-4 h-4" />
                            <span>Dashboard</span>
                        </a>

                        <a href="{{ route('catalog.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('catalog.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="grid" class="w-4 h-4" />
                            <span>Katalog Barang</span>
                        </a>

                        <a href="{{ route('pos.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('pos.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-700 bg-rose-50/50 hover:bg-rose-50 text-rose-800' }}">
                            <div class="flex items-center gap-3">
                                <x-icon name="pos" class="w-4 h-4 text-rose-600" />
                                <span class="font-semibold">Kasir (POS)</span>
                            </div>
                            <span class="text-[10px] uppercase font-bold px-1.5 py-0.5 rounded bg-rose-200/80 text-rose-800">Aktif</span>
                        </a>

                        <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('sales.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="history" class="w-4 h-4" />
                            <span>Riwayat Penjualan</span>
                        </a>
                    </nav>
                </div>

                <!-- Group: Master Data & Stok -->
                @can('manage-products')
                <div>
                    <p class="px-3 text-[11px] font-semibold tracking-wider text-slate-400 uppercase mb-2">Master & Persediaan</p>
                    <nav class="space-y-1">
                        <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="products" class="w-4 h-4" />
                            <span>Data Barang</span>
                        </a>

                        <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('categories.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="tag" class="w-4 h-4" />
                            <span>Kategori</span>
                        </a>

                        <a href="{{ route('suppliers.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('suppliers.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="supplier" class="w-4 h-4" />
                            <span>Supplier</span>
                        </a>

                        <a href="{{ route('inventory.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('inventory.index') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="inventory" class="w-4 h-4" />
                            <span>Stok Barang</span>
                        </a>

                        <a href="{{ route('purchases.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('purchases.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="in" class="w-4 h-4" />
                            <span>Barang Masuk (PO)</span>
                        </a>

                        <a href="{{ route('inventory.adjustments.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('inventory.adjustments.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="adjust" class="w-4 h-4" />
                            <span>Penyesuaian Stok</span>
                        </a>

                        <a href="{{ route('inventory.movements') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('inventory.movements') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="history" class="w-4 h-4" />
                            <span>Mutasi Ledger Stok</span>
                        </a>
                    </nav>
                </div>
                @endcan

                <!-- Group: Finansial & Laporan (Admin Only) -->
                @can('view-profit')
                <div>
                    <p class="px-3 text-[11px] font-semibold tracking-wider text-slate-400 uppercase mb-2">Finansial & Laporan</p>
                    <nav class="space-y-1">
                        <a href="{{ route('profit.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('profit.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="profit" class="w-4 h-4" />
                            <span>HPP & Keuntungan</span>
                        </a>

                        <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="reports" class="w-4 h-4" />
                            <span>Laporan Lengkap</span>
                        </a>
                    </nav>
                </div>
                @endcan

                <!-- Group: Pengaturan (Admin Only) -->
                @can('manage-settings')
                <div>
                    <p class="px-3 text-[11px] font-semibold tracking-wider text-slate-400 uppercase mb-2">Sistem</p>
                    <nav class="space-y-1">
                        <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="users" class="w-4 h-4" />
                            <span>Kelola Pengguna</span>
                        </a>

                        <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-rose-500 text-white shadow-sm shadow-rose-200 font-semibold' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100/80' }}">
                            <x-icon name="settings" class="w-4 h-4" />
                            <span>Pengaturan Toko</span>
                        </a>
                    </nav>
                </div>
                @endcan
            </div>

            <!-- User Info & Logout Footer -->
            <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-full bg-rose-100 text-rose-800 font-bold flex items-center justify-center text-xs shrink-0 ring-2 ring-rose-200">
                            {{ substr(auth()->user()->name ?? 'U', 0, 2) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name ?? 'Pengguna' }}</p>
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[10px] font-medium {{ auth()->user()->isAdmin() ? 'bg-rose-100 text-rose-700' : 'bg-slate-200 text-slate-700' }}">
                                {{ auth()->user()->isAdmin() ? 'Admin / Owner' : 'Kasir Toko' }}
                            </span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar dari akun" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors">
                            <x-icon name="log-out" class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- MAIN CONTENT AREA -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- TOPBAR -->
            <header class="h-20 bg-white/90 backdrop-blur-sm border-b border-slate-200/80 flex items-center justify-between px-8 z-20 shrink-0">
                <!-- Left: Branch / Status -->
                <div class="flex items-center gap-4">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-slate-100 border border-slate-200/80 text-xs font-semibold text-slate-700">
                        <x-icon name="store" class="w-3.5 h-3.5 text-rose-600" />
                        <span>{{ \App\Models\Setting::get('store_name', 'Rose Colection') }}</span>
                        <span class="text-slate-400">•</span>
                        <span class="text-slate-500 font-normal">Panakkukang, Makassar</span>
                    </div>

                    <div class="hidden md:flex items-center gap-1.5 text-xs text-slate-500">
                        <x-icon name="history" class="w-3.5 h-3.5 text-slate-400" />
                        <span>WITA (Asia/Makassar):</span>
                        <span class="font-medium text-slate-700" id="current-clock">{{ now()->setTimezone('Asia/Makassar')->format('d M Y, H:i') }}</span>
                    </div>
                </div>

                <!-- Right: Quick POS Access & Search -->
                <div class="flex items-center gap-4">
                    <a href="{{ route('pos.index') }}" class="flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all hover:shadow-lg hover:shadow-rose-300">
                        <x-icon name="pos" class="w-4 h-4" />
                        <span>Buka Kasir (POS)</span>
                    </a>

                    <div class="w-px h-6 bg-slate-200"></div>

                    <!-- Role Pill -->
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-full bg-rose-500 text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="text-left hidden sm:block">
                            <p class="text-xs font-bold text-slate-800 leading-tight">{{ auth()->user()->name ?? '' }}</p>
                            <p class="text-[10px] text-slate-400 leading-tight">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <!-- FLASH ALERTS -->
            @if(session('success'))
                <div class="mx-8 mt-4 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between text-sm shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-3">
                        <x-icon name="check" class="w-5 h-5 text-emerald-600" />
                        <span class="font-medium">{{ session('success') }}</span>
                    </div>
                    <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="mx-8 mt-4 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center justify-between text-sm shadow-sm" x-data="{ show: true }" x-show="show">
                    <div class="flex items-center gap-3">
                        <x-icon name="alert" class="w-5 h-5 text-rose-600" />
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                    <button @click="show = false" class="text-rose-500 hover:text-rose-700">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>
            @endif

            @if($errors->any())
                <div class="mx-8 mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-sm shadow-sm">
                    <div class="flex items-center gap-2 font-bold mb-1">
                        <x-icon name="alert" class="w-4 h-4 text-amber-600" />
                        <span>Terdapat kesalahan validasi:</span>
                    </div>
                    <ul class="list-disc list-inside text-xs space-y-0.5 ml-2">
                        @foreach($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- PAGE CONTENT BODY -->
            <main class="flex-1 overflow-y-auto px-8 py-6">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
