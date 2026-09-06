<x-premium-layout>
    <div class="space-y-6">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border-2 border-slate-100 shadow-sm">
            <div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-user-graduate text-[#008546]"></i> Manajemen Data & Login Siswa
                </h1>
                <p class="text-xs text-slate-500 mt-1">Kelola akun siswa, buat akun baru, serta atur reset password login siswa.</p>
            </div>
            
            <button onclick="openAddModal()" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white px-4 py-2.5 rounded-xl font-semibold text-xs transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="fa-solid fa-plus text-sm"></i>
                <span>Tambah Siswa Baru</span>
            </button>
        </div>

        <!-- Alert Notifications -->
        @if(session('success'))
            <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between text-xs font-semibold animate-fade-in">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                    <span>{{ session('success') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl text-xs space-y-1">
                <div class="font-bold flex items-center gap-2 text-rose-700">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>Terjadi kesalahan saat memproses data:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border-2 border-slate-100 shadow-sm flex flex-col sm:flex-row items-center justify-between gap-4">
            <form method="GET" action="{{ route('guru.siswa.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                <div class="relative w-full sm:w-72">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama atau email..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 pl-9 pr-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                </div>
                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-3 py-2.5 rounded-xl text-xs transition-colors">
                    Cari
                </button>
                @if(request('search'))
                    <a href="{{ route('guru.siswa.index') }}" class="text-xs text-rose-600 hover:underline px-2">Reset</a>
                @endif
            </form>

            <div class="text-xs font-medium text-slate-500">
                Total Siswa Terdaftar: <span class="font-bold text-slate-800">{{ number_format($totalSiswa) }}</span>
            </div>
        </div>

        <!-- Table Data Siswa -->
        <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-semibold uppercase tracking-wider">
                            <th class="py-4 px-6 w-12">#</th>
                            <th class="py-4 px-6">Nama Siswa</th>
                            <th class="py-4 px-6">Email / Username Login</th>
                            <th class="py-4 px-6">Tanggal Terdaftar</th>
                            <th class="py-4 px-6 text-center w-36">Aksi & Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($siswas as $index => $siswa)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-4 px-6 text-slate-400 font-medium">
                                    {{ $siswas->firstItem() + $index }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                            {{ strtoupper(substr($siswa->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-800">{{ $siswa->name }}</p>
                                            <span class="text-[10px] text-emerald-600 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/50">Siswa TKJ</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-4 px-6 font-medium text-slate-700">
                                    <i class="fa-regular fa-envelope text-slate-400 mr-1"></i>
                                    {{ $siswa->email }}
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    {{ $siswa->created_at->format('d M Y, H:i') }}
                                </td>
                                <td class="py-4 px-6">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Edit & Reset Password Button -->
                                        <button onclick="openEditModal({{ json_encode($siswa) }})" class="p-2 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-[#008546] rounded-xl border border-slate-200 transition-colors" title="Edit & Reset Password">
                                            <i class="fa-solid fa-[#008546] fa-key text-xs"></i>
                                        </button>
                                        
                                        <!-- Hapus Button -->
                                        <form method="POST" action="{{ route('guru.siswa.destroy', $siswa->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun siswa ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="p-2 bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 rounded-xl border border-slate-200 transition-colors" title="Hapus Akun">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-400">
                                    <i class="fa-solid fa-user-slash text-3xl mb-2 block text-slate-300"></i>
                                    Belum ada data siswa terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 bg-slate-50 border-t border-slate-100">
                {{ $siswas->links() }}
            </div>
        </div>

    </div>

    <!-- MODAL TAMBAH SISWA -->
    <div id="addModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-fade-in relative">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-[#008546]"></i> Tambah Akun Siswa Baru
                </h3>
                <button onclick="closeAddModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('guru.siswa.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" name="name" required placeholder="Contoh: Ahmad Rizki" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email / Username Login</label>
                    <input type="email" name="email" required placeholder="siswa@smkn1kinali.sch.id" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password</label>
                    <input type="password" name="password" required placeholder="Minimal 8 karakter" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="closeAddModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#008546] hover:bg-[#00703c] text-white rounded-xl text-xs font-bold shadow-md">
                        Simpan Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT & RESET PASSWORD SISWA -->
    <div id="editModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 flex items-center justify-center p-4 hidden">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-100 animate-fade-in relative">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-user-pen text-[#008546]"></i> Edit & Reset Password Siswa
                </h3>
                <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form id="editForm" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nama Lengkap Siswa</label>
                    <input type="text" id="edit_name" name="name" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email / Username Login</label>
                    <input type="email" id="edit_email" name="email" required class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                </div>

                <div class="pt-2 border-t border-slate-100">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Password Baru (Reset Password)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah password" class="w-full bg-slate-50 border border-slate-200 rounded-xl px-3.5 py-2.5 text-xs outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                    <p class="text-[10px] text-slate-400 mt-1">Isi kolom ini jika Anda ingin mengganti/reset password siswa yang lupa.</p>
                </div>

                <div class="pt-3 flex justify-end gap-2">
                    <button type="button" onclick="closeEditModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-[#008546] hover:bg-[#00703c] text-white rounded-xl text-xs font-bold shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Scripts for Modals -->
    <script>
        function openAddModal() {
            document.getElementById('addModal').classList.remove('hidden');
        }
        function closeAddModal() {
            document.getElementById('addModal').classList.add('hidden');
        }

        function openEditModal(siswa) {
            document.getElementById('edit_name').value = siswa.name;
            document.getElementById('edit_email').value = siswa.email;
            
            // Set dynamic action URL
            const form = document.getElementById('editForm');
            form.action = `/guru/siswa/${siswa.id}`;

            document.getElementById('editModal').classList.remove('hidden');
        }
        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }
    </script>
</x-premium-layout>
