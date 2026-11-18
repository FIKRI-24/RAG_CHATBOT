@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<section class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div class="space-y-1 max-w-2xl">
        <div class="flex items-center gap-2">
            <h2 class="text-sm sm:text-base font-bold text-slate-900">
                Zona Bahaya: Hapus Akun
            </h2>
            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-rose-100 text-rose-800">
                Permanen
            </span>
        </div>
        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
            Penghapusan akun guru bersifat permanen. Modul ajar wajib dialihkan ke akun guru aktif lainnya sebelum akun dihapus.
        </p>
    </div>

    <div class="shrink-0">
        <button
            type="button"
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
            class="inline-flex items-center gap-2 px-4 py-2.5 bg-white hover:bg-rose-50 text-rose-700 border border-rose-300 rounded-xl font-semibold text-xs transition-colors"
        >
            <i class="fa-solid fa-trash-can"></i>
            <span>Hapus Akun Saya</span>
        </button>
    </div>

    <!-- Modal Konfirmasi Hapus Akun -->
    <x-modal name="confirm-user-deletion" :show="$errors->getBag('userDeletion')->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8 space-y-5">
            @csrf
            @method('delete')

            <div class="flex items-start gap-4">
                <div class="w-11 h-11 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900">
                        Apakah Anda yakin ingin menghapus akun?
                    </h3>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Tindakan ini bersifat permanen dan tidak dapat dibatalkan. Masukkan kata sandi akun Anda untuk mengonfirmasi penghapusan.
                    </p>
                </div>
            </div>

            @if($user->isGuru())
                <div class="space-y-1.5">
                    <label for="transfer_to" class="block text-sm font-semibold text-slate-800">
                        Guru penerima modul (wajib jika masih memiliki modul)
                    </label>
                    <select id="transfer_to" name="transfer_to" class="block w-full rounded-xl border-slate-300 text-sm focus:border-rose-500 focus:ring-rose-500/20">
                        <option value="">Pilih guru penerima</option>
                        @foreach($transferTeachers as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->getBag('userDeletion')->get('transfer_to')" />
                </div>
            @endif

            <div class="space-y-1.5">
                <label for="password" class="block text-sm font-semibold text-slate-800">
                    Kata sandi konfirmasi <span class="text-rose-600">*</span>
                </label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 focus:outline-none transition-colors"
                    placeholder="Ketik kata sandi Anda untuk verifikasi"
                />
                <x-input-error :messages="$errors->getBag('userDeletion')->get('password')" class="mt-1.5" />
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
                <button
                    type="button"
                    x-on:click="$dispatch('close')"
                    class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition-colors"
                >
                    Batalkan
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-semibold text-xs rounded-xl transition-colors shadow-xs"
                >
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Ya, Hapus Akun Permanen</span>
                </button>
            </div>
        </form>
    </x-modal>
</section>
