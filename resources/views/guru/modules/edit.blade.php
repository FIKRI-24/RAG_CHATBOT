@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \App\Models\Module $module */
@endphp
<x-premium-layout>
    @if(session('error'))
        <div role="alert" class="max-w-5xl mx-auto mb-6 rounded-2xl border-2 border-rose-300 bg-rose-50 p-4 text-base font-bold text-rose-900 shadow-sm flex items-center gap-3">
            <i class="fa-solid fa-triangle-exclamation text-lg text-rose-700"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

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
                        <i class="fa-solid fa-pen-to-square text-emerald-700"></i> Edit Modul & Kegiatan Belajar
                    </h1>
                    <p class="text-sm font-medium text-slate-700 mt-1">
                        Perbarui rincian materi, Tujuan Pembelajaran (TP), link video, atau ganti berkas dokumen modul.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl text-xs sm:text-sm font-bold bg-emerald-50 text-emerald-900 border-2 border-emerald-400 shadow-2xs">
                    <i class="fa-solid fa-layer-group text-emerald-700"></i>
                    <span>Sedang Mengedit: {{ $module->kb_nomor }}</span>
                </span>
            </div>
        </div>

        <!-- Peringatan Error -->
        @if($errors->any())
            <div class="bg-rose-50 border-2 border-rose-300 text-rose-900 p-5 rounded-2xl space-y-2 shadow-sm">
                <div class="font-extrabold flex items-center gap-2 text-base text-rose-800">
                    <i class="fa-solid fa-triangle-exclamation text-lg"></i>
                    <span>Harap periksa kembali bagian berikut:</span>
                </div>
                <ul class="list-disc pl-6 space-y-1 text-sm font-semibold text-rose-800">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Formulir Edit Modul -->
        <form id="editForm" method="POST" action="{{ route('guru.modules.update', $module->id) }}" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            <!-- ========================================== -->
            <!-- LANGKAH 1: BERKAS DOKUMEN MODUL            -->
            <!-- ========================================== -->
            <div class="bg-white rounded-3xl border-2 border-slate-300 shadow-sm p-6 sm:p-8 space-y-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b-2 border-slate-200 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-8 h-8 rounded-xl bg-emerald-700 text-white font-black text-sm flex items-center justify-center">1</span>
                        <h2 class="font-extrabold text-lg sm:text-xl text-slate-900">
                            Berkas Dokumen Modul
                        </h2>
                    </div>
                    <span class="self-start sm:self-auto text-xs font-black uppercase tracking-wider text-emerald-900 bg-emerald-100 border border-emerald-300 px-3 py-1 rounded-lg">
                        Berkas Tersimpan
                    </span>
                </div>

                <!-- Info Berkas Saat Ini -->
                <div class="p-5 bg-emerald-50 rounded-2xl border-2 border-emerald-300 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div class="flex items-center gap-4 overflow-hidden">
                        <div class="w-14 h-14 rounded-2xl bg-white text-emerald-700 shadow-sm flex items-center justify-center text-2xl flex-shrink-0 border-2 border-emerald-200">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <div class="overflow-hidden">
                            <span class="text-xs font-black text-emerald-900 uppercase tracking-wider">Berkas Aktif di Sistem</span>
                            <p class="text-base sm:text-lg font-black text-slate-900 truncate mt-0.5" title="{{ basename($module->file_path) }}">
                                {{ basename($module->file_path) }}
                            </p>
                            <p class="text-xs sm:text-sm font-semibold text-emerald-800 mt-0.5">
                                <i class="fa-solid fa-check-circle mr-1"></i> Materi ini sudah aktif dipelajari oleh Asisten AI siswa
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('modules.download', $module->id) }}" 
                       class="self-start sm:self-auto px-4 py-2.5 bg-white hover:bg-emerald-100 text-emerald-900 font-extrabold text-xs sm:text-sm rounded-xl border-2 border-emerald-400 shadow-2xs transition-colors flex items-center gap-2 flex-shrink-0 cursor-pointer" 
                       title="Unduh Berkas Ini">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Berkas Ini</span>
                    </a>
                </div>

                <!-- Opsi Mengganti Berkas -->
                <div class="pt-2">
                    <label class="block text-sm sm:text-base font-bold text-slate-900 mb-1">
                        Unggah Berkas Baru Pengganti (Hanya jika ingin merevisi)
                    </label>
                    <p class="text-xs sm:text-sm font-medium text-slate-700 mb-3">
                        Kosongkan bagian ini jika berkas lama tidak perlu diubah. Jika Bapak/Ibu mengunggah berkas baru, sistem AI akan otomatis membaca ulang materi dari awal.
                    </p>

                    <div id="dropZone" class="relative border-3 border-dashed border-slate-400 hover:border-emerald-700 bg-slate-50 hover:bg-emerald-50/40 rounded-3xl p-7 text-center transition-all cursor-pointer group">
                        <input type="file" id="file" name="file" accept=".pdf,.docx" 
                               class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" 
                               onchange="handleFileSelect(this)">
                        
                        <div id="uploadPrompt" class="space-y-3">
                            <div class="w-12 h-12 rounded-xl bg-white shadow-xs text-emerald-700 flex items-center justify-center mx-auto text-xl border-2 border-slate-200 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i>
                            </div>
                            <p class="text-sm font-bold text-slate-900">
                                <span class="text-emerald-800 underline">Klik untuk memilih file baru</span> jika ingin mengganti dokumen lama
                            </p>
                            <p class="text-xs font-semibold text-slate-600">
                                Format: PDF (.pdf) atau Word (.docx) • Maksimal 10 MB
                            </p>
                        </div>

                        <div id="filePreview" class="hidden text-left space-y-3">
                            <div class="flex items-center gap-4 p-4 bg-white rounded-2xl border-2 border-emerald-500 shadow-md">
                                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center text-xl flex-shrink-0 border border-emerald-300">
                                    <i id="fileIcon" class="fa-solid fa-file-lines"></i>
                                </div>
                                <div class="flex-1 overflow-hidden">
                                    <span class="text-xs font-black bg-emerald-700 text-white px-2 py-0.5 rounded-md">BERKAS PENGGANTI TERPILIH</span>
                                    <p id="fileName" class="text-base font-black text-slate-900 truncate mt-1"></p>
                                    <p id="fileSize" class="text-xs font-bold text-slate-600 mt-0.5"></p>
                                </div>
                            </div>
                            <p class="text-xs sm:text-sm font-bold text-amber-900 bg-amber-50 border border-amber-300 p-2.5 rounded-xl text-center">
                                ⚠️ Perhatian: File baru ini akan menggantikan berkas lama saat Anda menekan tombol Simpan di bawah.
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
                    <input type="text" id="judul" name="judul" value="{{ old('judul', $module->judul) }}" required 
                           class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all">
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
                        <input type="text" id="mapel" name="mapel" list="mapel-suggestions" value="{{ old('mapel', $module->mapel) }}" required 
                               class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all">
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
                        <input id="kb_nomor" name="kb_nomor" type="text" required maxlength="255" value="{{ old('kb_nomor', $module->kb_nomor) }}" 
                               class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-bold rounded-2xl py-3.5 px-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all">
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
                              placeholder="Tuliskan capaian atau target pembelajaran yang diharapkan..."
                              class="w-full bg-white border-2 border-slate-400 text-slate-900 text-sm sm:text-base font-medium rounded-2xl p-4 outline-none focus:border-emerald-700 focus:ring-4 focus:ring-emerald-700/20 transition-all leading-relaxed">{{ old('tp', $module->tp) }}</textarea>
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
                        <input type="url" id="video_url" name="video_url" value="{{ old('video_url', $module->video_url) }}" 
                               placeholder="https://www.youtube.com/watch?v=..."
                               class="w-full bg-white border-2 border-slate-300 text-slate-900 text-sm font-semibold rounded-xl py-2.5 px-3.5 outline-none focus:border-red-600 focus:ring-2 focus:ring-red-600/20 transition-all">
                        <x-input-error class="mt-1 text-sm font-bold text-rose-700" :messages="$errors->get('video_url')" />
                    </div>

                    <!-- Link Kuis Eksternal & Tombol Kuis Aplikasi -->
                    <div class="p-5 bg-slate-50 rounded-2xl border-2 border-slate-300 space-y-3">
                        <div>
                            <label for="kuis_url" class="block text-sm font-bold text-slate-900 flex items-center gap-2">
                                <i class="fa-solid fa-pen-to-square text-emerald-700 text-lg"></i>
                                <span>Link Kuis Luar (Opsional)</span>
                            </label>
                            <p class="text-xs font-medium text-slate-700 mt-1">
                                Tautan Google Form, Quizizz, atau lembar kerja siswa jika ada.
                            </p>
                        </div>
                        <input type="url" id="kuis_url" name="kuis_url" value="{{ old('kuis_url', $module->kuis_url) }}" 
                               placeholder="https://forms.gle/... atau Quizizz"
                               class="w-full bg-white border-2 border-slate-300 text-slate-900 text-sm font-semibold rounded-xl py-2.5 px-3.5 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20 transition-all">
                        <x-input-error class="mt-1 text-sm font-bold text-rose-700" :messages="$errors->get('kuis_url')" />

                        <!-- Tautan Menuju Kuis Pilihan Ganda Internal -->
                        <div class="pt-2 border-t border-slate-200">
                            <a href="{{ route('guru.modules.quiz.edit', $module) }}" 
                               class="w-full px-4 py-2.5 bg-purple-50 hover:bg-purple-100 text-purple-900 border-2 border-purple-400 rounded-xl font-bold text-xs sm:text-sm flex items-center justify-center gap-2 transition-colors cursor-pointer shadow-2xs">
                                <i class="fa-solid fa-list-check text-purple-700"></i>
                                <span>Buat / Kelola Kuis Pilihan Ganda di Aplikasi →</span>
                            </a>
                        </div>
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
                        Setelah tanggal ini lewat, modul tetap aman dan tersimpan di arsip sekolah.
                    </p>
                    <input type="date" id="berlaku_sampai" name="berlaku_sampai" 
                           value="{{ old('berlaku_sampai', $module->berlaku_sampai ? $module->berlaku_sampai->format('Y-m-d') : '') }}"
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

                <button type="submit" 
                        class="w-full sm:w-auto order-1 sm:order-2 py-4 px-10 bg-emerald-700 hover:bg-emerald-800 text-white font-black text-base sm:text-lg rounded-2xl shadow-lg hover:shadow-xl transition-all hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3 cursor-pointer">
                    <i class="fa-solid fa-floppy-disk text-xl"></i>
                    <span>Simpan Seluruh Perubahan Modul</span>
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
                
                const sizeInKb = (file.size / 1024).toFixed(1);
                if (file.size > 1024 * 1024) {
                    sizeEl.textContent = 'Ukuran berkas baru: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                } else {
                    sizeEl.textContent = 'Ukuran berkas baru: ' + sizeInKb + ' KB';
                }

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
