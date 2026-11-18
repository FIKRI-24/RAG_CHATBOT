@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<section>
    <div class="mb-6">
        <h2 class="text-base sm:text-lg font-bold text-slate-900">
            Informasi Profil Akun
        </h2>
        <p class="text-xs sm:text-sm text-slate-500 mt-1">
            Perbarui foto profil, nama lengkap, dan alamat email yang Anda gunakan di sistem.
        </p>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-6" x-data="{
        previewUrl: '{{ $user->avatar_url }}',
        removeAvatar: false,
        handleFileChange(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewUrl = URL.createObjectURL(file);
                this.removeAvatar = false;
            }
        },
        resetPhoto() {
            this.$refs.fileInput.value = '';
            this.previewUrl = null;
            this.removeAvatar = true;
        }
    }">
        @csrf
        @method('patch')

        <input type="hidden" name="remove_avatar" :value="removeAvatar ? '1' : '0'">

        <!-- Foto Profil langsung menyatu tanpa kotak pembungkus samar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="shrink-0">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Pratinjau foto profil" class="w-16 h-16 rounded-full object-cover ring-2 ring-[#008546]">
                    </template>
                    <template x-if="!previewUrl">
                        <div class="w-16 h-16 rounded-full bg-[#008546] text-white flex items-center justify-center font-bold text-xl">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </template>
                </div>
                <div class="space-y-0.5">
                    <p class="text-sm font-semibold text-slate-900">Foto profil</p>
                    <p class="text-xs text-slate-500">Format JPG, PNG, atau WEBP dengan ukuran maksimal 2 MB.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2 shrink-0">
                <input
                    type="file"
                    name="avatar"
                    id="avatar"
                    accept="image/jpeg,image/png,image/jpg,image/webp"
                    class="hidden"
                    x-ref="fileInput"
                    @change="handleFileChange($event)"
                >
                <button
                    type="button"
                    @click="$refs.fileInput.click()"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-xs rounded-xl transition-colors"
                >
                    <i class="fa-solid fa-camera text-[#008546]"></i>
                    <span x-text="previewUrl ? 'Ganti foto' : 'Unggah foto'">Unggah foto</span>
                </button>

                <button
                    type="button"
                    x-show="previewUrl"
                    x-cloak
                    @click="resetPhoto()"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 font-semibold text-xs rounded-xl transition-colors"
                >
                    <i class="fa-solid fa-trash-can"></i>
                    <span>Hapus</span>
                </button>
            </div>
        </div>
        <x-input-error class="-mt-3" :messages="$errors->get('avatar')" />

        <!-- Identitas Dasar: 2 Kolom Sejajar di Desktop -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Nama lengkap <span class="text-rose-600">*</span>
                </label>
                <input
                    id="name"
                    name="name"
                    type="text"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                    value="{{ old('name', $user->name) }}"
                    required
                    autocomplete="name"
                    placeholder="Masukkan nama lengkap Anda"
                />
                <x-input-error class="mt-1.5" :messages="$errors->get('name')" />
            </div>

            <div>
                <label for="email" class="block text-sm font-semibold text-slate-800 mb-1.5">
                    Alamat email <span class="text-rose-600">*</span>
                </label>
                <input
                    id="email"
                    name="email"
                    type="email"
                    class="w-full px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                    value="{{ is_string(old('email', $user->email)) ? old('email', $user->email) : '' }}"
                    required
                    autocomplete="username"
                    placeholder="nama@tkj.com"
                />
                <x-input-error class="mt-1.5" :messages="$errors->get('email')" />
            </div>

            @if($user->isGuru())
                <div class="md:col-span-2">
                    <label for="teacher_number" class="block text-sm font-semibold text-slate-800 mb-1.5">
                        NIP / nomor guru
                    </label>
                    <input
                        id="teacher_number"
                        name="teacher_number"
                        type="text"
                        maxlength="100"
                        value="{{ old('teacher_number', $user->teacher_number) }}"
                        placeholder="Contoh: 198801012015031001"
                        class="w-full md:w-1/2 px-3.5 py-2.5 bg-white border border-slate-300 rounded-xl text-slate-900 text-sm focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 focus:outline-none transition-colors"
                    />
                    <x-input-error class="mt-1.5" :messages="$errors->get('teacher_number')" />
                </div>
            @elseif($user->class_name || $user->student_number)
                @if($user->class_name)
                    <div>
                        <span class="block text-xs font-medium text-slate-500 mb-1">Kelas terdaftar</span>
                        <p class="text-sm font-semibold text-slate-900">{{ $user->class_name }}</p>
                    </div>
                @endif
                @if($user->student_number)
                    <div>
                        <span class="block text-xs font-medium text-slate-500 mb-1">NIS / Nomor Induk</span>
                        <p class="text-sm font-semibold text-slate-900">{{ $user->student_number }}</p>
                    </div>
                @endif
            @endif
        </div>

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="p-3.5 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2.5 text-xs text-amber-800">
                <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5 shrink-0"></i>
                <div>
                    <p class="font-medium">Alamat email Anda belum diverifikasi.</p>
                    <button form="send-verification" class="underline text-amber-900 font-semibold hover:text-amber-700 mt-1 inline-block">
                        Kirim ulang email verifikasi
                    </button>
                </div>
            </div>

            @if (session('status') === 'verification-link-sent')
                <p class="font-semibold text-xs text-[#008546]">
                    Link verifikasi baru telah dikirimkan ke alamat email Anda.
                </p>
            @endif
        @endif

        <div class="flex flex-wrap items-center gap-3 pt-1">
            <button
                type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white font-semibold text-sm rounded-xl transition-colors"
            >
                <i class="fa-solid fa-floppy-disk text-xs"></i>
                <span>Simpan perubahan</span>
            </button>

            @if (session('status') === 'profile-updated')
                <span
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-[#008546]"
                >
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Profil diperbarui</span>
                </span>
            @endif
        </div>
    </form>
</section>
