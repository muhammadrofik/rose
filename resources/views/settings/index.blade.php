<x-layouts.app>
    <x-slot:title>Pengaturan Toko — Rose Colection</x-slot:title>

    <div class="max-w-4xl mx-auto space-y-6">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Pengaturan Toko & POS</h1>
            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi identitas butik, kontak toko, teks struk kasir thermal, mata uang, dan timezone.</p>
        </div>

        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf

            <!-- Section: Identitas Toko -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">1. Profil & Identitas Toko</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Toko *</label>
                        <input type="text" name="store_name" value="{{ old('store_name', $settings['store_name']) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tagline Toko</label>
                        <input type="text" name="store_tagline" value="{{ old('store_tagline', $settings['store_tagline']) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. Telepon / WhatsApp *</label>
                        <input type="text" name="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email Toko</label>
                        <input type="email" name="store_email" value="{{ old('store_email', $settings['store_email']) }}"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Toko Lengkap *</label>
                        <textarea name="store_address" rows="2" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">{{ old('store_address', $settings['store_address']) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Section: Konfigurasi Struk & POS -->
            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-4">
                <h2 class="text-sm font-bold text-slate-900 uppercase tracking-wider">2. Pengaturan Struk Kasir & Sistem</h2>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Mata Uang</label>
                        <input type="text" name="currency" value="{{ old('currency', $settings['currency']) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Zona Waktu (Timezone) *</label>
                        <input type="text" name="timezone" value="{{ old('timezone', $settings['timezone']) }}" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Batas Minimum Stok Global *</label>
                        <input type="number" name="min_stock_alert_threshold" value="{{ old('min_stock_alert_threshold', $settings['min_stock_alert_threshold']) }}" required min="1"
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>

                    <div class="sm:col-span-3">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Teks Footer Struk Kasir *</label>
                        <textarea name="receipt_footer" rows="3" required
                            class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">{{ old('receipt_footer', $settings['receipt_footer']) }}</textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Dicetak di bagian bawah struk thermal (pesan terima kasih, kebijakan retur, media sosial toko).</p>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-3">
                <button type="submit" class="px-6 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 transition-all">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
