<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Petunjuk Penggunaan E-Modul - SMK N 1 Kinali</title>

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
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-[#008546] to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20 text-white">
                    <i class="fa-solid fa-graduation-cap text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 tracking-tight leading-tight">E-Modul TKJ</h1>
                    <p class="text-[10px] text-emerald-600 font-semibold tracking-wide uppercase">SMK N 1 Kinali</p>
                </div>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Navigation Section -->
        <nav class="flex-1 overflow-y-auto p-4 space-y-2">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block px-2 mb-1">Menu Utama</span>
            
            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-robot text-[#008546] text-sm w-5 text-center"></i>
                <span>Tanya Jawab AI (Chatbot)</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-book-bookmark text-blue-600 text-sm w-5 text-center"></i>
                <span>Katalog E-Modul</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs">
                <i class="fa-solid fa-circle-question text-sm w-5 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <div class="pt-4 border-t border-slate-100 mt-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block px-2 mb-2">Akun</span>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                    <i class="fa-solid fa-user-gear text-slate-400 text-sm w-5 text-center"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
        </nav>
        
        <!-- User Profile Area -->
        <div class="p-3.5 border-t border-slate-100 bg-white">
            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100">
                @if(auth()->user()->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-xl object-cover shadow-sm shrink-0 border border-slate-200">
                @else
                    <div class="w-9 h-9 rounded-xl bg-[#008546] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] text-emerald-600 font-semibold truncate flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-ping"></span> Siswa TKJ
                    </p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:bg-rose-50 hover:text-rose-600 transition-colors" title="Log Out">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden relative">
        
        <!-- Top Bar Header -->
        <header class="h-16 flex items-center justify-between px-4 sm:px-8 border-b border-slate-200/80 bg-white shrink-0 z-20 shadow-xs">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#008546]"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Petunjuk Penggunaan E-Modul & AI</h2>
                </div>
            </div>

            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 hover:bg-[#008546] hover:text-white transition-all">
                <i class="fa-solid fa-comments"></i>
                <span class="hidden sm:inline">Mulai Tanya AI</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-8 max-w-5xl mx-auto w-full">
            
            <!-- Hero Banner -->
            <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#008546] text-white p-6 sm:p-8 rounded-3xl shadow-lg relative overflow-hidden">
                <div class="relative z-10 max-w-2xl">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/10 text-xs font-semibold mb-3">
                        <i class="fa-solid fa-circle-info"></i> Panduan Belajar Mandiri
                    </span>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Petunjuk Belajar dengan E-Modul Interaktif
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-2 leading-relaxed">
                        Selamat datang di sistem pembelajaran digital TKJ SMK N 1 Kinali. Ikuti panduan alur belajar 4 langkah pada tiap Kegiatan Belajar (KB) dan manfaatkan asisten cerdas AI untuk mendukung pemahaman materi Anda.
                    </p>
                </div>
            </div>

            <!-- 4 Alur Utama Pembelajaran KB (TP -> Materi -> Video -> Kuis) -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-route text-[#008546]"></i> Alur Belajar Tiap Kegiatan Belajar (KB 1, 2, & 3)
                    </h3>
                    <span class="text-xs text-slate-400 font-medium">Langkah Sistematis</span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Step 1: TP -->
                    <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-emerald-300 transition-all">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 text-[#008546] flex items-center justify-center font-extrabold text-sm mb-3 group-hover:scale-110 transition-transform">
                            1
                        </div>
                        <h4 class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-bullseye text-[#008546]"></i> Pahami TP
                        </h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Baca <strong>Tujuan Pembelajaran (TP)</strong> pada setiap KB untuk mengetahui kompetensi dan keahlian yang harus Anda kuasai.
                        </p>
                    </div>

                    <!-- Step 2: Materi -->
                    <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-blue-300 transition-all">
                        <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center font-extrabold text-sm mb-3 group-hover:scale-110 transition-transform">
                            2
                        </div>
                        <h4 class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-book-open text-blue-600"></i> Pelajari Materi
                        </h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Unduh modul PDF/DOCX resmi dari guru. Pelajari teori dan panduan praktikum langkah demi langkah secara mendalam.
                        </p>
                    </div>

                    <!-- Step 3: Video -->
                    <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-rose-300 transition-all">
                        <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center font-extrabold text-sm mb-3 group-hover:scale-110 transition-transform">
                            3
                        </div>
                        <h4 class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                            <i class="fa-brands fa-youtube text-rose-600"></i> Simak Video
                        </h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Klik tautan <strong>Video Pembelajaran</strong> untuk melihat visualisasi topologi, demonstrasi konfigurasi, dan simulasi jaringan.
                        </p>
                    </div>

                    <!-- Step 4: Kuis -->
                    <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden group hover:border-purple-300 transition-all">
                        <div class="w-10 h-10 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center font-extrabold text-sm mb-3 group-hover:scale-110 transition-transform">
                            4
                        </div>
                        <h4 class="font-bold text-sm text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square text-purple-600"></i> Kerjakan Kuis
                        </h4>
                        <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                            Selesaikan <strong>Link Kuis Evaluasi</strong> di akhir KB untuk mengukur dan membuktikan tingkat pemahaman Anda.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Panduan Menggunakan Chatbot RAG AI -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-6">
                <div class="border-b border-slate-100 pb-4">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <i class="fa-solid fa-robot text-[#008546]"></i> Cara Efektif Bertanya kepada AI Assistant
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Asisten ini menjawab berdasarkan materi yang telah diunggah oleh guru Anda di sekolah.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 border border-emerald-200">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">1. Gunakan Filter Mata Pelajaran</h5>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Pilih mata pelajaran yang relevan pada kotak filter sebelum bertanya agar AI fokus mencari konteks materi yang tepat.</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 border border-emerald-200">
                                <i class="fa-solid fa-comment-dots"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">2. Ajukan Pertanyaan Spesifik</h5>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Gunakan kalimat yang jelas. Contoh: <em>"Bagaimana cara menghitung subnet mask untuk prefix /26?"</em> atau <em>"Apa perbedaan switch manageable dan unmanageable?"</em></p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3.5">
                            <div class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 border border-emerald-200">
                                <i class="fa-solid fa-brain"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">3. Fitur Latihan Soal AI</h5>
                                <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">Klik tombol <strong>"Latihan Soal AI"</strong> di ruang chat untuk meminta AI membuatkan soal acak dari modul dan mengoreksi jawaban Anda secara langsung.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Do & Don't Box -->
                    <div class="bg-slate-50 p-5 rounded-2xl border border-slate-200/80 space-y-4">
                        <div>
                            <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-check text-emerald-600"></i> Yang Disarankan (DO)
                            </span>
                            <ul class="text-xs text-slate-600 list-disc pl-5 mt-1.5 space-y-1">
                                <li>Bertanya seputar materi TKJ yang ada di modul.</li>
                                <li>Meminta penjelasan analogi jika ada istilah jaringan yang belum dipahami.</li>
                                <li>Menjawab soal latihan AI secara mandiri sebelum melihat pembahasannya.</li>
                            </ul>
                        </div>

                        <div class="pt-3 border-t border-slate-200">
                            <span class="text-xs font-bold text-rose-700 uppercase tracking-wider flex items-center gap-1.5">
                                <i class="fa-solid fa-circle-xmark text-rose-600"></i> Yang Dihindari (DON'T)
                            </span>
                            <ul class="text-xs text-slate-600 list-disc pl-5 mt-1.5 space-y-1">
                                <li>Menanyakan topik di luar materi pelajaran (AI diprogram untuk menolak).</li>
                                <li>Menggunakan kata-kata kasar atau teks yang tidak pantas.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Button -->
            <div class="flex justify-center pb-8">
                <a href="{{ route('siswa.modules.index') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white font-bold text-xs sm:text-sm px-6 py-3.5 rounded-2xl shadow-md hover:shadow-lg transition-all active:scale-95">
                    <i class="fa-solid fa-book-bookmark"></i>
                    <span>Jelajahi E-Modul & Mulai Belajar Sekarang</span>
                </a>
            </div>

        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('open-sidebar-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const mobileBackdrop = document.getElementById('mobile-backdrop');

            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                mobileBackdrop.classList.toggle('hidden');
            }

            if(openSidebarBtn) openSidebarBtn.addEventListener('click', toggleSidebar);
            if(closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
            if(mobileBackdrop) mobileBackdrop.addEventListener('click', toggleSidebar);
        });
    </script>
</body>
</html>
