<x-layouts.app>
    <x-slot:title>Supplier — Rose Colection</x-slot:title>

    <div class="space-y-6" x-data="{ showAddModal: false, editModal: false, editSupplier: {} }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Supplier & Mitra Konveksi</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola data vendor pemasok bahan, pakaian, hijab, dan aksesoris.</p>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Supplier</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Nama Supplier</th>
                            <th class="py-3.5 px-6">Kontak Person</th>
                            <th class="py-3.5 px-6">No. Telepon / WhatsApp</th>
                            <th class="py-3.5 px-6">Email</th>
                            <th class="py-3.5 px-6">Alamat</th>
                            <th class="py-3.5 px-6 text-center">Transaksi PO</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($suppliers as $sup)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                                    {{ $sup->name }}
                                </td>
                                <td class="py-4 px-6 text-slate-700">
                                    {{ $sup->contact_person ?? '-' }}
                                </td>
                                <td class="py-4 px-6 font-mono text-slate-600">
                                    {{ $sup->phone ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    {{ $sup->email ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-slate-500 max-w-xs truncate">
                                    {{ $sup->address ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-700">
                                        {{ $sup->purchases_count }} PO
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editSupplier = @json($sup); editModal = true" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Edit">
                                            <x-icon name="edit" class="w-4 h-4" />
                                        </button>
                                        <form method="POST" action="{{ route('suppliers.destroy', $sup->id) }}" onsubmit="return confirm('Hapus supplier ini?')">
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
                                <td colspan="7" class="py-8 text-center text-slate-400">Belum ada supplier terdaftar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $suppliers->links() }}
            </div>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs" style="display: none;">
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Tambah Supplier Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form method="POST" action="{{ route('suppliers.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / Supplier *</label>
                        <input type="text" name="name" required placeholder="Contoh: Konveksi Gamis Bandung" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kontak Person</label>
                        <input type="text" name="contact_person" placeholder="Ibu Rina" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" placeholder="081234567890" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" placeholder="vendor@domain.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Gudang / Kantor</label>
                        <textarea name="address" rows="2" placeholder="Alamat lengkap..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Edit Modal -->
        <div x-show="editModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs" style="display: none;">
            <div @click.away="editModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Edit Data Supplier</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form :action="`{{ url('suppliers') }}/${editSupplier.id}`" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Perusahaan / Supplier *</label>
                        <input type="text" name="name" x-model="editSupplier.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kontak Person</label>
                        <input type="text" name="contact_person" x-model="editSupplier.contact_person" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WhatsApp</label>
                        <input type="text" name="phone" x-model="editSupplier.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" x-model="editSupplier.email" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat</label>
                        <textarea name="address" x-model="editSupplier.address" rows="2" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="editModal = false" class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold">Batal</button>
                        <button type="submit" class="px-5 py-2 bg-rose-600 text-white rounded-xl text-xs font-bold">Perbarui</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
