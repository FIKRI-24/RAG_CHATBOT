@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<section>
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-base border border-blue-100 shadow-xs">
                <i class="fa-solid fa-shield-halved"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">
                    Perbarui Kata Sandi
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Pastikan akun Anda menggunakan kombinasi sandi yang aman untuk mencegah akses tidak sah.
                </p>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
            <i class="fa-solid fa-lock"></i> Keamanan Login
        </span>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <!-- Kata Sandi Saat Ini -->
        <div x-data="{ show: false }">
            <label for="update_password_current_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Kata Sandi Saat Ini <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                    <i class="fa-solid fa-key"></i>
                </div>
                <input 
                    id="update_password_current_password" 
                    name="current_password" 
                    :type="show ? 'text' : 'password'" 
                    class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all shadow-xs" 
                    autocomplete="current-password" 
                    placeholder="Ketik sandi saat ini"
                />
                <button 
                    type="button" 
                    @click="show = !show" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                    title="Tampilkan / sembunyikan sandi"
                >
                    <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->getBag('updatePassword')->get('current_password')" class="mt-2" />
        </div>

        <!-- Kata Sandi Baru -->
        <div x-data="{ show: false }">
            <label for="update_password_password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <input 
                    id="update_password_password" 
                    name="password" 
                    :type="show ? 'text' : 'password'" 
                    class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all shadow-xs" 
                    autocomplete="new-password" 
                    placeholder="Minimal 8 karakter acak"
                />
                <button 
                    type="button" 
                    @click="show = !show" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                    title="Tampilkan / sembunyikan sandi"
                >
                    <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->getBag('updatePassword')->get('password')" class="mt-2" />
        </div>

        <!-- Konfirmasi Kata Sandi Baru -->
        <div x-data="{ show: false }">
            <label for="update_password_password_confirmation" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Konfirmasi Kata Sandi Baru <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <input 
                    id="update_password_password_confirmation" 
                    name="password_confirmation" 
                    :type="show ? 'text' : 'password'" 
                    class="w-full pl-10 pr-11 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-blue-600 focus:ring-4 focus:ring-blue-500/10 focus:outline-none transition-all shadow-xs" 
                    autocomplete="new-password" 
                    placeholder="Ulangi kata sandi baru"
                />
                <button 
                    type="button" 
                    @click="show = !show" 
                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600 transition-colors focus:outline-none"
                    title="Tampilkan / sembunyikan sandi"
                >
                    <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                </button>
            </div>
            <x-input-error :messages="$errors->getBag('updatePassword')->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Tips Sandi Aman -->
        <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-[11px] text-slate-500 flex items-start gap-2.5">
            <i class="fa-solid fa-circle-info text-blue-600 mt-0.5 shrink-0"></i>
            <p>
                Gunakan kombinasi minimal 8 karakter yang terdiri dari huruf besar, huruf kecil, angka, dan simbol agar akun Anda tetap terlindungi saat menggunakan komputer lab bersama.
            </p>
        </div>

        <!-- Tombol Aksi Simpan Password -->
        <div class="flex items-center gap-4 pt-2">
            <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 active:scale-95 border border-slate-700"
            >
                <i class="fa-solid fa-key"></i>
                <span>Simpan Kata Sandi Baru</span>
            </button>

            @if (session('status') === 'password-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-xs"
                >
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Kata sandi berhasil diperbarui!</span>
                </div>
            @endif
        </div>
    </form>
</section>
