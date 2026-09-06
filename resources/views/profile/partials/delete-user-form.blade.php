<section class="space-y-4">
    <div class="flex items-center justify-between pb-3 border-b border-rose-100">
        <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm border border-rose-200">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h4 class="text-sm font-bold text-slate-900">
                Zona Bahaya: Hapus Akun
            </h4>
        </div>
        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-50 text-rose-600 border border-rose-200">
            Permanen
        </span>
    </div>

    <p class="text-xs text-slate-500 leading-relaxed">
        Setelah akun Anda dihapus, semua data profil, riwayat unduhan, dan log percakapan AI Anda akan dihapus secara permanen dari server SMK N 1 Kinali.
    </p>

    <div>
        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 rounded-xl font-bold text-xs transition-all hover:scale-105 active:scale-95 shadow-xs"
        >
            <i class="fa-solid fa-trash-can"></i>
            <span>Hapus Akun Saya</span>
        </button>
    </div>

    <!-- Modal Konfirmasi Hapus Akun -->
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 space-y-6">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-100 text-rose-600 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">
                        Apakah Anda yakin ingin menghapus akun?
                    </h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan.
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                    Kata Sandi Konfirmasi <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                        <i class="fa-solid fa-lock"></i>
                    </div>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 focus:outline-none transition-all shadow-xs"
                        placeholder="Ketik kata sandi Anda untuk verifikasi"
                    />
                </div>
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button 
                    type="button" 
                    x-on:click="$dispatch('close')" 
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition-all"
                >
                    Batalkan
                </button>

                <button 
                    type="submit" 
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 active:scale-95"
                >
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Ya, Hapus Akun Permanen</span>
                </button>
            </div>
        </form>
    </x-modal>
</section>
