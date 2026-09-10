<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border-2 border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <a href="{{ route('guru.modules.index') }}" class="w-10 h-10 rounded-2xl bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-[#008546] flex items-center justify-center transition-colors shadow-xs" title="Kembali">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        <i class="fa-solid fa-cloud-arrow-up text-[#008546]"></i> Upload Modul & Kegiatan Belajar Baru
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Unggah materi dokumen, atur Tujuan Pembelajaran (TP), serta sematkan video dan kuis interaktif.</p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200">
                    <i class="fa-solid fa-microchip text-[10px] animate-pulse"></i> RAG AI Indexing Ready
                </span>
            </div>
        </div>

        <!-- Alert Notifications -->
        @if($errors->any())
            <div class="bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl text-xs space-y-1 animate-fade-in shadow-xs">
                <div class="font-bold flex items-center gap-2 text-rose-700">
                    <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                    <span>Harap periksa kembali form pengisian:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Main Form (2-Column Balanced Layout) -->
        <form id="uploadForm" method="POST" action="{{ route('guru.modules.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                
                <!-- KOLOM KIRI (KONTEN & MATERI) - 7 COLS -->
                <div class="lg:col-span-7 space-y-6">
                    
                    <!-- Card 1: Informasi Pokok & Kegiatan Belajar -->
                    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-book-bookmark text-[#008546]"></i> 1. Identitas Modul & Kegiatan Belajar
                            </h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md">Wajib Diisi</span>
                        </div>

                        <!-- Judul Modul -->
                        <div>
                            <label for="judul" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Judul Modul / Materi Pembelajaran <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-heading text-xs"></i>
                                </span>
                                <input type="text" id="judul" name="judul" value="{{ old('judul') }}" required placeholder="Contoh: Konfigurasi Virtual LAN (VLAN) pada Switch Manageable"
                                       class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm rounded-xl py-2.5 pl-10 pr-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('judul')" />
                        </div>

                        <!-- Mata Pelajaran -->
                        <div>
                            <label for="mapel" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Mata Pelajaran / Konsentrasi Keahlian <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                                    <i class="fa-solid fa-graduation-cap text-xs"></i>
                                </span>
                                <input type="text" id="mapel" name="mapel" list="mapel-suggestions" value="{{ old('mapel') }}" required placeholder="Pilih atau ketik mata pelajaran..."
                                       class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm rounded-xl py-2.5 pl-10 pr-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                                <datalist id="mapel-suggestions">
                                    <option value="Administrasi Infrastruktur Jaringan">
                                    <option value="Administrasi Sistem Jaringan">
                                    <option value="Teknologi Jaringan Berbasis Luas (WAN)">
                                    <option value="Teknologi Layanan Jaringan">
                                    <option value="Dasar-Dasar Teknik Komputer & Jaringan">
                                    <option value="Keamanan Jaringan">
                                    <option value="Subnetting & Routing">
                                </datalist>
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('mapel')" />
                        </div>

                        <!-- Pilihan Kegiatan Belajar (KB) Cards -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">
                                Tentukan Kegiatan Belajar (KB) <span class="text-rose-500">*</span>
                            </label>
                            <div class="grid grid-cols-3 gap-3">
                                @foreach(['KB 1', 'KB 2', 'KB 3'] as $kb)
                                <label class="kb-option-card relative flex flex-col items-center justify-center p-3 sm:p-4 rounded-2xl border-2 cursor-pointer transition-all duration-200 {{ old('kb_nomor', 'KB 1') === $kb ? 'border-[#008546] bg-emerald-50/70 text-[#008546] shadow-xs' : 'border-slate-200 bg-slate-50/50 text-slate-600 hover:border-slate-300 hover:bg-slate-50' }}">
                                    <input type="radio" name="kb_nomor" value="{{ $kb }}" class="sr-only" {{ old('kb_nomor', 'KB 1') === $kb ? 'checked' : '' }} onchange="selectKbOption(this)">
                                    <div class="w-8 h-8 rounded-xl bg-white text-[#008546] flex items-center justify-center shadow-xs mb-1.5 border border-slate-100">
                                        <i class="fa-solid fa-layer-group text-xs"></i>
                                    </div>
                                    <span class="font-extrabold text-xs sm:text-sm">{{ $kb }}</span>
                                    <span class="text-[10px] text-slate-400 mt-0.5">Kegiatan Belajar</span>
                                </label>
                                @endforeach
                            </div>
                            <x-input-error class="mt-1" :messages="$errors->get('kb_nomor')" />
                        </div>

                        <!-- Tujuan Pembelajaran (TP) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="tp" class="text-xs font-bold text-slate-700">
                                    Tujuan Pembelajaran (TP)
                                </label>
                                <span class="text-[10px] text-slate-400">Direkomendasikan</span>
                            </div>
                            <textarea id="tp" name="tp" rows="4" placeholder="Tuliskan capaian atau target pembelajaran yang diharapkan siswa kuasai pada KB ini...&#10;Contoh:&#10;1. Siswa mampu menjelaskan prinsip dasar VLAN.&#10;2. Siswa mampu mengkonfigurasi switch port mode access dan trunk."
                                      class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs sm:text-sm rounded-2xl p-3.5 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all leading-relaxed placeholder-slate-400">{{ old('tp') }}</textarea>
                            <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                                <i class="fa-solid fa-circle-info text-[#008546]"></i> TP ini akan ditampilkan secara khusus di kartu Kegiatan Belajar siswa.
                            </p>
                            <x-input-error class="mt-1" :messages="$errors->get('tp')" />
                        </div>
                    </div>

                    <!-- Card 2: Media Interaktif & Evaluasi -->
                    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-7 space-y-5">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3.5">
                            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-photo-film text-blue-600"></i> 2. Media Pembelajaran & Link Evaluasi
                            </h3>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 bg-slate-50 px-2 py-0.5 rounded-md">Opsional</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Link Video -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                                <label for="video_url" class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-brands fa-youtube text-red-500 text-sm"></i>
                                    <span>Link Video Pembelajaran</span>
                                </label>
                                <input type="url" id="video_url" name="video_url" value="{{ old('video_url') }}" placeholder="https://www.youtube.com/watch?v=..."
                                       class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl py-2 px-3 outline-none focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition-all">
                                <p class="text-[10px] text-slate-400 leading-tight">Mendukung embed video YouTube otomatis di halaman siswa.</p>
                                <x-input-error class="mt-1" :messages="$errors->get('video_url')" />
                            </div>

                            <!-- Link Kuis -->
                            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200/80 space-y-2">
                                <label for="kuis_url" class="block text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <i class="fa-solid fa-pen-to-square text-[#008546] text-sm"></i>
                                    <span>Link Kuis / Evaluasi</span>
                                </label>
                                <input type="url" id="kuis_url" name="kuis_url" value="{{ old('kuis_url') }}" placeholder="https://forms.gle/... atau Quizizz"
                                       class="w-full bg-white border border-slate-200 text-slate-800 text-xs rounded-xl py-2 px-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all">
                                <p class="text-[10px] text-slate-400 leading-tight">Tautan Google Form, Quizizz, atau lembar kerja siswa.</p>
                                <x-input-error class="mt-1" :messages="$errors->get('kuis_url')" />
                            </div>
                        </div>
                    </div>

                </div>

                <!-- KOLOM KANAN (UPLOAD FILE & AKSI) - 5 COLS -->
                <div class="lg:col-span-5 space-y-6">
                    
                    <!-- Card 3: Upload File Dokumen Materi (Interactive Drag & Drop) -->
                    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-file-arrow-up text-[#008546]"></i> 3. Berkas Materi Asli
                            </h3>
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/50">Maks 10 MB</span>
                        </div>

                        <!-- Drag & Drop Zone -->
                        <div id="dropZone" class="relative border-2 border-dashed border-slate-300 hover:border-[#008546] bg-slate-50 hover:bg-emerald-50/40 rounded-3xl p-6 text-center transition-all cursor-pointer group">
                            <input type="file" id="file" name="file" accept=".pdf,.docx" required class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10" onchange="handleFileSelect(this)">
                            
                            <!-- State 1: Belum Ada File -->
                            <div id="uploadPrompt" class="space-y-3">
                                <div class="w-14 h-14 rounded-2xl bg-white shadow-md text-[#008546] flex items-center justify-center mx-auto text-2xl group-hover:scale-110 transition-transform border border-slate-100">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">
                                        <span class="text-[#008546] underline">Pilih berkas</span> atau seret & lepas ke sini
                                    </p>
                                    <p class="text-[11px] text-slate-400 mt-1">Format didukung: <strong>PDF</strong> atau Word (<strong>.docx</strong>)</p>
                                </div>
                                <div class="flex items-center justify-center gap-2 pt-1">
                                    <span class="text-[10px] bg-white border border-slate-200 text-slate-500 font-semibold px-2 py-0.5 rounded-md shadow-2xs">
                                        <i class="fa-solid fa-file-pdf text-red-500 mr-1"></i> PDF
                                    </span>
                                    <span class="text-[10px] bg-white border border-slate-200 text-slate-500 font-semibold px-2 py-0.5 rounded-md shadow-2xs">
                                        <i class="fa-solid fa-file-word text-blue-500 mr-1"></i> DOCX
                                    </span>
                                </div>
                            </div>

                            <!-- State 2: File Terpilih (Interactive Preview) -->
                            <div id="filePreview" class="hidden text-left space-y-3">
                                <div class="flex items-center gap-3 p-3 bg-white rounded-2xl border border-emerald-200 shadow-xs">
                                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center text-lg flex-shrink-0">
                                        <i id="fileIcon" class="fa-solid fa-file-lines"></i>
                                    </div>
                                    <div class="flex-1 overflow-hidden">
                                        <p id="fileName" class="text-xs font-bold text-slate-800 truncate"></p>
                                        <p id="fileSize" class="text-[10px] text-slate-400 mt-0.5"></p>
                                    </div>
                                    <span class="text-[10px] bg-emerald-100 text-emerald-800 font-bold px-2 py-0.5 rounded-md">Siap</span>
                                </div>
                                <p class="text-[10px] text-slate-400 text-center">Klik lagi untuk mengganti file dokumen</p>
                            </div>
                        </div>
                        <x-input-error class="mt-1" :messages="$errors->get('file')" />
                    </div>

                    <!-- Card 4: Pengaturan Masa Berlaku Modul -->
                    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                                <i class="fa-solid fa-calendar-days text-[#008546]"></i> 4. Periode Aktif Modul
                            </h3>
                            <span class="text-[10px] text-slate-400 font-medium">Auto-Archive</span>
                        </div>

                        <div>
                            <label for="berlaku_sampai" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Berlaku Sampai Tanggal
                            </label>
                            <input type="date" id="berlaku_sampai" name="berlaku_sampai" value="{{ old('berlaku_sampai', now(config('app.display_timezone'))->addMonths(6)->format('Y-m-d')) }}"
                                   class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all font-medium">
                            <p class="text-[10px] text-slate-400 mt-1.5 leading-relaxed">
                                Setelah tanggal ini lewat, modul otomatis <strong>diarsipkan</strong> dari pencarian AI siswa, namun arsip berkas asli tetap aman.
                            </p>
                            <x-input-error class="mt-1" :messages="$errors->get('berlaku_sampai')" />
                        </div>
                    </div>

                    <!-- Card 5: Tombol Aksi Submit & Batal -->
                    <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 space-y-3">
                        <button type="submit" id="submitBtn" class="w-full py-3.5 px-6 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-sm rounded-2xl shadow-md hover:shadow-lg transition-all hover:scale-[1.01] active:scale-[0.99] flex items-center justify-center gap-2">
                            <i class="fa-solid fa-cloud-arrow-up"></i>
                            <span>Upload & Proses AI Sekarang</span>
                        </button>

                        <a href="{{ route('guru.modules.index') }}" class="w-full py-3 px-6 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-2xl transition-colors flex items-center justify-center">
                            Batal dan Kembali
                        </a>
                    </div>

                </div>

            </div>
        </form>

    </div>

    <!-- Script Interaktif UI -->
    <script>
        function selectKbOption(radio) {
            document.querySelectorAll('.kb-option-card').forEach(card => {
                const input = card.querySelector('input[type="radio"]');
                if (input.checked) {
                    card.classList.add('border-[#008546]', 'bg-emerald-50/70', 'text-[#008546]', 'shadow-xs');
                    card.classList.remove('border-slate-200', 'bg-slate-50/50', 'text-slate-600');
                } else {
                    card.classList.remove('border-[#008546]', 'bg-emerald-50/70', 'text-[#008546]', 'shadow-xs');
                    card.classList.add('border-slate-200', 'bg-slate-50/50', 'text-slate-600');
                }
            });
        }

        function handleFileSelect(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                const prompt = document.getElementById('uploadPrompt');
                const preview = document.getElementById('filePreview');
                const nameEl = document.getElementById('fileName');
                const sizeEl = document.getElementById('fileSize');
                const iconEl = document.getElementById('fileIcon');

                nameEl.textContent = file.name;
                
                // Format file size
                const sizeInKb = (file.size / 1024).toFixed(1);
                if (file.size > 1024 * 1024) {
                    sizeEl.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
                } else {
                    sizeEl.textContent = sizeInKb + ' KB';
                }

                // File icon
                if (file.name.endsWith('.pdf')) {
                    iconEl.className = 'fa-solid fa-file-pdf text-red-500';
                } else if (file.name.endsWith('.docx') || file.name.endsWith('.doc')) {
                    iconEl.className = 'fa-solid fa-file-word text-blue-500';
                } else {
                    iconEl.className = 'fa-solid fa-file-lines text-[#008546]';
                }

                prompt.classList.add('hidden');
                preview.classList.remove('hidden');
            }
        }
    </script>
</x-premium-layout>
