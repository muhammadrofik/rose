<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Rose Colection POS & Retail Management</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#faf7f5] text-slate-800 antialiased min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md" x-data="{ email: '', password: '' }">
        <!-- Logo & Brand Header -->
        <div class="text-center mb-8">
            <div class="inline-flex w-16 h-16 rounded-2xl bg-gradient-to-tr from-rose-700 via-rose-600 to-rose-500 items-center justify-center text-white shadow-xl shadow-rose-200 mb-4">
                <x-icon name="catalog" class="w-8 h-8" />
            </div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Rose Colection</h1>
            <p class="text-sm text-slate-500 mt-1">Sistem Manajemen Toko Fashion & Kasir POS</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-3xl p-8 border border-slate-200/80 shadow-xl shadow-slate-100">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-slate-900">Masuk ke Sistem</h2>
                <p class="text-xs text-slate-500 mt-0.5">Silakan masukkan akun Anda untuk melanjutkan</p>
            </div>

            <!-- Demo Quick Fill Buttons -->
            <div class="mb-6 p-3 bg-rose-50/60 rounded-2xl border border-rose-100">
                <p class="text-[11px] font-bold text-rose-800 uppercase tracking-wider mb-2">Akun Demo Pengujian:</p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" @click="email = 'admin@rosecolection.com'; password = 'password123'" class="px-3 py-2 bg-white rounded-xl text-left border border-rose-200 hover:border-rose-400 hover:bg-rose-50 transition-all text-xs font-semibold text-slate-800 shadow-sm">
                        <div class="text-rose-700 font-bold">Admin / Owner</div>
                        <div class="text-[10px] text-slate-400">admin@rosecolection.com</div>
                    </button>
                    <button type="button" @click="email = 'kasir@rosecolection.com'; password = 'password123'" class="px-3 py-2 bg-white rounded-xl text-left border border-rose-200 hover:border-rose-400 hover:bg-rose-50 transition-all text-xs font-semibold text-slate-800 shadow-sm">
                        <div class="text-rose-700 font-bold">Kasir Toko</div>
                        <div class="text-[10px] text-slate-400">kasir@rosecolection.com</div>
                    </button>
                </div>
            </div>

            <!-- Validation Errors -->
            @if ($errors->any())
                <div class="mb-5 p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-xs">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 mb-1.5">Alamat Email</label>
                    <div class="relative">
                        <input type="email" id="email" name="email" x-model="email" value="{{ old('email') }}" required autofocus
                            placeholder="nama@rosecolection.com"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 text-sm transition-all outline-none">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold text-slate-700 mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <input type="password" id="password" name="password" x-model="password" required
                            placeholder="••••••••"
                            class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 text-sm transition-all outline-none">
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer select-none text-slate-600">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-rose-600 focus:ring-rose-500">
                        <span>Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 px-4 bg-gradient-to-r from-rose-600 to-rose-500 hover:from-rose-700 hover:to-rose-600 text-white rounded-xl text-sm font-bold shadow-lg shadow-rose-200 hover:shadow-xl hover:shadow-rose-300 transition-all">
                    Masuk Sekarang
                </button>
            </form>
        </div>

        <p class="text-center text-xs text-slate-400 mt-6">
            Rose Colection • Sistem POS & Manajemen Inventaris Terpadu
        </p>
    </div>
</body>
</html>
