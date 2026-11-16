<x-layouts.app>
    <x-slot:title>Kelola Pengguna — Rose Colection</x-slot:title>

    <div class="space-y-6" x-data="{ showAddModal: false, editModal: false, editUser: {} }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Manajemen Pengguna</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola akun Admin / Owner toko dan Kasir dengan kontrol hak akses berbasis peran (RBAC).</p>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Nama Pengguna</th>
                            <th class="py-3.5 px-6">Email Login</th>
                            <th class="py-3.5 px-6">Peran (Role)</th>
                            <th class="py-3.5 px-6">No. Telepon</th>
                            <th class="py-3.5 px-6 text-center">Riwayat Transaksi</th>
                            <th class="py-3.5 px-6 text-center">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @foreach($users as $u)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6">
                                    <div class="font-bold text-slate-900 text-sm flex items-center gap-2">
                                        <span>{{ $u->name }}</span>
                                        @if($u->id === auth()->id())
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 text-slate-600">Anda</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-mono text-slate-600">{{ $u->email }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold {{ $u->isAdmin() ? 'bg-rose-100 text-rose-800' : 'bg-slate-100 text-slate-700' }}">
                                        {{ $u->isAdmin() ? 'Admin / Owner' : 'Kasir Toko' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6 font-mono text-slate-500">{{ $u->phone ?? '-' }}</td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700 font-bold">
                                        {{ $u->sales_count }} Penjualan
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-center">
                                    @if($u->is_active)
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Aktif</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-200 text-slate-600">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editUser = @json($u); editModal = true" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Edit">
                                            <x-icon name="edit" class="w-4 h-4" />
                                        </button>
                                        @if($u->id !== auth()->id())
                                            <form method="POST" action="{{ route('users.destroy', $u->id) }}" onsubmit="return confirm('Hapus / nonaktifkan pengguna ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Hapus">
                                                    <x-icon name="trash" class="w-4 h-4" />
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs" style="display: none;">
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Tambah Pengguna Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form method="POST" action="{{ route('users.store') }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" required placeholder="Nama kasir/admin" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email Login *</label>
                        <input type="email" name="email" required placeholder="kasir2@rosecolection.com" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran Akses (Role) *</label>
                        <select name="role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="kasir">Kasir (Hanya POS & Riwayat Pribadi)</option>
                            <option value="admin">Admin / Owner (Akses Penuh Stok & Finansial)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" name="phone" placeholder="08..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi *</label>
                        <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-slate-700">Akun Aktif</span>
                        </label>
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
                    <h3 class="text-base font-bold text-slate-900">Edit Data Pengguna</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form :action="`{{ url('users') }}/${editUser.id}`" method="POST" class="space-y-3">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Lengkap *</label>
                        <input type="text" name="name" x-model="editUser.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alamat Email *</label>
                        <input type="email" name="email" x-model="editUser.email" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Peran Akses (Role) *</label>
                        <select name="role" x-model="editUser.role" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                            <option value="kasir">Kasir</option>
                            <option value="admin">Admin / Owner</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">No. HP / WA</label>
                        <input type="text" name="phone" x-model="editUser.phone" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kata Sandi Baru (Kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" minlength="6" placeholder="••••••••" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="flex items-center gap-2 cursor-pointer pt-1">
                            <input type="checkbox" name="is_active" value="1" :checked="editUser.is_active" class="w-4 h-4 rounded text-rose-600 focus:ring-rose-500">
                            <span class="text-xs font-bold text-slate-700">Akun Aktif</span>
                        </label>
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
