<section>
    <div class="flex items-center justify-between pb-4 mb-6 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-base border border-emerald-100 shadow-xs">
                <i class="fa-solid fa-user-pen"></i>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">
                    Informasi Profil Akun
                </h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Perbarui nama lengkap dan alamat email yang Anda gunakan di sistem.
                </p>
            </div>
        </div>
        <span class="hidden sm:inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-600">
            <i class="fa-solid fa-id-card"></i> Identitas Akun
        </span>
    </div>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-5" x-data="{
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

        <!-- Foto Profil -->
        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-3">
            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                Foto Profil Pengguna
            </label>
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-4">
                <!-- Avatar Preview -->
                <div class="relative shrink-0">
                    <template x-if="previewUrl">
                        <img :src="previewUrl" alt="Avatar Preview" class="w-20 h-20 rounded-full object-cover border-2 border-emerald-500 shadow-md">
                    </template>
                    <template x-if="!previewUrl">
                        <div class="w-20 h-20 rounded-full bg-gradient-to-tr from-[#008546] to-emerald-400 text-white flex items-center justify-center font-extrabold text-2xl shadow-md border-2 border-emerald-500/20">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                    </template>
                    <div class="absolute bottom-0 right-0 w-6 h-6 rounded-full bg-emerald-600 text-white flex items-center justify-center text-[10px] shadow border border-white">
                        <i class="fa-solid fa-camera"></i>
                    </div>
                </div>

                <!-- Action buttons and hint -->
                <div class="flex-1 text-center sm:text-left space-y-2">
                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2">
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
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs rounded-xl border border-slate-300 shadow-xs hover:border-[#008546] hover:text-[#008546] transition-all"
                        >
                            <i class="fa-solid fa-cloud-arrow-up text-emerald-600"></i>
                            <span x-text="previewUrl ? 'Ganti Foto' : 'Unggah Foto'">Unggah Foto</span>
                        </button>

                        <button 
                            type="button" 
                            x-show="previewUrl" 
                            @click="resetPhoto()" 
                            class="inline-flex items-center gap-1.5 px-3 py-2 bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs rounded-xl border border-rose-200 transition-all"
                        >
                            <i class="fa-solid fa-trash-can"></i>
                            <span>Hapus Foto</span>
                        </button>
                    </div>
                    <p class="text-[11px] text-slate-500 leading-normal">
                        Format yang didukung: <span class="font-semibold text-slate-700">JPG, PNG, WEBP</span>. Ukuran berkas maksimal <span class="font-semibold text-slate-700">2 MB</span>.
                    </p>
                    <x-input-error class="mt-1" :messages="$errors->get('avatar')" />
                </div>
            </div>
        </div>

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Nama Lengkap <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                    <i class="fa-solid fa-user"></i>
                </div>
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-[#008546] focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all shadow-xs" 
                    value="{{ old('name', $user->name) }}" 
                    required 
                    autofocus 
                    autocomplete="name" 
                    placeholder="Masukkan nama lengkap Anda"
                />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <!-- Alamat Email -->
        <div>
            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                Alamat E-mail <span class="text-rose-500">*</span>
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 text-sm">
                    <i class="fa-solid fa-envelope"></i>
                </div>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    class="w-full pl-10 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm font-medium focus:bg-white focus:border-[#008546] focus:ring-4 focus:ring-emerald-500/10 focus:outline-none transition-all shadow-xs" 
                    value="{{ old('email', $user->email) }}" 
                    required 
                    autocomplete="username" 
                    placeholder="contoh@smkn1kinali.sch.id"
                />
            </div>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-amber-600 mt-0.5 shrink-0 text-sm"></i>
                    <div class="text-xs text-amber-800">
                        <p class="font-medium">Alamat email Anda belum diverifikasi.</p>
                        <button form="send-verification" class="underline text-amber-900 font-bold hover:text-amber-700 mt-1 inline-block">
                            Klik di sini untuk mengirim ulang email verifikasi.
                        </button>
                    </div>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 font-bold text-xs text-emerald-600 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check"></i> Link verifikasi baru telah dikirimkan ke alamat email Anda.
                    </p>
                @endif
            @endif
        </div>

        <!-- Tombol Aksi Simpan -->
        <div class="flex items-center gap-4 pt-2">
            <button 
                type="submit" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-105 active:scale-95 border border-emerald-400/30"
            >
                <i class="fa-solid fa-floppy-disk"></i>
                <span>Simpan Perubahan</span>
            </button>

            @if (session('status') === 'profile-updated')
                <div
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 3000)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200 shadow-xs"
                >
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Informasi profil berhasil diperbarui!</span>
                </div>
            @endif
        </div>
    </form>
</section>
