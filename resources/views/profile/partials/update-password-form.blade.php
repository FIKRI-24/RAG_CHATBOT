@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<section>
    <div class="mb-6">
        <h2 class="text-base sm:text-lg font-bold text-slate-900">
            Perbarui Kata Sandi
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Gunakan kata sandi minimal 8 karakter agar akun Anda tetap aman.
        </p>
    </div>

    <form method="post" action="{{ route('password.update') }}" class="space-y-5">
        @csrf
        @method('put')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <!-- Kata Sandi Saat Ini -->
            <div class="md:col-span-2" x-data="{ show: false }">
                <label for="update_password_current_password" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Kata sandi saat ini <span class="text-rose-600">*</span>
                </label>
                <div class="relative md:w-1/2">
                    <input
                        id="update_password_current_password"
                        name="current_password"
                        :type="show ? 'text' : 'password'"
                        class="w-full pl-3.5 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                        autocomplete="current-password"
                        placeholder="Masukkan kata sandi saat ini"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        aria-label="Tampilkan atau sembunyikan kata sandi saat ini"
                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors focus:outline-none"
                    >
                        <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->getBag('updatePassword')->get('current_password')" class="mt-1.5" />
            </div>

            <!-- Kata Sandi Baru -->
            <div x-data="{ show: false }">
                <label for="update_password_password" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Kata sandi baru <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        id="update_password_password"
                        name="password"
                        :type="show ? 'text' : 'password'"
                        class="w-full pl-3.5 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                        autocomplete="new-password"
                        placeholder="Minimal 8 karakter"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        aria-label="Tampilkan atau sembunyikan kata sandi baru"
                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors focus:outline-none"
                    >
                        <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->getBag('updatePassword')->get('password')" class="mt-1.5" />
            </div>

            <!-- Konfirmasi Kata Sandi Baru -->
            <div x-data="{ show: false }">
                <label for="update_password_password_confirmation" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Konfirmasi kata sandi baru <span class="text-rose-600">*</span>
                </label>
                <div class="relative">
                    <input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        :type="show ? 'text' : 'password'"
                        class="w-full pl-3.5 pr-11 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                        autocomplete="new-password"
                        placeholder="Ulangi kata sandi baru"
                    />
                    <button
                        type="button"
                        @click="show = !show"
                        aria-label="Tampilkan atau sembunyikan konfirmasi kata sandi"
                        class="absolute inset-y-0 right-0 px-3.5 flex items-center text-slate-400 hover:text-slate-700 transition-colors focus:outline-none"
                    >
                        <i :class="show ? 'fa-solid fa-eye-slash' : 'fa-solid fa-eye'" class="text-sm"></i>
                    </button>
                </div>
                <x-input-error :messages="$errors->getBag('updatePassword')->get('password_confirmation')" class="mt-1.5" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-sm rounded-xl transition-colors"
            >
                <i class="fa-solid fa-key text-xs"></i>
                <span>Simpan kata sandi baru</span>
            </button>

            @if (session('status') === 'password-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-[#008546]"
                >
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Kata sandi diperbarui</span>
                </span>
            @endif
        </div>
    </form>
</section>
