<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petunjuk Siswa - SMK N 1 Kinali</title>

    <!-- Scripts & Styles via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden selection:bg-emerald-200 selection:text-emerald-900">

    <!-- Mobile Overlay Backdrop -->
    <div id="mobile-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar Siswa -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden shrink-0"><img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Kinali" class="w-10 h-10 object-contain"></div>
                <div>
                    <h1 class="font-bold text-slate-900 tracking-tight leading-tight text-sm">E-Modul TKJ</h1>
                    <p class="text-[10px] text-[#008546] font-mono font-semibold tracking-wider uppercase">SMK N 1 Kinali</p>
                </div>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Navigation Section -->
        <nav class="flex-1 overflow-y-auto p-3.5 space-y-1">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block px-2.5 mb-1.5">Menu Utama</span>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-user-gear text-slate-400 text-xs w-4 text-center"></i>
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/80">
                <i class="fa-solid fa-circle-question text-xs w-4 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-book-bookmark text-slate-400 text-xs w-4 text-center"></i>
                <span>Katalog E-Modul</span>
            </a>

            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-robot text-slate-400 text-xs w-4 text-center"></i>
                <span>Tanya Jawab AI (Chatbot)</span>
            </a>
        </nav>
        
        <!-- User Profile Area -->
        <div class="p-3.5 border-t border-slate-200 bg-white">
            <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-200">
                @else
                    <div class="w-8 h-8 rounded-lg bg-[#008546] text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-slate-500 font-medium truncate flex items-center gap-1.5">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Siswa TKJ
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Log Out">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden relative">
        
        <!-- Top Bar Header -->
        <header class="h-14 flex items-center justify-between px-4 sm:px-8 border-b border-slate-200 bg-white shrink-0 z-20">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-4 rounded-xs bg-[#008546]"></span>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Petunjuk Penggunaan E-Modul</h2>
                </div>
            </div>

            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/80 hover:bg-[#008546] hover:text-white transition-all shadow-2xs">
                <i class="fa-solid fa-comments text-xs"></i>
                <span class="hidden sm:inline">Mulai Tanya AI</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 max-w-5xl mx-auto w-full">
            
            <!-- Cyber-Network Glassmorphism Header (Opsi C) -->
            <div class="relative bg-slate-950 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 rounded-3xl border border-emerald-500/20 shadow-2xl shadow-emerald-950/15 overflow-hidden backdrop-blur-xl">
                <!-- Subtle Cyber-Network Circuit & Ambient Glow -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <svg class="absolute inset-0 w-full h-full pointer-events-none" style="opacity: 0.06;" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="cyber-grid-petunjuk" width="32" height="32" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1.5" fill="#34d399" />
                            <path d="M 2 2 L 32 2 M 2 2 L 2 32" stroke="#34d399" stroke-width="0.5" stroke-dasharray="2 4" fill="none" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cyber-grid-petunjuk)" />
                </svg>

                <div class="relative z-10 max-w-2xl space-y-2.5">
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                            <i class="fa-solid fa-network-wired text-[11px]"></i>
                            PANDUAN BELAJAR DIGITAL • SMK N 1 KINALI
                        </span>
                    </div>
                    
                    <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm leading-tight">
                        Alur Pembelajaran & Interaksi AI
                    </h1>
                    
                    <p class="text-slate-300/90 text-xs sm:text-sm leading-relaxed font-medium">
                        Ikuti alur belajar 4 tahap mandiri pada setiap Kegiatan Belajar (KB) dan manfaatkan asisten AI RAG cerdas untuk konsultasi materi jaringan, topologi, dan evaluasi soal.
                    </p>
                </div>
            </div>

            <!-- 4 Alur Utama Pembelajaran KB -->
            <div class="space-y-4">
                <div class="flex items-center justify-between border-b border-slate-200 pb-3">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        4 Tahap Belajar Tiap Kegiatan Belajar (KB)
                    </h3>
                    <span class="text-xs font-semibold text-emerald-800 bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200/80">Tahap Terstruktur</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Step 1: TP -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-emerald-500/40 hover:shadow-md transition-all flex flex-col justify-between group">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center group-hover:scale-105 transition-transform">
                                    01
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Kompetensi</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 group-hover:text-[#008546] transition-colors">
                                Pahami TP
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Baca <strong>Tujuan Pembelajaran (TP)</strong> pada setiap KB untuk mengetahui target kompetensi yang harus dikuasai.
                            </p>
                        </div>
                    </div>

                    <!-- Step 2: Materi -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-emerald-500/40 hover:shadow-md transition-all flex flex-col justify-between group">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center group-hover:scale-105 transition-transform">
                                    02
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Dokumen</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 group-hover:text-[#008546] transition-colors">
                                Pelajari Materi
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Unduh modul PDF/DOCX resmi dari guru. Pelajari teori dan langkah praktikum jaringan secara menyeluruh.
                            </p>
                        </div>
                    </div>

                    <!-- Step 3: Video -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-emerald-500/40 hover:shadow-md transition-all flex flex-col justify-between group">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center group-hover:scale-105 transition-transform">
                                    03
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Visualisasi</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 group-hover:text-[#008546] transition-colors">
                                Simak Video
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Tonton <strong>Video Pembelajaran</strong> untuk melihat visualisasi topologi, demonstrasi konfigurasi, dan simulasi perangkat.
                            </p>
                        </div>
                    </div>

                    <!-- Step 4: Kuis -->
                    <div class="bg-white p-5 rounded-2xl border border-slate-200/90 shadow-xs hover:border-emerald-500/40 hover:shadow-md transition-all flex flex-col justify-between group">
                        <div class="space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center group-hover:scale-105 transition-transform">
                                    04
                                </span>
                                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Evaluasi</span>
                            </div>
                            <h4 class="font-bold text-sm text-slate-900 group-hover:text-[#008546] transition-colors">
                                Kerjakan Kuis
                            </h4>
                            <p class="text-xs text-slate-600 leading-relaxed">
                                Selesaikan <strong>Kuis Objektif</strong> di halaman detail KB untuk menguji pemahaman dan rekap nilai otomatis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panduan Menggunakan Chatbot RAG AI -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-6">
                <div class="border-b border-slate-100 pb-4 flex items-center justify-between">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">
                            Cara Efektif Berkonsultasi dengan Asisten AI
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">Asisten AI berbasis RAG menjawab akurat bersumber langsung dari materi guru Anda.</p>
                    </div>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 flex items-center justify-center text-xs shrink-0">
                        <i class="fa-solid fa-robot"></i>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 mt-0.5 border border-emerald-200/80 flex items-center justify-center">
                                1
                            </span>
                            <div>
                                <h5 class="text-xs font-bold text-slate-900">Pilih Fokus Modul (KB)</h5>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Pilih modul spesifik di filter sidebar agar pencarian materi oleh AI lebih tepat sasaran.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 mt-0.5 border border-emerald-200/80 flex items-center justify-center">
                                2
                            </span>
                            <div>
                                <h5 class="text-xs font-bold text-slate-900">Pertanyaan Teknis Spesifik</h5>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Gunakan istilah jaringan yang jelas, seperti: <em>"Bagaimana cara konfigurasi firewall chain input?"</em></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 mt-0.5 border border-emerald-200/80 flex items-center justify-center">
                                3
                            </span>
                            <div>
                                <h5 class="text-xs font-bold text-slate-900">Gunakan Latihan Soal AI</h5>
                                <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">Klik tombol <strong>"Latihan Soal AI"</strong> di ruang tanya jawab untuk berlatih dan mendapat umpan balik seketika.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Do & Don't Box -->
                    <div class="bg-slate-50/90 p-5 rounded-2xl border border-slate-200/80 space-y-4">
                        <div>
                            <span class="text-xs font-bold text-[#008546] uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-check text-[#008546]"></i> Direkomendasikan
                            </span>
                            <ul class="text-xs text-slate-600 list-disc pl-5 mt-1.5 space-y-1">
                                <li>Bertanya seputar materi TKJ yang tercantum di modul.</li>
                                <li>Meminta penjelasan cara kerja protokol atau konfigurasi router.</li>
                                <li>Mencoba menjawab soal latihan sendiri sebelum melihat pembahasan.</li>
                            </ul>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <span class="text-xs font-bold text-rose-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-xmark text-rose-600"></i> Dihindari
                            </span>
                            <ul class="text-xs text-slate-600 list-disc pl-5 mt-1.5 space-y-1">
                                <li>Menanyakan topik di luar materi kurikulum pembelajaran.</li>
                                <li>Mengirim pesan spam atau kalimat tidak relevan.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-start pb-8">
                <a href="{{ route('siswa.modules.index') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-xs sm:text-sm px-6 py-3 rounded-xl transition-all shadow-sm hover:shadow-md active:scale-95">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Buka Katalog E-Modul Sekarang</span>
                </a>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('open-sidebar-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const backdrop = document.getElementById('mobile-backdrop');

            if (openSidebarBtn && sidebar && backdrop) {
                openSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    backdrop.classList.remove('hidden');
                });
            }

            if (closeSidebarBtn && sidebar && backdrop) {
                closeSidebarBtn.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }

            if (backdrop && sidebar) {
                backdrop.addEventListener('click', function() {
                    sidebar.classList.add('-translate-x-full');
                    backdrop.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>
