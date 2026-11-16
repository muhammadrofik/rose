<x-layouts.app>
    <x-slot:title>Kategori Barang — Rose Colection</x-slot:title>

    <div class="space-y-6" x-data="{ showAddModal: false, editModal: false, editCategory: {} }">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-extrabold text-slate-900 tracking-tight">Kategori Produk</h1>
                <p class="text-xs text-slate-500 mt-0.5">Kelola pengelompokan pakaian, busana muslim, dan aksesoris toko.</p>
            </div>
            <button @click="showAddModal = true" class="px-4 py-2.5 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-200 flex items-center gap-2 transition-all">
                <x-icon name="plus" class="w-4 h-4" />
                <span>Tambah Kategori</span>
            </button>
        </div>

        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-400 font-bold uppercase tracking-wider border-b border-slate-100">
                        <tr>
                            <th class="py-3.5 px-6">Nama Kategori</th>
                            <th class="py-3.5 px-6">Slug</th>
                            <th class="py-3.5 px-6">Deskripsi</th>
                            <th class="py-3.5 px-6 text-center">Jumlah Produk</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                        @forelse($categories as $cat)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-4 px-6 font-bold text-slate-900 text-sm">
                                    {{ $cat->name }}
                                </td>
                                <td class="py-4 px-6 font-mono text-slate-500">
                                    {{ $cat->slug }}
                                </td>
                                <td class="py-4 px-6 text-slate-500 max-w-xs truncate">
                                    {{ $cat->description ?? '-' }}
                                </td>
                                <td class="py-4 px-6 text-center">
                                    <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700">
                                        {{ $cat->products_count }} Produk
                                    </span>
                                </td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button @click="editCategory = @json($cat); editModal = true" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors" title="Edit">
                                            <x-icon name="edit" class="w-4 h-4" />
                                        </button>
                                        <form method="POST" action="{{ route('categories.destroy', $cat->id) }}" onsubmit="return confirm('Hapus kategori ini?')">
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
                                <td colspan="5" class="py-8 text-center text-slate-400">Belum ada kategori terdaftar</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-slate-100">
                {{ $categories->links() }}
            </div>
        </div>

        <!-- Add Modal -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs" style="display: none;">
            <div @click.away="showAddModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900">Tambah Kategori Baru</h3>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form method="POST" action="{{ route('categories.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori *</label>
                        <input type="text" name="name" required placeholder="Contoh: Outerwear & Rompi" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Keterangan singkat kategori..." class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none"></textarea>
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
                    <h3 class="text-base font-bold text-slate-900">Edit Kategori</h3>
                    <button @click="editModal = false" class="text-slate-400 hover:text-slate-600"><x-icon name="x" class="w-4 h-4" /></button>
                </div>
                <form :action="`{{ url('categories') }}/${editCategory.id}`" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori *</label>
                        <input type="text" name="name" x-model="editCategory.name" required class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi</label>
                        <textarea name="description" x-model="editCategory.description" rows="3" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-xs focus:border-rose-500 outline-none"></textarea>
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
