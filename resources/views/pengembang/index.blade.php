@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<x-premium-layout>
    <div class="space-y-8 max-w-6xl mx-auto pb-12">

        <!-- Flash Notification Alert -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50/90 border border-emerald-200/90 text-emerald-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 text-emerald-800 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-circle-check text-sm"></i>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center font-bold shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                </div>
                <div class="text-xs">
                    <p class="font-bold">Gagal memperbarui foto:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-rose-800">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Academic Monograph Header -->
        <div class="relative bg-stone-900 text-white rounded-2xl border border-stone-800 p-7 sm:p-9 shadow-sm overflow-hidden">
            <!-- Subtle Architectural Accent Line -->
            <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-emerald-600 via-[#008546] to-stone-700"></div>

            <div class="relative z-10 max-w-3xl space-y-3">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-stone-800 text-stone-300 border border-stone-700/80 text-xs font-medium tracking-wide">
                    <i class="fa-solid fa-graduation-cap text-emerald-400 text-xs"></i>
                    <span>{{ $developer->fakultas }} • {{ $developer->institusi }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white leading-tight">
                    Profil Pengembang Media Pembelajaran
                </h1>
                <p class="text-stone-300 text-xs sm:text-sm leading-relaxed max-w-2xl font-normal">
                    Sistem Pembelajaran Digital <strong>{{ $developer->produk }}</strong> ini dikembangkan sebagai karya inovasi media pembelajaran vokasi untuk mendukung pemahaman peserta didik Teknik Komputer dan Jaringan di SMK Negeri 1 Kinali.
                </p>
            </div>
        </div>

        <!-- Profil & Biodata Pengembang (2-Column Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

            <!-- Left: Kartu Identitas & Portrait (col-span-4) -->
            <div class="lg:col-span-4 space-y-6">

                <div class="bg-white rounded-2xl border border-stone-200/90 shadow-xs p-6 sm:p-7 text-center space-y-5">

                    <!-- Foto Resmi Pasfoto Pengembang (Formal Passe-Partout Framing) -->
                    <div class="space-y-3">
                        <div class="relative mx-auto w-36 h-48 rounded-xl overflow-hidden shadow-sm border border-stone-200 bg-stone-100 ring-4 ring-stone-50 group transition-all">
                            @if($developer->foto_url)
                                <img src="{{ $developer->foto_url }}" alt="{{ $developer->nama }}" class="w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-102">
                            @else
                                <div class="w-full h-full bg-gradient-to-b from-stone-800 to-stone-900 text-white flex flex-col items-center justify-center p-4 text-center">
                                    <div class="w-14 h-14 rounded-xl bg-stone-700/80 border border-stone-600 flex items-center justify-center text-xl font-bold mb-2">
                                        RP
                                    </div>
                                    <span class="text-xs font-semibold text-stone-200 tracking-tight">Rudi Putra</span>
                                    <span class="text-[10px] text-stone-400 font-medium">Pengembang</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status Badge Pengembang Formal -->
                        <div class="flex justify-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-[11px] font-semibold bg-stone-100 text-stone-700 border border-stone-200/80">
                                <i class="fa-solid fa-code text-[10px] text-emerald-700"></i>
                                <span>Pengembang Media RAG</span>
                            </span>
                        </div>
                    </div>

                    <!-- Identitas Singkat -->
                    <div class="space-y-1">
                        <h2 class="text-xl font-bold text-stone-900 tracking-tight">{{ $developer->nama }}</h2>
                        <p class="text-xs font-semibold text-emerald-800 tracking-wide uppercase">Peneliti & Pengembang Media</p>
                        <p class="text-xs text-stone-500 font-mono">NIM: {{ $developer->nim }}</p>
                    </div>

                    <div class="pt-3 border-t border-stone-100 flex flex-wrap justify-center gap-1.5">
                        <span class="px-3 py-1 rounded-md text-[11px] font-medium bg-stone-50 text-stone-700 border border-stone-200/80">
                            {{ $developer->prodi }}
                        </span>
                        <span class="px-3 py-1 rounded-md text-[11px] font-medium bg-stone-50 text-stone-700 border border-stone-200/80">
                            {{ $developer->fakultas }}
                        </span>
                    </div>

                    <!-- Khusus Guru: Panel Kontrol Upload Foto Inline -->
                    @if(auth()->user()?->isGuru())
                        <div class="pt-4 border-t border-stone-100 space-y-3" x-data="{
                            showForm: false,
                            previewUrl: null,
                            onFileSelected(e) {
                                const file = e.target.files[0];
                                if (file) {
                                    this.previewUrl = URL.createObjectURL(file);
                                }
                            },
                            cancelUpload() {
                                this.showForm = false;
                                this.previewUrl = null;
                                if (this.$refs.fileInput) {
                                    this.$refs.fileInput.value = '';
                                }
                            }
                        }">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-stone-700 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-shield text-emerald-700"></i> Kelola Pasfoto
                                </span>
                                <button
                                    type="button"
                                    @click="showForm = !showForm"
                                    class="text-xs font-semibold text-[#008546] hover:text-[#00703c] transition-colors flex items-center gap-1"
                                >
                                    <i class="fa-solid" :class="showForm ? 'fa-xmark' : 'fa-pen-to-square'"></i>
                                    <span x-text="showForm ? 'Tutup' : '{{ $developer->foto ? 'Ganti Foto' : 'Upload Foto' }}'"></span>
                                </button>
                            </div>

                            <!-- Inline Form Kontrol -->
                            <div
                                x-show="showForm"
                                x-cloak
                                class="p-4 rounded-xl bg-stone-50 border border-stone-200 text-left space-y-3"
                            >
                                <form method="POST" action="{{ route('pengembang.foto.update') }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf

                                    <!-- Pratinjau Pasfoto -->
                                    <div class="text-center space-y-2">
                                        <div class="mx-auto w-24 h-32 rounded-lg overflow-hidden border border-dashed border-stone-400 bg-white flex items-center justify-center shadow-2xs">
                                            <template x-if="previewUrl">
                                                <img :src="previewUrl" alt="Pratinjau" class="w-full h-full object-cover object-top">
                                            </template>
                                            <template x-if="!previewUrl">
                                                <div class="p-2 text-center text-stone-400 space-y-1">
                                                    <i class="fa-solid fa-image text-xl text-stone-300"></i>
                                                    <p class="text-[9px] leading-tight">Pilih berkas pasfoto</p>
                                                </div>
                                            </template>
                                        </div>
                                        <p class="text-[10px] text-stone-500">
                                            Format pasfoto resmi (3:4), JPG/PNG/WEBP maks 2 MB.
                                        </p>
                                    </div>

                                    <div>
                                        <input
                                            type="file"
                                            name="foto"
                                            required
                                            x-ref="fileInput"
                                            accept="image/jpeg,image/png,image/jpg,image/webp"
                                            class="w-full text-xs text-stone-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-stone-200 file:text-stone-800 hover:file:bg-stone-300 cursor-pointer border border-stone-300 rounded-lg p-1 bg-white"
                                            @change="onFileSelected($event)"
                                        >
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <button
                                            type="button"
                                            @click="cancelUpload()"
                                            class="flex-1 py-2 bg-white hover:bg-stone-100 text-stone-700 font-semibold text-xs rounded-lg border border-stone-200 transition-colors text-center"
                                        >
                                            Batal
                                        </button>
                                        <button
                                            type="submit"
                                            class="flex-1 py-2 bg-[#008546] hover:bg-[#00703c] text-white font-semibold text-xs rounded-lg shadow-xs transition-colors text-center"
                                        >
                                            Simpan Foto
                                        </button>
                                    </div>
                                </form>

                                @if($developer->foto)
                                    <div class="pt-2 border-t border-stone-200">
                                        <form method="POST" action="{{ route('pengembang.foto.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto pengembang dan kembali ke avatar default?');">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="w-full py-1.5 text-xs text-rose-700 hover:text-rose-900 hover:bg-rose-50 font-semibold rounded-md transition-colors flex items-center justify-center gap-1.5"
                                            >
                                                <i class="fa-solid fa-trash-can text-[11px]"></i>
                                                <span>Hapus Foto (Reset ke Default)</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-stone-100">
                        <a href="mailto:{{ $developer->email }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-stone-900 hover:bg-stone-800 text-white font-semibold text-xs rounded-xl shadow-xs transition-colors">
                            <i class="fa-solid fa-envelope text-xs"></i>
                            <span>Hubungi Pengembang</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu Institusi Pendidikan -->
                <div class="bg-white rounded-2xl border border-stone-200/90 shadow-xs p-5 sm:p-6 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg bg-stone-100 text-stone-700 flex items-center justify-center text-sm shrink-0 border border-stone-200">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-stone-900">Institusi Peneliti</h4>
                            <p class="text-xs text-stone-500">{{ $developer->institusi }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-stone-600 leading-relaxed pt-1">
                        Dikembangkan di bawah naungan Program Studi {{ $developer->prodi }}, Fakultas {{ $developer->fakultas }} {{ $developer->institusi }}, bekerja sama dengan SMK Negeri 1 Kinali.
                    </p>
                </div>

            </div>

            <!-- Right: Rincian Lengkap Biodata & Produk (col-span-8) -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Lembar Biodata Resmi / Academic Dossier -->
                <div class="bg-white rounded-2xl border border-stone-200/90 shadow-xs overflow-hidden">

                    <!-- Dossier Header Bar -->
                    <div class="px-6 sm:px-8 py-5 border-b border-stone-100 bg-stone-50/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight">Biodata Lengkap Pengembang</h3>
                            <p class="text-xs text-stone-500 mt-0.5">Informasi akademik dan kontak resmi</p>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-md text-xs font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200/80">
                                <i class="fa-solid fa-circle-check text-emerald-600 text-xs"></i>
                                <span>Terverifikasi</span>
                            </span>
                        </div>
                    </div>

                    <!-- Dossier Content: Human-Crafted Definition Specification -->
                    <div class="p-6 sm:p-8 space-y-6">

                        <!-- Bagian Identitas Akademik -->
                        <div class="space-y-1">
                            <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider pb-2 border-b border-stone-100">
                                Data Akademik
                            </div>

                            <dl class="divide-y divide-stone-100 text-xs sm:text-sm">

                                <!-- Nama Lengkap -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Nama Lengkap</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 font-bold text-stone-900 text-sm sm:text-base">
                                        {{ $developer->nama }}
                                    </dd>
                                </div>

                                <!-- NIM -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Nomor Induk Mahasiswa (NIM)</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 font-semibold text-stone-900 font-mono tracking-wide">
                                        {{ $developer->nim }}
                                    </dd>
                                </div>

                                <!-- Program Studi -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Program Studi</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 font-semibold text-stone-900">
                                        {{ $developer->prodi }}
                                    </dd>
                                </div>

                                <!-- Fakultas -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Fakultas</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 font-semibold text-stone-900">
                                        {{ $developer->fakultas }}
                                    </dd>
                                </div>

                                <!-- Institusi Perguruan Tinggi -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Institusi Perguruan Tinggi</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2 font-semibold text-stone-900">
                                        {{ $developer->institusi }}
                                    </dd>
                                </div>

                            </dl>
                        </div>

                        <!-- Bagian Karya & Kontak -->
                        <div class="space-y-1 pt-2">
                            <div class="text-[11px] font-bold text-stone-400 uppercase tracking-wider pb-2 border-b border-stone-100">
                                Karya & Komunikasi
                            </div>

                            <dl class="divide-y divide-stone-100 text-xs sm:text-sm">

                                <!-- Produk -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-center">
                                    <dt class="font-medium text-stone-500">Produk Penelitian & Pengembangan</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-50/70 border border-emerald-200/80 text-emerald-900 font-semibold text-xs sm:text-sm">
                                            <i class="fa-solid fa-code text-xs text-emerald-700"></i>
                                            <span>{{ $developer->produk }}</span>
                                        </div>
                                    </dd>
                                </div>

                                <!-- Kontak Email -->
                                <div class="py-3.5 sm:grid sm:grid-cols-3 sm:gap-4 sm:items-baseline">
                                    <dt class="font-medium text-stone-500">Alamat Email Kontak</dt>
                                    <dd class="mt-1 sm:mt-0 sm:col-span-2">
                                        <a href="mailto:{{ $developer->email }}" class="inline-flex items-center gap-2 font-mono text-xs sm:text-sm font-semibold text-stone-900 hover:text-emerald-700 underline decoration-stone-300 underline-offset-4 transition-colors">
                                            <i class="fa-regular fa-envelope text-stone-400 text-xs"></i>
                                            <span>{{ $developer->email }}</span>
                                        </a>
                                    </dd>
                                </div>

                            </dl>
                        </div>

                    </div>
                </div>

                <!-- Kartu Deskripsi & Karakteristik Produk Pembelajaran (Three Architectural Pillars) -->
                <div class="bg-white rounded-2xl border border-stone-200/90 shadow-xs p-6 sm:p-8 space-y-5">
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100">
                        <div>
                            <h3 class="text-base sm:text-lg font-bold text-stone-900 tracking-tight">Karakteristik & Fitur Inovasi Produk</h3>
                            <p class="text-xs text-stone-500 mt-0.5">Arsitektur pembelajaran digital terpadu</p>
                        </div>
                        <span class="text-xs font-mono font-medium text-stone-400 hidden sm:inline-block">
                            Spesifikasi Sistem
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">

                        <!-- Pilar 1: AI Chatbot RAG -->
                        <div class="p-4 sm:p-5 rounded-xl border border-stone-200/80 bg-stone-50/50 space-y-2.5 transition-colors hover:border-stone-300">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-mono font-bold text-stone-400">01</span>
                                <div class="w-7 h-7 rounded-md bg-stone-200/70 text-stone-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-robot"></i>
                                </div>
                            </div>
                            <h4 class="text-sm font-bold text-stone-900">AI Chatbot RAG</h4>
                            <p class="text-stone-600 text-xs leading-relaxed">
                                Asisten cerdas berbasis Retrieval-Augmented Generation yang menjawab pertanyaan berdasarkan dokumen modul resmi tanpa halusinasi.
                            </p>
                        </div>

                        <!-- Pilar 2: Kegiatan Belajar (KB) -->
                        <div class="p-4 sm:p-5 rounded-xl border border-stone-200/80 bg-stone-50/50 space-y-2.5 transition-colors hover:border-stone-300">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-mono font-bold text-stone-400">02</span>
                                <div class="w-7 h-7 rounded-md bg-stone-200/70 text-stone-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-book-bookmark"></i>
                                </div>
                            </div>
                            <h4 class="text-sm font-bold text-stone-900">Kegiatan Belajar (KB)</h4>
                            <p class="text-stone-600 text-xs leading-relaxed">
                                Terstruktur per unit Kegiatan Belajar (KB) sesuai kebutuhan sekolah, memuat Tujuan Pembelajaran (TP), dokumen modul, video praktikum, dan kuis online.
                            </p>
                        </div>

                        <!-- Pilar 3: Sinkronisasi Vokasi -->
                        <div class="p-4 sm:p-5 rounded-xl border border-stone-200/80 bg-stone-50/50 space-y-2.5 transition-colors hover:border-stone-300">
                            <div class="flex items-center justify-between">
                                <span class="text-[11px] font-mono font-bold text-stone-400">03</span>
                                <div class="w-7 h-7 rounded-md bg-stone-200/70 text-stone-700 flex items-center justify-center text-xs">
                                    <i class="fa-solid fa-chalkboard-user"></i>
                                </div>
                            </div>
                            <h4 class="text-sm font-bold text-stone-900">Sinkronisasi Vokasi</h4>
                            <p class="text-stone-600 text-xs leading-relaxed">
                                Disesuaikan secara langsung dengan Capaian Pembelajaran Kurikulum Merdeka pada Konsentrasi Keahlian TKJ SMK Negeri 1 Kinali.
                            </p>
                        </div>

                    </div>
                </div>

            </div>

        </div>

    </div>
</x-premium-layout>
