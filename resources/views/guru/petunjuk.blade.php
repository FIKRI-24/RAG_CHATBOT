<x-premium-layout>
    <div class="space-y-8 max-w-6xl mx-auto pb-12">
        
        <!-- Monograph Header Panduan Guru -->
        <div class="relative bg-slate-900 text-white p-6 sm:p-8 rounded-xl border border-slate-800 overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-[#008546]"></div>
            
            <div class="relative z-10 max-w-3xl">
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-[11px] font-mono mb-3">
                    <i class="fa-solid fa-book-open text-emerald-400 text-[10px]"></i>
                    <span>DOKUMENTASI OPERASIONAL GURU • SMK NEGERI 1 KINALI</span>
                </div>
                <h1 class="text-xl sm:text-2xl font-bold tracking-tight leading-tight text-white">
                    Petunjuk Penggunaan Sistem E-Modul & AI RAG
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                    Panduan teknis bagi Guru pengampu dalam mengelola Kegiatan Belajar (KB), merumuskan Tujuan Pembelajaran (TP), mengunggah dokumen materi ajar, memantau status indeks vektor AI, dan mengatur akses akun siswa TKJ.
                </p>

                <!-- Quick Action Buttons -->
                <div class="flex flex-wrap items-center gap-2.5 mt-5">
                    <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Modul Baru</span>
                    </a>
                    <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg transition-colors border border-slate-700">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>Katalog Modul Guru</span>
                    </a>
                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg transition-colors border border-slate-700">
                        <i class="fa-solid fa-users-gear"></i>
                        <span>Kelola Akun Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4 Langkah Alur Kerja Guru (Unified Engineering Pipeline) -->
        <div class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 border-b border-slate-200 pb-3">
                <h3 class="text-base font-bold text-slate-900">
                    Alur Kerja Guru (4 Tahapan Utama)
                </h3>
                <span class="text-xs font-mono text-slate-500">TAHAP 01 — 04</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Tahap 1 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono font-bold text-xs">
                                01
                            </span>
                            <span class="text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-500">
                                Unggahan
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">
                            Unggah Materi
                        </h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Pilih Mata Pelajaran, tentukan <strong>nama atau nomor Kegiatan Belajar (KB)</strong>, dan unggah dokumen PDF atau DOCX resmi (maks. 10MB).
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-[#008546]"></i> Tersimpan di storage privat
                    </div>
                </div>

                <!-- Tahap 2 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono font-bold text-xs">
                                02
                            </span>
                            <span class="text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-500">
                                Kurikulum
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">
                            Rumuskan TP
                        </h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Tuliskan <strong>Tujuan Pembelajaran (TP)</strong> spesifik sesuai Kurikulum Merdeka agar siswa memahami target kompetensi yang harus dicapai.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-[#008546]"></i> Acuan kompetensi mandiri
                    </div>
                </div>

                <!-- Tahap 3 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded bg-slate-900 text-white font-mono font-bold text-xs">
                                03
                            </span>
                            <span class="text-[10px] font-mono font-semibold uppercase tracking-wider text-slate-500">
                                Multimedia
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">
                            Video & Kuis
                        </h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Sematkan tautan <strong>Video Pembelajaran</strong> (YouTube/Google Drive) serta susun <strong>Kuis Objektif</strong> untuk menguji pemahaman siswa.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-[#008546]"></i> Evaluasi terintegrasi rekap
                    </div>
                </div>

                <!-- Tahap 4 -->
                <div class="bg-white p-5 rounded-xl border border-slate-200/90 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2 py-0.5 rounded bg-[#008546] text-white font-mono font-bold text-xs">
                                04
                            </span>
                            <span class="text-[10px] font-mono font-semibold uppercase tracking-wider text-[#008546]">
                                Vektor RAG
                            </span>
                        </div>
                        <h4 class="font-bold text-sm text-slate-900">
                            AI RAG Indexing
                        </h4>
                        <p class="text-xs text-slate-600 mt-1.5 leading-relaxed">
                            Worker mengekstrak teks dokumen, memecah paragraf (chunking), dan membentuk embedding vektor agar asisten AI siap menjawab berdasarkan modul.
                        </p>
                    </div>
                    <div class="mt-4 pt-3 border-t border-slate-100 text-[11px] text-slate-500 flex items-center gap-1.5">
                        <i class="fa-solid fa-check text-[#008546]"></i> Grounded pada dokumen guru
                    </div>
                </div>
            </div>
        </div>

        <!-- Panduan Rinci Komponen Kegiatan Belajar -->
        <div class="bg-white p-6 sm:p-7 rounded-xl border border-slate-200/90 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">
                    Panduan Pengisian Komponen Modul Pembelajaran
                </h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Spesifikasi komponen pedagogis dan teknis pada setiap modul yang diunggah ke sistem SMK Negeri 1 Kinali:
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Komponen 1: Pemilihan KB & Mapel -->
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-[#008546]">[A]</span>
                        <h4 class="text-sm font-bold text-slate-900">Mata Pelajaran & Nomor KB</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Pilih mata pelajaran TKJ yang diampu (misal: <em>Administrasi Infrastruktur Jaringan, Administrasi Sistem Jaringan, Teknologi Jaringan Berbasis Luas, atau Dasar Program Keahlian TJKT</em>).
                    </p>
                    <div class="text-[11px] bg-white p-2.5 rounded border border-slate-200 text-slate-600">
                        <strong>Penamaan KB:</strong> Gunakan kode terstruktur seperti <span class="font-mono font-semibold text-slate-900">KB 1</span>, <span class="font-mono font-semibold text-slate-900">KB 2</span>, atau <span class="font-mono font-semibold text-slate-900">KB 4</span>.
                    </div>
                </div>

                <!-- Komponen 2: Tujuan Pembelajaran (TP) -->
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-[#008546]">[B]</span>
                        <h4 class="text-sm font-bold text-slate-900">Tujuan Pembelajaran (TP)</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Rumuskan TP dengan Kata Kerja Operasional (KKO) yang terukur sesuai capaian Kurikulum Merdeka.
                    </p>
                    <div class="text-[11px] bg-white p-2.5 rounded border border-slate-200 text-slate-600">
                        <strong>Contoh:</strong> <em>"Peserta didik mampu mengkonfigurasi routing dinamis OSPF multi-area pada RouterOS MikroTik dan memverifikasi tabel routing secara tepat."</em>
                    </div>
                </div>

                <!-- Komponen 3: Berkas Dokumen (PDF/DOCX) -->
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-[#008546]">[C]</span>
                        <h4 class="text-sm font-bold text-slate-900">Dokumen Modul Ajar (PDF / DOCX)</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Unggah berkas dokumen asli (maksimal 10 MB). Berkas ini diunduh oleh siswa dan diekstrak menjadi basis pengetahuan asisten AI.
                    </p>
                    <div class="text-[11px] bg-white p-2.5 rounded border border-slate-200 text-slate-600">
                        <strong>Catatan Teknis:</strong> Gunakan dokumen <em>teks digital asli</em> (bukan hasil foto kamera/scan gambar kosong) agar ekstraksi teks berjalan akurat.
                    </div>
                </div>

                <!-- Komponen 4: Link Video & Link Kuis -->
                <div class="p-4 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2">
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-[#008546]">[D]</span>
                        <h4 class="text-sm font-bold text-slate-900">Tautan Video & Kuis Evaluasi</h4>
                    </div>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Mendukung demonstrasi visual praktikum jaringan serta evaluasi pemahaman siswa.
                    </p>
                    <div class="text-[11px] bg-white p-2.5 rounded border border-slate-200 text-slate-600 space-y-1">
                        <div><strong>Link Video:</strong> URL YouTube atau Google Drive demonstrasi praktikum / Packet Tracer.</div>
                        <div><strong>Kuis Evaluasi:</strong> Soal pilihan ganda internal (1x kesempatan) atau tautan evaluasi eksternal.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memahami Status Indexing AI & Penanganan Masalah -->
        <div class="bg-white p-6 sm:p-7 rounded-xl border border-slate-200/90 space-y-6">
            <div class="border-b border-slate-100 pb-4">
                <h3 class="text-base font-bold text-slate-900">
                    Memahami Status Indeks AI & Tombol "Index Ulang"
                </h3>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                    Arti indikator status pemrosesan dokumen pada tabel manajemen modul:
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Status Completed -->
                <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200">
                            Selesai (Completed)
                        </span>
                        <span class="text-[11px] font-mono font-semibold text-[#008546]">READY</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-900">Modul Siap Digunakan</h5>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Dokumen telah selesai di-chunking dan di-embed ke vektor AI. Chatbot siswa sudah dapat menjawab pertanyaan dari modul ini.
                    </p>
                </div>

                <!-- Status Processing / Pending -->
                <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                            Diproses (Processing)
                        </span>
                        <span class="text-[11px] font-mono font-semibold text-amber-700">QUEUE</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-900">Sedang Dianalisa</h5>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Sistem sedang mengekstrak teks dan membentuk representasi vektor. Proses ini umumnya memerlukan waktu 10 hingga 60 detik.
                    </p>
                </div>

                <!-- Status Failed -->
                <div class="p-4 rounded-lg border border-slate-200 bg-slate-50/60 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-semibold bg-rose-50 text-rose-800 border border-rose-200">
                            Gagal (Failed)
                        </span>
                        <span class="text-[11px] font-mono font-semibold text-rose-700">ERROR</span>
                    </div>
                    <h5 class="text-xs font-bold text-slate-900">Gagal Ekstraksi</h5>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Terjadi kendala pembacaan berkas (misal PDF berisi hasil pindaian gambar tanpa lapisan teks atau gangguan jaringan API).
                    </p>
                </div>
            </div>

            <!-- Panduan Fitur Index Ulang -->
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                <div>
                    <h4 class="text-xs font-bold text-slate-900">Kapan Menggunakan Tombol "Coba Index Ulang"?</h4>
                    <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                        Jika sebuah modul berstatus <strong>Gagal</strong> atau tertahan di antrean, buka halaman detail modul lalu klik <strong>"Coba Index Ulang"</strong> untuk memproses ulang tanpa harus menghapus modul.
                    </p>
                </div>
                <a href="{{ route('guru.modules.index') }}" class="shrink-0 inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white border border-slate-300 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                    <span>Buka Tabel Modul</span>
                </a>
            </div>
        </div>

        <!-- Panduan Manajemen Data Akun Siswa & Tips -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- Best Practices Modul Ajar -->
            <div class="bg-white p-6 rounded-xl border border-slate-200/90 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">
                    Pedoman Penyusunan Dokumen Materi RAG
                </h3>
                <ul class="space-y-2.5 text-xs text-slate-600">
                    <li class="flex items-start gap-2.5">
                        <span class="font-mono font-bold text-[#008546] shrink-0">01.</span>
                        <span><strong>Penomoran Sub-Bab Konsisten:</strong> Gunakan struktur heading yang jelas (contoh: <em>1.1 Pengertian Subnetting, 1.2 Tabel Prefix CIDR</em>).</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="font-mono font-bold text-[#008546] shrink-0">02.</span>
                        <span><strong>Definisi Istilah Teknis:</strong> Jabarkan singkatan teknis seperti VLAN, DHCP, DNS, NAT, dan Firewall pada awal pembahasan.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="font-mono font-bold text-[#008546] shrink-0">03.</span>
                        <span><strong>Prosedur Praktikum Berurutan:</strong> Susun langkah konfigurasi MikroTik/Cisco secara berurutan agar AI dapat memandu siswa langkah demi langkah.</span>
                    </li>
                    <li class="flex items-start gap-2.5">
                        <span class="font-mono font-bold text-rose-600 shrink-0">04.</span>
                        <span><strong>Hindari Dokumen Gambar Murni:</strong> Berkas PDF yang seluruhnya berisi foto tanpa teks digital tidak dapat diekstrak ke dalam indeks vektor.</span>
                    </li>
                </ul>
            </div>

            <!-- Manajemen Akun Siswa -->
            <div class="bg-white p-6 rounded-xl border border-slate-200/90 flex flex-col justify-between space-y-4">
                <div class="space-y-3">
                    <h3 class="text-sm font-bold text-slate-900 border-b border-slate-100 pb-3">
                        Manajemen Akun & Kredensial Siswa
                    </h3>
                    <p class="text-xs text-slate-600 leading-relaxed">
                        Melalui menu <strong>Data & Login Siswa</strong>, Guru memiliki kendali penuh atas akses kelas:
                    </p>
                    <ul class="space-y-2 text-xs text-slate-600">
                        <li>• <strong>Tambah Siswa Baru:</strong> Daftarkan nama, NIS, kelas, dan email login siswa.</li>
                        <li>• <strong>Reset Password:</strong> Perbarui kata sandi siswa secara langsung saat praktikum di laboratorium.</li>
                        <li>• <strong>Nonaktifkan Akun:</strong> Cabut akses login siswa tanpa menghapus riwayat nilai dan percakapan.</li>
                    </ul>
                </div>
                <div class="pt-2 border-t border-slate-100">
                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold transition-colors">
                        <span>Buka Menu Data Siswa →</span>
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-premium-layout>
