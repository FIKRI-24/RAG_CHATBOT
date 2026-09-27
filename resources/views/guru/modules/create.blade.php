@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<x-premium-layout>
    <div class="space-y-6 max-w-5xl mx-auto pb-16">
        
        <!-- Header Section (Jelas, Terbaca & Kontras Tinggi) -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-300 shadow-sm">
            <div class="flex items-center gap-4">
                <a href="{{ route('guru.modules.index') }}" 
                   class="w-12 h-12 rounded-2xl bg-slate-100 hover:bg-emerald-50 text-slate-800 hover:text-emerald-800 border-2 border-slate-300 flex items-center justify-center transition-colors shadow-xs flex-shrink-0" 
                   title="Kembali ke Daftar Modul">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-black text-slate-900 tracking-tight flex items-center gap-2.5">
                        <i class="fa-solid fa-cloud-arrow-up text-emerald-700"></i> Upload Modul & Kegiatan Belajar Baru
                    </h1>
                    <p class="text-sm font-medium text-slate-700 mt-1">
                        Unggah materi pembelajaran agar siswa dapat membaca modul dan bertanya langsung kepada Asisten AI.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold bg-emerald-50 text-emerald-900 border-2 border-emerald-400 shadow-2xs">
                    <i class="fa-solid fa-circle-check text-emerald-700 text-sm"></i>
                    <span>Sistem AI Siap Membaca Modul</span>
                </span>
            </div>
        </div>

        <!-- Peringatan Error (Jika Ada Form yang Belum Lengkap) -->
        @if($errors->any())
            <div class="bg-rose-50 border-2 border-rose-300 text-rose-900 p-5 rounded-2xl space-y-2 shadow-sm">
                <div class="font-extrabold flex items-center gap-2 text-base text-rose-800">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <span>Mohon lengkapi bagian formulir berikut:</span>
                </div>
                <ul class="list-disc pl-6 space-y-1 text-sm font-semibold text-rose-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulir Upload Modul (Alur Langkah Berurutan & Mudah Dipahami) -->
        <form id="uploadForm" method="POST" action="{{ route('guru.modules.store') }}" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <!-- ========================================== -->
            <!-- LANGKAH 1: UPLOAD BERKAS MATERI (WAJIB)     -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-sm p-6 sm:p-8 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-700 text-white font-black text-sm flex items-center justify-center">1</span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-slate-900">
                            Berkas Materi Pembelajaran
                        </h2>
                    </div>
                    <span class="self-start sm:self-auto text-xs font-black uppercase tracking-wider text-rose-800 bg-rose-100 border border-rose-300 px-3 py-1 rounded-lg">
                        * Wajib Diunggah
                    </span>
                </div>

                <div>
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">
                        Pilih Dokumen Modul (PDF atau Word) <span class="text-rose-600">*</span>
                    </label>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 mb-3">
                        Pastikan berkas berisi materi bacaan yang jelas agar sistem AI dapat mempelajari dan menjawab pertanyaan siswa dengan tepat.
                    </p>

                    <!-- Area Dropzone Berkas -->
                    <div id="dropZone" class="relative border-3 border-dashed border-slate-400 hover:border-emerald-700 bg-slate-50 hover:bg-emerald-50/40 rounded-3xl p-8 sm:p-10 text-center transition-all cursor-pointer group">
                        <input type="file" id="file" name="file" accept=".pdf,.docx" required 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                               onchange="handleFileSelect(this)">
                        
                        <!-- Tampilan 1: Belum Memilih File -->
                        <div id="uploadPrompt" class="space-y-4">
                            <div class="w-16 h-16 rounded-2xl bg-white shadow-md text-emerald-700 flex items-center justify-center mx-auto text-3xl border-2 border-slate-200 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-cloud-arrow-up"></i>
                            </div>
                            <div>
                                <span class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-700 hover:bg-emerald-800 text-white font-extrabold text-sm sm:text-base rounded-2xl shadow-sm transition-colors mb-2">
                                    <i class="fa-solid fa-folder-open"></i> Klik Untuk Memilih File dari Komputer
                                </span>
                                <p class="text-sm font-semibold text-slate-700 mt-2">
                                    atau seret (drag) file dokumen Anda dan lepaskan di area kotak ini
                                </p>
                            </div>
                            <div class="pt-2 border-t border-slate-200/80 flex flex-wrap items-center justify-center gap-2">
                                <span class="text-xs font-bold bg-white border-2 border-slate-300 text-slate-800 px-3 py-1 rounded-lg">
                                    <i class="fa-solid fa-file-pdf text-red-600 mr-1.5 text-sm"></i> File PDF (.pdf)
                                </span>
                                <span class="text-xs font-bold bg-white border-2 border-slate-300 text-slate-800 px-3 py-1 rounded-lg">
                                    <i class="fa-solid fa-file-word text-blue-600 mr-1.5 text-sm"></i> File Word (.docx)
                                </span>
                                <span class="text-xs font-bold bg-emerald-50 border border-emerald-300 text-emerald-900 px-3 py-1 rounded-lg">
                                    Ukuran Maksimal: 10 MB
                                </span>
                            </div>
                        </div>

                        <!-- Tampilan 2: File Sudah Terpilih (Sangat Jelas & Terbaca) -->
                        <div id="filePreview" class="hidden text-left space-y-3">
                            <div class="flex items-center gap-4 p-4 bg-white rounded-2xl border-2 border-emerald-500 shadow-md">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-2xl flex-shrink-0 border border-emerald-300">
                                    <i id="fileIcon" class="fa-solid fa-file-lines"></i>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-black bg-emerald-700 text-white px-2.5 py-0.5 rounded-md">BERKAS TERPILIH</span>
                                    </div>
                                    <p id="fileName" class="text-base sm:text-lg font-black text-slate-900 truncate mt-1"></p>
                                    <p id="fileSize" class="text-xs sm:text-sm font-bold text-slate-600 mt-0.5"></p>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm font-bold text-emerald-800 text-center flex items-center justify-center gap-1.5">
                                <i class="fa-solid fa-rotate text-sm"></i> Klik kembali kotak ini jika Bapak/Ibu ingin mengganti file yang dipilih
                            </p>
                        </div>
                    </div>
                    <x-input-error class="mt-2 text-sm font-bold text-rose-700" :messages="$errors->get('file')" />
                </div>
            </div>

            <!-- ========================================== -->
            <!-- LANGKAH 2: IDENTITAS MODUL (WAJIB)         -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-700 text-white font-black text-sm flex items-center justify-center">2</span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-slate-900">
                            Identitas Modul & Kegiatan Belajar
                        </h2>
                    </div>
                    <span class="self-start sm:self-auto text-xs font-black uppercase tracking-wider text-rose-800 bg-rose-100 border border-rose-300 px-3 py-1 rounded-lg">
                        * Wajib Diisi
                    </span>
                </div>

                <!-- Judul Modul -->
                <div>
                    <label for="judul" class="flex items-center gap-2 text-sm sm:text-base font-bold text-slate-900 mb-1">
                        <i class="fa-solid fa-book-open text-emerald-700 text-base"></i>
                        <span>Judul Modul / Materi Pembelajaran <span class="text-rose-600">*</span></span>
                    </label>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Tuliskan judul pokok bahasan materi secara lengkap dan jelas.
                    </p>
                    <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required 
                           placeholder="Contoh: Konfigurasi Virtual LAN (VLAN) pada Switch Manageable"
                           class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all placeholder:text-slate-400 placeholder:font-normal">
                    <x-input-error class="mt-1.5 text-sm font-bold text-rose-700" :messages="$errors->get('judul')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Mata Pelajaran -->
                    <div>
                        <label for="mapel" class="flex items-center gap-2 text-sm sm:text-base font-bold text-slate-900 mb-1">
                            <i class="fa-solid fa-graduation-cap text-emerald-700 text-base"></i>
                            <span>Mata Pelajaran / Konsentrasi Keahlian <span class="text-rose-600">*</span></span>
                        </label>
                        <p class="text-xs sm:text-sm font-medium text-slate-700 mb-2">
                            Pilih rekomendasi yang tersedia atau ketik nama mata pelajaran.
                        </p>
                        <input type="text" id="mapel" name="mapel" list="mapel-suggestions" value="{{ old('mapel') }}" required 
                               placeholder="Pilih atau ketik nama mata pelajaran..."
                               class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all placeholder:text-slate-400 placeholder:font-normal">
                        <datalist id="mapel-suggestions">
                            <option value="Administrasi Infrastruktur Jaringan">
                            <option value="Administrasi Sistem Jaringan">
                            <option value="Teknologi Jaringan Berbasis Luas (WAN)">
                            <option value="Teknologi Layanan Jaringan">
                            <option value="Dasar-Dasar Teknik Komputer & Jaringan">
                            <option value="Keamanan Jaringan">
                            <option value="Subnetting & Routing">
                        </datalist>
                        <x-input-error class="mt-1.5 text-sm font-bold text-rose-700" :messages="$errors->get('mapel')" />
                    </div>

                    <!-- Nomor Kegiatan Belajar (KB) -->
                    <div>
                        <label for="kb_nomor" class="flex items-center gap-2 text-sm sm:text-base font-bold text-slate-900 mb-1">
                            <i class="fa-solid fa-layer-group text-emerald-700 text-base"></i>
                            <span>Nama atau Nomor Kegiatan Belajar (KB) <span class="text-rose-600">*</span></span>
                        </label>
                        <p class="text-xs sm:text-sm font-medium text-slate-700 mb-2">
                            Tentukan urutan belajar siswa (contoh: <strong>KB 1</strong>, <strong>KB 2</strong>, atau <strong>Topik 1</strong>).
                        </p>
                        <input id="kb_nomor" name="kb_nomor" type="text" required maxlength="255" value="{{ old('kb_nomor') }}" 
                               placeholder="Contoh: KB 1, KB 2, atau Unit 1"
                               class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all placeholder:text-slate-400 placeholder:font-normal">
                        <x-input-error class="mt-1.5 text-sm font-bold text-rose-700" :messages="$errors->get('kb_nomor')" />
                    </div>
                </div>
            </div>

            <!-- ========================================== -->
            <!-- LANGKAH 3: PENGAYAAN & MEDIA (OPSIONAL)    -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-sm p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-blue-700 text-white font-black text-sm flex items-center justify-center">3</span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-slate-900">
                            Pengayaan & Media Pendukung
                        </h2>
                    </div>
                    <span class="self-start sm:self-auto text-xs font-bold uppercase tracking-wider text-blue-900 bg-blue-50 border border-blue-200 px-3 py-1 rounded-lg">
                        Opsional (Boleh Dikosongkan)
                    </span>
                </div>

                <!-- Tujuan Pembelajaran (TP) -->
                <div>
                    <label for="tp" class="block text-sm sm:text-base font-bold text-slate-900 mb-1">
                        Tujuan Pembelajaran (TP)
                    </label>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Tuliskan target kompetensi yang diharapkan dikuasai siswa. TP ini akan muncul di kartu pembelajaran siswa.
                    </p>
                    <textarea id="tp" name="tp" rows="3" 
                              placeholder="Contoh:&#10;1. Siswa mampu memahami konsep dasar VLAN.&#10;2. Siswa mampu mengkonfigurasi port switch mode access."
                              class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-medium rounded-2xl p-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all leading-relaxed placeholder:text-slate-400">{{ old('tp') }}</textarea>
                    <x-input-error class="mt-1.5 text-sm font-bold text-rose-700" :messages="$errors->get('tp')" />
                </div>

                <!-- Link Video & Kuis Eksternal -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Link Video YouTube -->
                    <div class="p-5 bg-slate-50 rounded-2xl border-2 border-slate-300 space-y-2">
                        <label for="video_url" class="block text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-brands fa-youtube text-red-600 text-lg"></i>
                            <span>Link Video Pembelajaran Utama (YouTube)</span>
                        </label>
                        <p class="text-xs font-medium text-slate-700">
                            Siswa dapat langsung menonton video ini di halaman modul mereka.
                        </p>
                        <input type="url" id="video_url" name="video_url" value="{{ old('video_url') }}" 
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-white border-2 border-slate-300 text-slate-900 text-sm font-semibold rounded-xl py-2.5 px-3.5 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20 transition-all placeholder:text-slate-400">
                        <x-input-error class="mt-1 text-sm font-bold text-rose-700" :messages="$errors->get('video_url')" />
                    </div>

                    <!-- Link Kuis Eksternal -->
                    <div class="p-5 bg-slate-50 rounded-2xl border-2 border-slate-300 space-y-2">
                        <label for="kuis_url" class="block text-sm font-bold text-slate-900 flex items-center gap-2">
                            <i class="fa-solid fa-pen-to-square text-emerald-700 text-lg"></i>
                            <span>Link Kuis / Evaluasi Luar (Opsional)</span>
                        </label>
                        <p class="text-xs font-medium text-slate-700">
                            Tautan Google Form, Quizizz, atau lembar kerja siswa jika ada.
                        </p>
                        <input type="url" id="kuis_url" name="kuis_url" value="{{ old('kuis_url') }}" 
                               placeholder="https://forms.gle/... atau Quizizz"
                               class="w-full bg-white border-2 border-slate-300 text-slate-900 text-sm font-semibold rounded-xl py-2.5 px-3.5 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 transition-all placeholder:text-slate-400">
                        <x-input-error class="mt-1 text-sm font-bold text-rose-700" :messages="$errors->get('kuis_url')" />
                    </div>
                </div>

                <!-- Bagian Video Tambahan (Menyatu Rapi di Langkah 3) -->
                @include('guru.modules.media-fields')
            </div>

            <!-- ========================================== -->
            <!-- LANGKAH 4: MASA AKTIF MODUL (OPSIONAL)     -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-sm p-6 sm:p-8 space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-slate-700 text-white font-black text-sm flex items-center justify-center">4</span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-slate-900">
                            Periode Aktif Modul
                        </h2>
                    </div>
                    <span class="self-start sm:self-auto text-xs font-bold text-slate-700 bg-slate-100 border border-slate-300 px-3 py-1 rounded-lg">
                        Pengaturan Waktu
                    </span>
                </div>

                <div class="max-w-md">
                    <label for="berlaku_sampai" class="block text-sm sm:text-base font-bold text-slate-900 mb-1">
                        Berlaku Sampai Tanggal
                    </label>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 mb-2">
                        Standar otomatis diatur aktif untuk 6 bulan ke depan. Setelah tanggal ini lewat, modul tetap aman dan tersimpan di arsip sekolah.
                    </p>
                    <input type="date" id="berlaku_sampai" name="berlaku_sampai" 
                           value="{{ old('berlaku_sampai', now(config('app.display_timezone'))->addMonths(6)->format('Y-m-d')) }}"
                           class="w-full sm:w-72 bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all">
                    <x-input-error class="mt-1.5 text-sm font-bold text-rose-700" :messages="$errors->get('berlaku_sampai')" />
                </div>
            </div>

            <!-- ========================================== -->
            <!-- TOMBOL AKSI SIMPAN & BATAL                 -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-md p-6 sm:p-7 flex flex-col sm:flex-row items-center justify-between gap-4">
                <a href="{{ route('guru.modules.index') }}" 
                   class="w-full sm:w-auto order-2 sm:order-1 py-4 px-8 bg-slate-100 hover:bg-slate-200 border-2 border-slate-300 text-slate-900 font-extrabold text-sm sm:text-base rounded-2xl transition-colors text-center shadow-xs">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Batal dan Kembali
                </a>

                <button type="submit" id="submitBtn" 
                        class="w-full sm:w-auto order-1 sm:order-2 py-4 px-10 bg-emerald-700 hover:bg-emerald-800 text-white font-black text-base sm:text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 cursor-pointer">
                    <i class="fa-solid fa-cloud-arrow-up text-xl"></i>
                    <span>Simpan & Upload Modul Sekarang</span>
                </button>
            </div>

        </form>

    </div>

    <!-- Script Interaktif UI Pratinjau Berkas -->
    <script>
        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const prompt = document.getElementById('uploadPrompt');
                const preview = document.getElementById('filePreview');
                const nameEl = document.getElementById('fileName');
                const sizeEl = document.getElementById('fileSize');
                const iconEl = document.getElementById('fileIcon');

                nameEl.textContent = file.name;
                
                // Format ukuran berkas
                const sizeInKb = (file.size / 1024).toFixed(1);
                if (file.size > 1024 * 1024) {
                    sizeEl.textContent = 'Ukuran berkas: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                } else {
                    sizeEl.textContent = 'Ukuran berkas: ' + sizeInKb + ' KB';
                }

                // Ikon jenis berkas
                if (file.name.toLowerCase().endsWith('.pdf')) {
                    iconEl.className = 'fa-solid fa-file-pdf text-red-600';
                } else if (file.name.toLowerCase().endsWith('.docx') || file.name.toLowerCase().endsWith('.doc')) {
                    iconEl.className = 'fa-solid fa-file-word text-blue-600';
                } else {
                    iconEl.className = 'fa-solid fa-file-lines text-emerald-700';
                }

                prompt.classList.add('hidden');
                preview.classList.remove('hidden');
            }
        }
    </script>
</x-premium-layout>
