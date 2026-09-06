<x-premium-layout>
    <div class="space-y-8 max-w-6xl mx-auto pb-12">
        
        <!-- Hero Banner Panduan Guru -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#008546] text-white p-6 sm:p-8 rounded-3xl shadow-lg relative overflow-hidden">
            <!-- Background Glow Effect -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold mb-3.5 backdrop-blur-xs">
                    <i class="fa-solid fa-circle-question text-emerald-400"></i>
                    <span>Panduan Resmi Guru • SMK Negeri 1 Kinali</span>
                </div>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight leading-tight">
                    Petunjuk Penggunaan Sistem E-Modul & AI RAG
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-2.5 leading-relaxed">
                    Panduan terstruktur untuk Bapak/Ibu Guru dalam mengelola Kegiatan Belajar (KB 1, 2, & 3), merumuskan Tujuan Pembelajaran (TP), mengunggah materi pelajaran, memantau proses kecerdasan buatan (AI Indexing), dan mengelola akun siswa TKJ.
                </p>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-3 mt-6">
                    <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md transition-all hover:scale-105 active:scale-95 border border-emerald-400/30">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Modul Baru</span>
                    </a>
                    <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all border border-white/20">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>Katalog Modul Guru</span>
                    </a>
                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all border border-white/20">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Kelola Akun Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Langkah Alur Kerja Guru (Workflow Cards) -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-[#008546] flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-diagram-project"></i>
                    </span>
                    <span>Alur Kerja Guru (4 Tahapan Utama)</span>
                </h3>
                <span class="text-xs text-slate-400 font-medium">Sistematis & Terintegrasi Otomatis</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tahap 1 -->
                <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] font-extrabold text-xs flex items-center justify-center border border-emerald-100">
                                01
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md">
                                Input
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-file-arrow-up text-[#008546]"></i> Unggah Materi
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Pilih Mata Pelajaran, tentukan <strong>Kegiatan Belajar (KB 1, 2, atau 3)</strong>, dan unggah modul format PDF atau DOCX resmi (maks. 10MB).
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-50 text-[11px] text-slate-400 flex items-center gap-1">
                        <i class="fa-solid fa-check text-emerald-500"></i> Berkas tersimpan aman & privat
                    </div>
                </div>

                <!-- Tahap 2 -->
                <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-blue-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 font-extrabold text-xs flex items-center justify-center border border-blue-100">
                                02
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-blue-600 bg-blue-50 px-2 py-0.5 rounded-md">
                                Kurikulum
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-bullseye text-blue-600"></i> Rumuskan TP
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Tuliskan <strong>Tujuan Pembelajaran (TP)</strong> spesifik sesuai Kurikulum Merdeka agar siswa memahami target kompetensi yang harus dicapai.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-50 text-[11px] text-slate-400 flex items-center gap-1">
                        <i class="fa-solid fa-check text-blue-500"></i> Panduan kompetensi belajar mandiri
                    </div>
                </div>

                <!-- Tahap 3 -->
                <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-purple-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 font-extrabold text-xs flex items-center justify-center border border-purple-100">
                                03
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 bg-purple-50 px-2 py-0.5 rounded-md">
                                Interaktif
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-link text-purple-600"></i> Video & Kuis
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Sematkan link <strong>Video Pembelajaran</strong> (YouTube/GDrive) dan tautan <strong>Kuis Evaluasi</strong> (Google Form/Quizizz) untuk menguji pemahaman siswa.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-50 text-[11px] text-slate-400 flex items-center gap-1">
                        <i class="fa-solid fa-check text-purple-500"></i> Pembelajaran multimedia lengkap
                    </div>
                </div>

                <!-- Tahap 4 -->
                <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-amber-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 font-extrabold text-xs flex items-center justify-center border border-amber-100">
                                04
                            </span>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 bg-amber-50 px-2 py-0.5 rounded-md">
                                AI Otomatis
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-brain text-amber-600"></i> AI RAG Indexing
                        </h4>
                        <p class="text-xs text-slate-500 mt-2 leading-relaxed">
                            Sistem secara otomatis mengekstrak teks dokumen, memecah paragraf (chunking), dan membuat embedding vektor AI agar chatbot siswa siap menjawab tanya jawab.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-50 text-[11px] text-slate-400 flex items-center gap-1">
                        <i class="fa-solid fa-check text-amber-500"></i> Asisten 24/7 untuk siswa TKJ
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan Rinci Komponen Kegiatan Belajar (KB 1, 2, 3) -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-[#008546] text-xs font-bold mb-2">
                    <i class="fa-solid fa-layer-group"></i> Struktur Standar Modul
                </div>
                <h3 class="text-lg font-bold text-slate-900">
                    Panduan Pengisian Komponen Modul Pembelajaran
                </h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Setiap modul yang diunggah ke dalam sistem wajib memiliki elemen pedagogis yang lengkap demi kelancaran proses pembelajaran mandiri siswa di SMK N 1 Kinali:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Komponen 1: Pemilihan KB & Mapel -->
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-sm shrink-0 border border-emerald-200">
                        <i class="fa-solid fa-tag"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Mata Pelajaran & Nomor KB</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Pilih mata pelajaran TKJ yang diampu (misal: <em>Administrasi Infrastruktur Jaringan, Administrasi Sistem Jaringan, Teknologi Jaringan Berbasis Luas, atau Dasar Program Keahlian TJKT</em>).
                        </p>
                        <div class="mt-2 text-[11px] bg-white p-2.5 rounded-xl border border-slate-200 text-slate-500">
                            <strong>Pilihan KB:</strong> Pilih <span class="text-emerald-700 font-semibold">KB 1</span>, <span class="text-blue-700 font-semibold">KB 2</span>, atau <span class="text-purple-700 font-semibold">KB 3</span> untuk menstrukturkan tahapan belajar siswa dalam satu semester atau satu tema bahasan.
                        </div>
                    </div>
                </div>

                <!-- Komponen 2: Tujuan Pembelajaran (TP) -->
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70">
                    <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0 border border-blue-200">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Tujuan Pembelajaran (TP)</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Rumuskan TP dengan jelas menggunakan Kata Kerja Operasional (KKO) Taksonomi Bloom yang terukur sesuai Kurikulum Merdeka.
                        </p>
                        <div class="mt-2 text-[11px] bg-white p-2.5 rounded-xl border border-slate-200 text-slate-500">
                            <strong>Contoh yang Baik:</strong> <em>"Peserta didik mampu mengkonfigurasi routing dinamis OSPF multi-area pada RouterOS MikroTik dan memverifikasi tabel routing secara tepat."</em>
                        </div>
                    </div>
                </div>

                <!-- Komponen 3: Berkas Dokumen (PDF/DOCX) -->
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70">
                    <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm shrink-0 border border-amber-200">
                        <i class="fa-solid fa-file-pdf"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Dokumen Modul Ajar (PDF / DOCX)</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Unggah berkas dokumen asli (maksimal 10 MB). Berkas ini akan diunduh oleh siswa dan dijadikan sumber pengetahuan oleh asisten AI.
                        </p>
                        <div class="mt-2 text-[11px] bg-white p-2.5 rounded-xl border border-slate-200 text-slate-500">
                            <strong>Catatan Penting:</strong> Pastikan dokumen berupa <em>teks digital asli</em> (bisa disorot/copy teksnya), bukan hasil foto kamera yang miring atau scan resolusi rendah agar AI dapat mengekstrak kalimat dengan sempurna.
                        </div>
                    </div>
                </div>

                <!-- Komponen 4: Link Video & Link Kuis -->
                <div class="flex items-start gap-4 p-4 rounded-2xl bg-slate-50/70 border border-slate-200/70">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-bold text-sm shrink-0 border border-rose-200">
                        <i class="fa-solid fa-photo-film"></i>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-bold text-slate-900">Tautan Video & Kuis Evaluasi</h4>
                        <p class="text-xs text-slate-600 leading-relaxed">
                            Mendukung penguatan visual praktikum jaringan serta evaluasi mandiri secara online.
                        </p>
                        <div class="mt-2 text-[11px] bg-white p-2.5 rounded-xl border border-slate-200 text-slate-500 space-y-1">
                            <div><i class="fa-brands fa-youtube text-rose-600 mr-1"></i> <strong>Link Video:</strong> URL YouTube demonstrasi praktikum atau simulasi Packet Tracer.</div>
                            <div><i class="fa-solid fa-pen-nib text-purple-600 mr-1"></i> <strong>Link Kuis:</strong> URL Google Form, Quizizz, atau lembar ujian online.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memahami Status Indexing AI & Penanganan Masalah -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-50 text-blue-600 text-xs font-bold mb-2">
                    <i class="fa-solid fa-robot"></i> RAG AI Knowledge Pipeline
                </div>
                <h3 class="text-lg font-bold text-slate-900">
                    Memahami Status Indeks AI & Tombol "Index Ulang"
                </h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Setelah modul diunggah, sistem background worker akan memproses dokumen menjadi vektor pengetahuan AI. Berikut arti status pada tabel modul guru:
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Status Completed -->
                <div class="p-4 rounded-2xl border-2 border-emerald-100 bg-emerald-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                            <i class="fa-solid fa-circle-check text-emerald-600"></i> Selesai (Completed)
                        </span>
                        <span class="text-xs font-bold text-emerald-600">Siap</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-800">Modul Siap Digunakan</h5>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Dokumen telah selesai di-chunking dan di-embed ke vektor AI. Chatbot siswa di menu dashboard sudah bisa menjawab pertanyaan berdasarkan modul ini.
                    </p>
                </div>

                <!-- Status Processing / Pending -->
                <div class="p-4 rounded-2xl border-2 border-amber-100 bg-amber-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800 border border-amber-300">
                            <i class="fa-solid fa-spinner fa-spin text-amber-600"></i> Diproses (Processing)
                        </span>
                        <span class="text-xs font-bold text-amber-600">Antrean</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-800">Sedang Dianalisa</h5>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Sistem sedang mengekstrak teks dan menghubungkan ke API Gemini untuk membentuk representasi vektor. Biasanya berlangsung 10 hingga 60 detik.
                    </p>
                </div>

                <!-- Status Failed -->
                <div class="p-4 rounded-2xl border-2 border-rose-100 bg-rose-50/40 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-300">
                            <i class="fa-solid fa-triangle-exclamation text-rose-600"></i> Gagal (Failed)
                        </span>
                        <span class="text-xs font-bold text-rose-600">Perlu Aksi</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-800">Gagal Ekstraksi</h5>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Terjadi kegagalan baca (misal PDF berisi scan gambar kosong, koneksi internet sekolah terputus, atau API kuota).
                    </p>
                </div>
            </div>

            <!-- Panduan Fitur Index Ulang -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center text-base shrink-0 shadow-sm">
                        <i class="fa-solid fa-bolt"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Kapan Harus Menggunakan Tombol "Index Ulang"?</h4>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Jika sebuah modul berstatus <strong>Gagal</strong> atau Anda baru saja merevisi isi file dokumen, Bapak/Ibu tidak perlu menghapus modul. Cukup klik tombol <strong>"Index Ulang"</strong> (ikon petir oranye) pada tabel modul, dan sistem akan memproses ulang pembentukan AI secara instan.
                        </p>
                    </div>
                </div>
                <a href="{{ route('guru.modules.index') }}" class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-white border border-slate-300 text-xs font-bold text-slate-700 hover:bg-slate-100 transition-all shadow-xs">
                    <i class="fa-solid fa-arrow-up-right-from-square"></i> Cek Tabel Modul
                </a>
            </div>
        </div>

        <!-- Panduan Manajemen Data Akun Siswa -->
        <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-50 text-purple-700 text-xs font-bold mb-2">
                    <i class="fa-solid fa-user-graduate"></i> Manajemen Pengguna
                </div>
                <h3 class="text-lg font-bold text-slate-900">
                    Panduan Kelola Akun & Kredensial Siswa
                </h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Guru memiliki wewenang penuh untuk mengatur akses siswa ke sistem pembelajaran AI ini melalui menu <strong>Data & Login Siswa</strong>:
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- Fitur Siswa 1 -->
                <div class="space-y-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs border border-purple-200">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">1. Tambah Siswa Baru</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Masukkan Nama Lengkap Siswa, Email (atau NISN@sekolah.sch.id), dan kata sandi default saat siswa baru masuk ke kelas TKJ.
                    </p>
                </div>

                <!-- Fitur Siswa 2 -->
                <div class="space-y-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs border border-purple-200">
                        <i class="fa-solid fa-key"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">2. Reset Kata Sandi Siswa</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Jika siswa lupa kata sandi saat praktikum di laboratorium, guru dapat mengganti password siswa secara langsung melalui tombol edit di tabel siswa.
                    </p>
                </div>

                <!-- Fitur Siswa 3 -->
                <div class="space-y-2">
                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs border border-purple-200">
                        <i class="fa-solid fa-user-xmark"></i>
                    </div>
                    <h4 class="text-xs font-bold text-slate-900">3. Hapus Akun Siswa Lulus</h4>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Bapak/Ibu dapat menghapus akun siswa yang telah lulus atau pindah sekolah demi menjaga kebersihan database dan keamanan akses sistem.
                    </p>
                </div>
            </div>

            <div class="pt-2">
                <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold shadow-xs transition-all">
                    <i class="fa-solid fa-arrow-right"></i> Buka Menu Data Siswa
                </a>
            </div>
        </div>

        <!-- Tips Praktis & FAQ Guru -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Best Practices Modul Ajar -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-lightbulb text-amber-500"></i>
                    <span>Tips Menyusun Modul "AI-Friendly"</span>
                </h3>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Agar Chatbot RAG dapat menjawab pertanyaan siswa dengan akurasi 100%, berikut tips praktis penyusunan dokumen materi:
                </p>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-[#008546] mt-0.5 shrink-0"></i>
                        <span><strong>Gunakan Penomoran Bab & Sub-Bab:</strong> Buat struktur heading yang tegas (contoh: <em>1.1 Pengertian Subnetting, 1.2 Cara Menghitung CIDR</em>).</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-[#008546] mt-0.5 shrink-0"></i>
                        <span><strong>Definisikan Istilah Teknis:</strong> Jelaskan istilah singkatan seperti VLAN, DHCP, DNS, CLI, GUI pada awal paragraf pertama materi.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-check text-[#008546] mt-0.5 shrink-0"></i>
                        <span><strong>Langkah Praktik Berurutan:</strong> Buat tutorial langkah konfigurasi dalam bentuk penomoran langkah 1, 2, 3 agar AI dapat memandu siswa dengan urutan yang benar.</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fa-solid fa-circle-xmark text-rose-500 mt-0.5 shrink-0"></i>
                        <span><strong>Hindari Foto/Scan Miring:</strong> Dokumen yang dipindai dengan kamera HP tanpa teks asli tidak dapat dibaca oleh sistem ekstraksi RAG.</span>
                    </li>
                </ul>
            </div>

            <!-- FAQ Guru -->
            <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-comments-question text-blue-600"></i>
                    <span>Pertanyaan yang Sering Diajukan (FAQ)</span>
                </h3>

                <div class="space-y-3">
                    <div class="border border-slate-200/80 rounded-2xl p-3.5 bg-slate-50/50">
                        <h5 class="text-xs font-bold text-slate-900">Apakah siswa bisa mengunduh modul PDF aslinya?</h5>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Ya. Di menu <strong>Katalog E-Modul</strong> siswa, terdapat tombol <em>"Unduh Dokumen Materi"</em> resmi sehingga siswa dapat menyimpan materi secara offline di perangkat mereka.
                        </p>
                    </div>

                    <div class="border border-slate-200/80 rounded-2xl p-3.5 bg-slate-50/50">
                        <h5 class="text-xs font-bold text-slate-900">Bagaimana jika AI menjawab di luar materi SMK N 1 Kinali?</h5>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Sistem telah diproteksi dengan mekanisme <em>Strict RAG Grounding</em>. Jika siswa menanyakan hal yang tidak tercantum di modul yang diunggah guru, AI akan sopan mengarahkan siswa untuk bertanya sesuai materi ajar yang tersedia.
                        </p>
                    </div>

                    <div class="border border-slate-200/80 rounded-2xl p-3.5 bg-slate-50/50">
                        <h5 class="text-xs font-bold text-slate-900">Bisa mengedit materi atau tautan kuis yang sudah diunggah?</h5>
                        <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                            Bisa. Klik ikon <strong>Edit</strong> (pensil biru) pada tabel modul. Bapak/Ibu dapat memperbarui Judul, TP, Tautan Video, Tautan Kuis, maupun mengganti berkas lampiran kapan saja.
                        </p>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bantuan & Narahubung -->
        <div class="bg-gradient-to-r from-emerald-500/10 via-teal-500/10 to-blue-500/10 border border-emerald-200 rounded-3xl p-6 flex flex-col sm:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-4 text-center sm:text-left">
                <div class="w-12 h-12 rounded-2xl bg-[#008546] text-white flex items-center justify-center text-xl shrink-0 shadow-md">
                    <i class="fa-solid fa-headset"></i>
                </div>
                <div>
                    <h4 class="text-sm font-bold text-slate-900">Memerlukan Bantuan Teknis atau Penambahan Kuota Modul?</h4>
                    <p class="text-xs text-slate-600 mt-0.5">
                        Hubungi Tim Administrator IT Laboratorium TKJ SMK Negeri 1 Kinali jika menemukan kendala sistem atau server lokal.
                    </p>
                </div>
            </div>
            <a href="mailto:admin@smkn1kinali.sch.id" class="shrink-0 inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold px-5 py-2.5 rounded-xl shadow-xs transition-all">
                <i class="fa-solid fa-envelope"></i> Hubungi Admin IT
            </a>
        </div>

    </div>
</x-premium-layout>
