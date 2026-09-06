@php
    $isGuru = Auth::user() && Auth::user()->role === 'guru';
    $layout = $isGuru ? 'premium-layout' : 'siswa-layout';
@endphp

<x-dynamic-component :component="$layout">
    <div class="space-y-8 max-w-6xl mx-auto pb-12">
        
        <!-- Flash Notification Alert -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <p class="text-xs sm:text-sm font-semibold">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start gap-3 shadow-xs">
                <div class="w-8 h-8 rounded-xl bg-rose-100 text-rose-700 flex items-center justify-center font-bold shrink-0 mt-0.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="text-xs">
                    <p class="font-bold">Gagal memperbarui foto:</p>
                    <ul class="list-disc list-inside mt-1 space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <!-- Hero Header Pengembang -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#008546] text-white p-6 sm:p-8 rounded-3xl shadow-lg relative overflow-hidden">
            <!-- Background Glow Decor -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold mb-3.5 backdrop-blur-xs">
                    <i class="fa-solid fa-graduation-cap text-emerald-400"></i>
                    <span>{{ $developer->fakultas }} • {{ $developer->institusi }}</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                    Profil Pengembang Media Pembelajaran
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-2.5 leading-relaxed">
                    Sistem Pembelajaran Digital <strong>{{ $developer->produk }}</strong> ini dikembangkan sebagai karya inovasi media pembelajaran vokasi untuk mendukung pemahaman peserta didik Teknik Komputer dan Jaringan di SMK Negeri 1 Kinali.
                </p>
            </div>
        </div>

        <!-- Profil & Biodata Pengembang (2-Column Grid) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Kartu Identitas Pengembang (col-span-4) -->
            <div class="lg:col-span-4 space-y-6">
                
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-7 text-center space-y-5">
                    
                    <!-- Foto Resmi Pasfoto Pengembang (Rasio Formal 3:4 Elegan) -->
                    <div class="space-y-3">
                        <div class="relative mx-auto w-36 h-48 rounded-2xl overflow-hidden shadow-md border-4 border-white ring-2 ring-slate-200/80 bg-slate-100 group transition-all">
                            @if($developer->foto_url)
                                <img src="{{ $developer->foto_url }}" alt="{{ $developer->nama }}" class="w-full h-full object-cover object-top transition-transform duration-300 group-hover:scale-105">
                            @else
                                <div class="w-full h-full bg-gradient-to-tr from-[#008546] to-emerald-400 text-white flex flex-col items-center justify-center font-extrabold shadow-inner p-4 text-center">
                                    <div class="w-16 h-16 rounded-2xl bg-white/20 flex items-center justify-center text-2xl font-black mb-2 border border-white/30">
                                        RP
                                    </div>
                                    <span class="text-xs font-bold text-white tracking-tight">Rudi Putra</span>
                                    <span class="text-[10px] text-emerald-100 font-medium">Pengembang</span>
                                </div>
                            @endif
                        </div>

                        <!-- Status Badge Pengembang (Tidak Menutupi Dasi/Baju) -->
                        <div class="flex justify-center">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-bold bg-slate-900 text-emerald-400 shadow-xs">
                                <i class="fa-solid fa-code text-[10px]"></i>
                                <span>Pengembang Media RAG</span>
                            </span>
                        </div>
                    </div>

                    <!-- Identitas Singkat -->
                    <div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">{{ $developer->nama }}</h2>
                        <p class="text-xs font-semibold text-[#008546] mt-0.5">Peneliti & Pengembang Media</p>
                        <p class="text-[11px] text-slate-400 mt-1">NIM: {{ $developer->nim }}</p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex flex-wrap justify-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                            {{ $developer->prodi }}
                        </span>
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                            {{ $developer->fakultas }}
                        </span>
                    </div>

                    <!-- Khusus Guru: Panel Kontrol Upload Foto Inline (Tanpa Modal Penuh Layar) -->
                    @if($isGuru)
                        <div class="pt-4 border-t border-slate-100 space-y-3" x-data="{
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
                                <span class="text-[11px] font-bold text-slate-600 flex items-center gap-1.5">
                                    <i class="fa-solid fa-user-shield text-emerald-600"></i> Kelola Pasfoto
                                </span>
                                <button 
                                    type="button" 
                                    @click="showForm = !showForm" 
                                    class="text-xs font-bold text-[#008546] hover:text-[#00703c] transition-colors flex items-center gap-1"
                                >
                                    <i class="fa-solid" :class="showForm ? 'fa-xmark' : 'fa-pen-to-square'"></i>
                                    <span x-text="showForm ? 'Tutup' : '{{ $developer->foto ? 'Ganti Foto' : 'Upload Foto' }}'"></span>
                                </button>
                            </div>

                            <!-- Inline Form (Ramping & Sesuai Ukuran Kartu) -->
                            <div 
                                x-show="showForm" 
                                x-cloak
                                class="p-4 rounded-2xl bg-slate-50 border border-slate-200 text-left space-y-3 shadow-inner"
                            >
                                <form method="POST" action="{{ route('pengembang.foto.update') }}" enctype="multipart/form-data" class="space-y-3">
                                    @csrf

                                    <!-- Pratinjau Pasfoto -->
                                    <div class="text-center space-y-2">
                                        <div class="mx-auto w-24 h-32 rounded-xl overflow-hidden border-2 border-dashed border-emerald-400 bg-white flex items-center justify-center shadow-xs">
                                            <template x-if="previewUrl">
                                                <img :src="previewUrl" alt="Pratinjau" class="w-full h-full object-cover object-top">
                                            </template>
                                            <template x-if="!previewUrl">
                                                <div class="p-2 text-center text-slate-400 space-y-1">
                                                    <i class="fa-solid fa-image text-xl text-slate-300"></i>
                                                    <p class="text-[9px] leading-tight">Pilih berkas pasfoto</p>
                                                </div>
                                            </template>
                                        </div>
                                        <p class="text-[10px] text-slate-500">
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
                                            class="w-full text-[11px] text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-emerald-100 file:text-[#008546] hover:file:bg-emerald-200 cursor-pointer border border-slate-200 rounded-xl p-1 bg-white"
                                            @change="onFileSelected($event)"
                                        >
                                    </div>

                                    <div class="flex items-center gap-2 pt-1">
                                        <button 
                                            type="button" 
                                            @click="cancelUpload()" 
                                            class="flex-1 py-2 bg-white hover:bg-slate-100 text-slate-600 font-bold text-xs rounded-xl border border-slate-200 transition-colors text-center"
                                        >
                                            Batal
                                        </button>
                                        <button 
                                            type="submit" 
                                            class="flex-1 py-2 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-xs rounded-xl shadow-xs transition-all hover:scale-105 active:scale-95 text-center"
                                        >
                                            Simpan Foto
                                        </button>
                                    </div>
                                </form>

                                @if($developer->foto)
                                    <div class="pt-2 border-t border-slate-200/80">
                                        <form method="POST" action="{{ route('pengembang.foto.destroy') }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus foto pengembang dan kembali ke avatar default?');">
                                            @csrf
                                            @method('DELETE')
                                            <button 
                                                type="submit" 
                                                class="w-full py-1.5 text-[11px] text-rose-600 hover:text-rose-800 hover:bg-rose-50 font-bold rounded-lg transition-colors flex items-center justify-center gap-1.5"
                                            >
                                                <i class="fa-solid fa-trash-can text-[10px]"></i>
                                                <span>Hapus Foto (Reset ke Default)</span>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <div class="pt-3 border-t border-slate-100">
                        <a href="mailto:{{ $developer->email }}" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-xs rounded-xl shadow-md transition-all hover:scale-[1.02] active:scale-[0.98]">
                            <i class="fa-solid fa-envelope"></i>
                            <span>Hubungi Pengembang</span>
                        </a>
                    </div>
                </div>

                <!-- Kartu Institusi Pendidikan -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-base shrink-0 border border-blue-200">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-900">Institusi Peneliti</h4>
                            <p class="text-[11px] text-slate-500">{{ $developer->institusi }}</p>
                        </div>
                    </div>
                    <p class="text-[11px] text-slate-600 leading-relaxed pt-1">
                        Dikembangkan di bawah naungan Program Studi {{ $developer->prodi }}, Fakultas {{ $developer->fakultas }} {{ $developer->institusi }}, bekerja sama dengan SMK Negeri 1 Kinali.
                    </p>
                </div>

            </div>

            <!-- Right: Rincian Lengkap Biodata & Produk (col-span-8) -->
            <div class="lg:col-span-8 space-y-6">
                
                <!-- Kartu Biodata Lengkap Pengembang -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-8 space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#008546] flex items-center justify-center text-base border border-emerald-200">
                                <i class="fa-solid fa-id-card-clip"></i>
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900">Biodata Lengkap Pengembang</h3>
                                <p class="text-xs text-slate-400">Informasi akademik dan kontak resmi</p>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                            <i class="fa-solid fa-check"></i> Terverifikasi
                        </span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        
                        <!-- Nama Lengkap -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Nama Lengkap
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-user text-[#008546] text-xs"></i>
                                <span>{{ $developer->nama }}</span>
                            </p>
                        </div>

                        <!-- NIM -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Nomor Induk Mahasiswa (NIM)
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-id-badge text-blue-600 text-xs"></i>
                                <span>{{ $developer->nim }}</span>
                            </p>
                        </div>

                        <!-- Program Studi -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Program Studi
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-book-open text-purple-600 text-xs"></i>
                                <span>{{ $developer->prodi }}</span>
                            </p>
                        </div>

                        <!-- Fakultas -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Fakultas
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-graduation-cap text-amber-600 text-xs"></i>
                                <span>{{ $developer->fakultas }}</span>
                            </p>
                        </div>

                        <!-- Institusi -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1 sm:col-span-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Institusi Perguruan Tinggi
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-university text-emerald-600 text-xs"></i>
                                <span>{{ $developer->institusi }}</span>
                            </p>
                        </div>

                        <!-- Produk -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1 sm:col-span-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Produk Penelitian & Pengembangan
                            </span>
                            <p class="text-sm font-bold text-emerald-700 flex items-center gap-2">
                                <i class="fa-solid fa-laptop-code text-emerald-600 text-xs"></i>
                                <span>{{ $developer->produk }}</span>
                            </p>
                        </div>

                        <!-- Kontak Email -->
                        <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-1 sm:col-span-2">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">
                                Alamat Email Kontak
                            </span>
                            <p class="text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-envelope text-rose-500 text-xs"></i>
                                <a href="mailto:{{ $developer->email }}" class="text-blue-600 hover:underline">
                                    {{ $developer->email }}
                                </a>
                            </p>
                        </div>

                    </div>
                </div>

                <!-- Kartu Deskripsi & Karakteristik Produk Pembelajaran -->
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-8 space-y-5">
                    <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center text-base border border-purple-200">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Karakteristik & Fitur Inovasi Produk</h3>
                            <p class="text-xs text-slate-400">Arsitektur pembelajaran digital terpadu</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-xs">
                        <div class="p-4 rounded-2xl bg-emerald-50/50 border border-emerald-200/60 space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-emerald-800">
                                <i class="fa-solid fa-robot text-emerald-600"></i>
                                <span>AI Chatbot RAG</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Asisten cerdas berbasis Retrieval-Augmented Generation yang menjawab pertanyaan berdasarkan dokumen modul resmi tanpa halusinasi.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-blue-50/50 border border-blue-200/60 space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-blue-800">
                                <i class="fa-solid fa-book-bookmark text-blue-600"></i>
                                <span>Kegiatan Belajar (KB)</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Terstruktur per unit KB 1, KB 2, dan KB 3 yang memuat Tujuan Pembelajaran (TP), dokumen modul asli, video praktikum, dan kuis online.
                            </p>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50/50 border border-amber-200/60 space-y-1.5">
                            <div class="flex items-center gap-2 font-bold text-amber-800">
                                <i class="fa-solid fa-chalkboard-user text-amber-600"></i>
                                <span>Sinkronisasi Vokasi</span>
                            </div>
                            <p class="text-slate-600 text-[11px] leading-relaxed">
                                Disesuaikan secara langsung dengan Capaian Pembelajaran Kurikulum Merdeka pada Konsentrasi Keahlian TKJ SMK Negeri 1 Kinali.
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>
</x-dynamic-component>
