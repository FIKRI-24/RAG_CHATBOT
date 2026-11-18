<x-premium-layout>
    <div
        class="max-w-6xl mx-auto pb-14"
        x-data="{
            step: 1,
            copiedTp: false,
            openFaq: 1,
            sampleTp: 'Setelah mempelajari modul ini, peserta didik mampu:\n1) Menjelaskan prinsip kerja jaringan nirkabel (IEEE 802.11).\n2) Mengonfigurasi Access Point dan keamanan WPA2 pada topologi TKJ.\n3) Menganalisis gangguan sinyal serta melakukan perbaikan koneksi.'
        }"
    >
        <!-- Editorial Header -->
        <header class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8 mb-8">
            <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
                <div class="max-w-2xl space-y-2.5">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#008546]">
                        <span class="w-5 h-0.5 bg-[#008546] rounded-full"></span>
                        <span>Panduan Pengajar • SMK Negeri 1 Kinali</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-snug">
                        Petunjuk Penggunaan Sistem E-Modul & AI RAG
                    </h1>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Panduan ringkas menyusun modul berbasis 5 Elemen TKJ, menuliskan Tujuan Pembelajaran (TP), melengkapi media ajar, hingga memastikan materi siap dijawab asisten AI.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-xs">
                        <i class="fa-solid fa-plus"></i>
                        <span>Upload Modul Baru</span>
                    </a>
                    <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center gap-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-200 transition-colors">
                        <span>Katalog Modul Guru</span>
                    </a>
                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-200 transition-colors">
                        <span>Kelola Akun Siswa</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- Two-Column Documentation Layout -->
        <div class="flex flex-col lg:flex-row gap-8 items-start">

            <!-- Left Sticky Navigation -->
            <aside class="w-full lg:w-56 shrink-0 lg:sticky lg:top-2 space-y-6">
                <nav aria-label="Daftar isi panduan" class="space-y-1">
                    <p class="px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-400 mb-2">Daftar Isi</p>
                    <a href="#alur-kerja" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-white hover:text-[#008546] transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-[#008546]"></span>
                        <span>Alur Kerja Guru</span>
                    </a>
                    <a href="#standar-dokumen" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white hover:text-[#008546] transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        <span>Standar Dokumen AI</span>
                    </a>
                    <a href="#status-ai" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white hover:text-[#008546] transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        <span>Status Indeks AI</span>
                    </a>
                    <a href="#tanya-jawab" class="flex items-center gap-2.5 px-3 py-2 rounded-lg text-xs font-semibold text-slate-600 hover:bg-white hover:text-[#008546] transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span>
                        <span>Tanya Jawab & Siswa</span>
                    </a>
                </nav>

                <div class="hidden lg:block p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/70 space-y-2">
                    <p class="text-xs font-bold text-slate-900">Butuh cek modul gagal?</p>
                    <p class="text-[11px] text-slate-600 leading-relaxed">
                        Buka tabel modul lalu klik detail modul untuk menjalankan ulang ekstraksi AI.
                    </p>
                    <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center gap-1.5 text-[11px] font-bold text-[#008546] hover:underline pt-1">
                        <span>Buka Daftar Modul</span>
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </aside>

            <!-- Right Flowing Editorial Content -->
            <div class="flex-1 min-w-0 space-y-10">

                <!-- 1. Interactive 4-Step Pipeline -->
                <section id="alur-kerja" class="scroll-mt-6 space-y-5">
                    <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-1 border-b border-slate-200 pb-3">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Alur Kerja Guru (4 Tahapan Utama)</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Klik salah satu tahapan di bawah untuk melihat rincian pengisian dan contohnya.</p>
                        </div>
                        <span class="text-xs font-medium text-slate-500">Langkah <strong class="text-slate-900" x-text="'0' + step">01</strong> dari 04</span>
                    </div>

                    <!-- Connected Pipeline Selector -->
                    <div class="relative grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                        <!-- Step 1 -->
                        <button
                            type="button"
                            @click="step = 1"
                            :aria-pressed="step === 1"
                            :class="step === 1 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                            class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                        >
                            <div class="flex items-center justify-between w-auto sm:w-full">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                                    <!-- Custom Icon: Module Packet Uplink -->
                                    <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M9 9.5C9 7.84315 10.3431 6.5 12 6.5H20.5L26.5 12.5V26.5C26.5 28.1569 25.1569 29.5 23.5 29.5H12C10.3431 29.5 9 28.1569 9 26.5V9.5Z" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65" stroke-linejoin="round"/>
                                        <path d="M20.5 6.5V11C20.5 11.8284 21.1716 12.5 22 12.5H26.5" fill="#A7F3D0"/>
                                        <path d="M20.5 6.5V11C20.5 11.8284 21.1716 12.5 22 12.5H26.5" stroke="#0F172A" stroke-width="1.65" stroke-linejoin="round"/>
                                        <path d="M17.75 23.5V14.5M17.75 14.5L14 18.25M17.75 14.5L21.5 18.25" stroke="#008546" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M13 25.75H22.5" stroke="#008546" stroke-width="1.65" stroke-linecap="round" stroke-dasharray="2.5 2.5"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 1 ? 'text-[#008546]' : 'text-slate-400'">01</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900">Unggah Materi</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Elemen, Topik, KB & PDF/DOCX</div>
                            </div>
                        </button>

                        <!-- Step 2 -->
                        <button
                            type="button"
                            @click="step = 2"
                            :aria-pressed="step === 2"
                            :class="step === 2 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                            class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                        >
                            <div class="flex items-center justify-between w-auto sm:w-full">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                                    <!-- Custom Icon: Curriculum Blueprint & Milestone -->
                                    <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="7" y="7" width="18" height="22" rx="3" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65"/>
                                        <path d="M11 13H19M11 17.5H17M11 22H15" stroke="#0F172A" stroke-width="1.65" stroke-linecap="round"/>
                                        <circle cx="24.5" cy="22.5" r="5.5" fill="#A7F3D0" stroke="#008546" stroke-width="1.65"/>
                                        <path d="M22.2 22.6L23.8 24.2L27 21" stroke="#008546" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M11 9.5L13.5 7H18.5L21 9.5" stroke="#008546" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 2 ? 'text-[#008546]' : 'text-slate-400'">02</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900">Rumuskan TP</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Target kompetensi siswa</div>
                            </div>
                        </button>

                        <!-- Step 3 -->
                        <button
                            type="button"
                            @click="step = 3"
                            :aria-pressed="step === 3"
                            :class="step === 3 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                            class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                        >
                            <div class="flex items-center justify-between w-auto sm:w-full">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                                    <!-- Custom Icon: Dual Video Frame & Quiz Matrix -->
                                    <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <rect x="6" y="7.5" width="18.5" height="14" rx="3" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65"/>
                                        <path d="M13.25 11.75L18.5 14.5L13.25 17.25V11.75Z" fill="#008546" stroke="#008546" stroke-width="1.4" stroke-linejoin="round"/>
                                        <rect x="16" y="17.5" width="14" height="11.5" rx="2.5" fill="#FFFFFF" stroke="#0F172A" stroke-width="1.65"/>
                                        <rect x="18.75" y="20.25" width="3.5" height="3.5" rx="0.8" fill="#A7F3D0" stroke="#008546" stroke-width="1.2"/>
                                        <path d="M24 21.5H27.25M18.75 26H27.25" stroke="#0F172A" stroke-width="1.5" stroke-linecap="round"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 3 ? 'text-[#008546]' : 'text-slate-400'">03</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900">Video & Kuis</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Praktikum visual & evaluasi</div>
                            </div>
                        </button>

                        <!-- Step 4 -->
                        <button
                            type="button"
                            @click="step = 4"
                            :aria-pressed="step === 4"
                            :class="step === 4 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                            class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                        >
                            <div class="flex items-center justify-between w-auto sm:w-full">
                                <div class="w-10 h-10 rounded-xl bg-[#008546] border border-emerald-700 flex items-center justify-center shrink-0" aria-hidden="true">
                                    <!-- Custom Icon: Semantic Vector Network & RAG Core -->
                                    <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M18 8.5L26 13.25V22.75L18 27.5L10 22.75V13.25L18 8.5Z" stroke="#A7F3D0" stroke-width="1.5" stroke-linejoin="round"/>
                                        <path d="M18 8.5V14.5M26 13.25L21 16.25M26 22.75L21 19.75M18 27.5V21.5M10 22.75L15 19.75M10 13.25L15 16.25" stroke="#6EE7B7" stroke-width="1.4" stroke-linecap="round"/>
                                        <path d="M18 14L21.5 18L18 22L14.5 18L18 14Z" fill="#FFFFFF"/>
                                        <circle cx="18" cy="8.5" r="2" fill="#FFFFFF"/>
                                        <circle cx="26" cy="13.25" r="2" fill="#FFFFFF"/>
                                        <circle cx="26" cy="22.75" r="2" fill="#FFFFFF"/>
                                        <circle cx="18" cy="27.5" r="2" fill="#FFFFFF"/>
                                        <circle cx="10" cy="22.75" r="2" fill="#FFFFFF"/>
                                        <circle cx="10" cy="13.25" r="2" fill="#FFFFFF"/>
                                    </svg>
                                </div>
                                <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 4 ? 'text-[#008546]' : 'text-slate-400'">04</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-slate-900">AI RAG Indexing</div>
                                <div class="text-[11px] text-slate-500 mt-0.5">Ekstraksi & indeks vektor</div>
                            </div>
                        </button>
                    </div>

                    <!-- Focused Detail Panel (Switches cleanly per selected step) -->
                    <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6">
                        <!-- Panel 1 -->
                        <div x-show="step === 1" class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base font-bold text-slate-900">1. Unggah Materi & Identitas Kurikulum</h3>
                                <a href="{{ route('guru.modules.create') }}" class="text-xs font-semibold text-[#008546] hover:underline">Buka Form Upload →</a>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Unggah berkas materi resmi dalam format <strong>PDF</strong> atau <strong>DOCX</strong> (maksimal 10 MB). Berkas tersimpan aman di storage privat, dapat diunduh siswa, dan menjadi sumber utama jawaban asisten AI.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-2 border-t border-slate-100 text-xs">
                                <div>
                                    <p class="font-bold text-slate-800">5 Elemen TKJ</p>
                                    <p class="text-slate-500 mt-0.5 leading-relaxed">Pilih Elemen 1 s.d. Elemen 5 Kurikulum Merdeka agar katalog tertata rapi.</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">Topik Materi (Opsional)</p>
                                    <p class="text-slate-500 mt-0.5 leading-relaxed">Contoh: <em>Instalasi Jaringan Nirkabel</em> untuk mengelompokkan beberapa KB.</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">Kode Kegiatan Belajar</p>
                                    <p class="text-slate-500 mt-0.5 leading-relaxed">Gunakan format konsisten seperti <strong>KB 1</strong>, <strong>KB 2</strong>, atau <strong>KB 3</strong>.</p>
                                </div>
                            </div>
                        </div>

                        <!-- Panel 2 -->
                        <div x-show="step === 2" x-cloak class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base font-bold text-slate-900">2. Rumuskan Tujuan Pembelajaran (TP)</h3>
                                <button
                                    type="button"
                                    @click="navigator.clipboard?.writeText(sampleTp); copiedTp = true; setTimeout(() => copiedTp = false, 2000)"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold border transition-colors"
                                    :class="copiedTp ? 'bg-emerald-50 border-emerald-300 text-[#008546]' : 'bg-slate-50 hover:bg-slate-100 border-slate-200 text-slate-700'"
                                >
                                    <i class="fa-regular" :class="copiedTp ? 'fa-circle-check' : 'fa-copy'"></i>
                                    <span x-text="copiedTp ? 'Template TP Tersalin' : 'Salin Contoh TP'">Salin Contoh TP</span>
                                </button>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Tuliskan <strong>Tujuan Pembelajaran (TP)</strong> dengan kata kerja operasional yang terukur sesuai Kurikulum Merdeka. TP ditampilkan di kartu katalog dan halaman belajar siswa sebagai acuan kompetensi.
                            </p>
                            <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 text-xs text-slate-700 leading-relaxed whitespace-pre-line font-medium" x-text="sampleTp"></div>
                        </div>

                        <!-- Panel 3 -->
                        <div x-show="step === 3" x-cloak class="space-y-4">
                            <h3 class="text-base font-bold text-slate-900">3. Lengkapi Video Pembelajaran & Kuis Evaluasi</h3>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Bagian ini opsional, namun sangat membantu siswa memahami langkah konfigurasi perangkat jaringan dan menguji pemahaman setelah membaca modul.
                            </p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-slate-100 text-xs">
                                <div>
                                    <p class="font-bold text-slate-800">Tautan Video (YouTube / Google Drive)</p>
                                    <p class="text-slate-500 mt-0.5 leading-relaxed">Anda bisa menambahkan video utama dan beberapa video pendukung (demonstrasi MikroTik, crimping, atau Packet Tracer).</p>
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">Kuis Objektif Terintegrasi</p>
                                    <p class="text-slate-500 mt-0.5 leading-relaxed">Susun soal pilihan ganda langsung melalui tombol <strong>Kelola Kuis</strong> di daftar modul (1 kali kesempatan pengerjaan per siswa dengan rekap otomatis).</p>
                                </div>
                            </div>
                        </div>

                        <!-- Panel 4 -->
                        <div x-show="step === 4" x-cloak class="space-y-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="text-base font-bold text-slate-900">4. Pemrosesan Otomatis AI RAG Indexing</h3>
                                <a href="{{ route('guru.modules.index') }}" class="text-xs font-semibold text-[#008546] hover:underline">Pantau Status di Tabel →</a>
                            </div>
                            <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                                Setelah modul disimpan, worker latar belakang mengekstrak teks dokumen, memecahnya menjadi potongan paragraf (<em>chunking</em>), dan membuat vektor semantik agar jawaban AI tetap berlandaskan materi guru.
                            </p>
                            <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-100 text-xs text-slate-600">
                                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-medium">1. Ekstrak Teks PDF/DOCX</span>
                                <span aria-hidden="true" class="text-slate-400">→</span>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-medium">2. Pemecahan Paragraf</span>
                                <span aria-hidden="true" class="text-slate-400">→</span>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-50 border border-slate-200 font-medium">3. Embedding Vektor</span>
                                <span aria-hidden="true" class="text-slate-400">→</span>
                                <span class="px-2.5 py-1 rounded-lg bg-emerald-50 border border-emerald-200 font-semibold text-[#008546]">4. Siap Ditanya Siswa</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- 2. Show, Don't Tell: Standar Dokumen -->
                <section id="standar-dokumen" class="scroll-mt-6 space-y-4 pt-2 border-t border-slate-200">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Standar Dokumen Agar Jawaban AI Akurat</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Kualitas jawaban chatbot sangat bergantung pada kerapian teks di dalam file PDF atau DOCX yang diunggah.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="bg-white rounded-xl border border-emerald-200/80 p-5 space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold text-[#008546]">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Disarankan (Terbaca Akurat)</span>
                            </div>
                            <ul class="space-y-2 text-xs text-slate-600 leading-relaxed">
                                <li>• File PDF hasil ekspor langsung dari Word/Docs (teks bisa diblok/disalin) atau file <strong>.docx</strong>.</li>
                                <li>• Sub-bab bernomor jelas (contoh: <em>1.1 Pengertian VLAN, 1.2 Mode Port Switch</em>).</li>
                                <li>• Singkatan teknis (DHCP, NAT, OSPF, WPA2) dijelaskan kepanjangan dan fungsinya di awal materi.</li>
                                <li>• Langkah praktikum ditulis berurutan dari langkah 1 hingga verifikasi hasil.</li>
                            </ul>
                        </div>

                        <div class="bg-white rounded-xl border border-rose-200/80 p-5 space-y-2.5">
                            <div class="flex items-center gap-2 text-xs font-bold text-rose-700">
                                <i class="fa-solid fa-circle-xmark"></i>
                                <span>Hindari (Berisiko Gagal Ekstrak)</span>
                            </div>
                            <ul class="space-y-2 text-xs text-slate-600 leading-relaxed">
                                <li>• File PDF hasil foto kamera HP atau pindaian (<em>scan</em>) gambar tanpa lapisan teks OCR.</li>
                                <li>• Materi praktikum yang seluruh penjelasannya hanya ditaruh dalam gambar tangkapan layar tanpa keterangan teks.</li>
                                <li>• File terproteksi kata sandi (<em>password-protected PDF</em>) atau rusak saat disimpan.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- 3. Status Reference Table -->
                <section id="status-ai" class="scroll-mt-6 space-y-4 pt-2 border-t border-slate-200">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">Memahami Status Indeks AI</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Arti label status yang muncul pada tabel Manajemen Modul beserta langkah tindak lanjutnya.</p>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200/90 overflow-hidden">
                        <table class="min-w-full divide-y divide-slate-100 text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-semibold text-left">
                                <tr>
                                    <th class="px-5 py-3 w-36">Status di Tabel</th>
                                    <th class="px-5 py-3">Arti Sistem</th>
                                    <th class="px-5 py-3">Tindakan Guru</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                <tr>
                                    <td class="px-5 py-3.5 align-top">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                    </td>
                                    <td class="px-5 py-3.5 align-top leading-relaxed">
                                        Ekstraksi dan embedding vektor selesai. Modul tampil di katalog siswa dan siap dipakai Chatbot AI.
                                    </td>
                                    <td class="px-5 py-3.5 align-top text-slate-500">Tidak perlu tindakan.</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3.5 align-top">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-blue-100 text-blue-800">Diproses</span>
                                    </td>
                                    <td class="px-5 py-3.5 align-top leading-relaxed">
                                        Worker sedang membaca isi dokumen dan menyusun indeks vektor (umumnya 10–60 detik).
                                    </td>
                                    <td class="px-5 py-3.5 align-top text-slate-500">Tunggu sebentar lalu muat ulang halaman.</td>
                                </tr>
                                <tr>
                                    <td class="px-5 py-3.5 align-top">
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                    </td>
                                    <td class="px-5 py-3.5 align-top leading-relaxed">
                                        File gagal dibaca (biasanya PDF hasil scan gambar) atau koneksi layanan embedding sempat terputus.
                                    </td>
                                    <td class="px-5 py-3.5 align-top">
                                        Buka detail modul → klik <strong>Coba Index Ulang</strong>, atau ganti dengan PDF teks asli.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- 4. FAQ Accordion & Student Account Access -->
                <section id="tanya-jawab" class="scroll-mt-6 space-y-4 pt-2 border-t border-slate-200">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Tanya Jawab & Pengelolaan Siswa</h2>
                            <p class="text-xs text-slate-500 mt-0.5">Pertanyaan yang paling sering ditemui saat mengoperasikan sistem di kelas.</p>
                        </div>
                        <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#008546] hover:underline">
                            <span>Buka Menu Data Siswa</span>
                            <span aria-hidden="true">→</span>
                        </a>
                    </div>

                    <div class="bg-white rounded-xl border border-slate-200/90 divide-y divide-slate-100">
                        <!-- FAQ 1 -->
                        <div>
                            <button type="button" @click="openFaq = openFaq === 1 ? 0 : 1" class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 text-xs sm:text-sm font-bold text-slate-900 hover:bg-slate-50/70 transition-colors">
                                <span>Kapan perlu menekan tombol "Coba Index Ulang"?</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="openFaq === 1 ? 'rotate-180 text-[#008546]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 1" class="px-5 pb-4 text-xs text-slate-600 leading-relaxed">
                                Gunakan tombol <strong>Coba Index Ulang</strong> di halaman detail modul jika status modul menjadi <strong>Gagal</strong> karena gangguan jaringan sementara, atau tertahan di status Pending terlalu lama. Anda tidak perlu menghapus dan mengunggah ulang file yang sama.
                            </div>
                        </div>

                        <!-- FAQ 2 -->
                        <div>
                            <button type="button" @click="openFaq = openFaq === 2 ? 0 : 2" class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 text-xs sm:text-sm font-bold text-slate-900 hover:bg-slate-50/70 transition-colors">
                                <span>Jika memperbarui file materi, apakah data kuis dan indeks lama langsung hilang?</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="openFaq === 2 ? 'rotate-180 text-[#008546]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 2" x-cloak class="px-5 pb-4 text-xs text-slate-600 leading-relaxed">
                                Tidak. Soal kuis tetap tersimpan, dan indeks AI lama tetap melayani siswa selama file baru sedang diproses. Indeks baru baru menggantikan indeks lama setelah proses ekstraksi file baru selesai tanpa error.
                            </div>
                        </div>

                        <!-- FAQ 3 -->
                        <div>
                            <button type="button" @click="openFaq = openFaq === 3 ? 0 : 3" class="w-full px-5 py-4 text-left flex items-center justify-between gap-4 text-xs sm:text-sm font-bold text-slate-900 hover:bg-slate-50/70 transition-colors">
                                <span>Bagaimana cara menambah siswa atau mereset password siswa yang lupa?</span>
                                <i class="fa-solid fa-chevron-down text-xs text-slate-400 transition-transform" :class="openFaq === 3 ? 'rotate-180 text-[#008546]' : ''"></i>
                            </button>
                            <div x-show="openFaq === 3" x-cloak class="px-5 pb-4 text-xs text-slate-600 leading-relaxed">
                                Masuk ke menu <strong>Data & Login Siswa</strong> untuk menambah akun (nama, NIS, kelas, email), memperbarui password siswa secara langsung saat praktikum di lab, atau menonaktifkan akun sementara tanpa menghapus riwayat nilai kuis dan percakapan AI.
                            </div>
                        </div>
                    </div>
                </section>

            </div>
        </div>
    </div>
</x-premium-layout>
