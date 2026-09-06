<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $module->kb_nomor }} - {{ $module->judul }} | SMK N 1 Kinali</title>

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

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs">
                <i class="fa-solid fa-book-bookmark text-sm w-5 text-center"></i>
                <span>Katalog E-Modul (KB 1-3)</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-circle-question text-slate-400 text-sm w-5 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <a href="{{ route('pengembang') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-address-card text-slate-400 text-sm w-5 text-center"></i>
                <span>Profil Pengembang</span>
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
                <div class="w-9 h-9 rounded-xl bg-[#008546] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
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
                    <a href="{{ route('siswa.modules.index') }}" class="text-slate-400 hover:text-slate-700 text-xs font-semibold flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog
                    </a>
                </div>
            </div>

            <!-- Jump to AI Chatbot with this subject -->
            <a href="{{ route('siswa.dashboard') }}?mapel={{ urlencode($module->mapel) }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 hover:bg-[#008546] hover:text-white transition-all shadow-xs">
                <i class="fa-solid fa-robot"></i>
                <span>Tanya AI Soal Materi Ini</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 max-w-5xl mx-auto w-full">
            
            <!-- Module Title Banner -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-50 text-[#008546] border border-emerald-200">
                        <i class="fa-solid fa-layer-group text-[10px]"></i> {{ $module->kb_nomor ?? 'KB 1' }}
                    </span>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-100 px-3 py-1 rounded-xl">
                        {{ $module->mapel }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight leading-snug">
                    {{ $module->judul }}
                </h1>
            </div>

            <!-- 1. TUJUAN PEMBELAJARAN (TP) CARD -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-3 relative overflow-hidden">
                <div class="flex items-center gap-2.5 text-[#008546]">
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center text-sm border border-emerald-200/60">
                        <i class="fa-solid fa-bullseye"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wider">
                        1. Tujuan Pembelajaran (TP)
                    </h3>
                </div>

                <div class="p-4 sm:p-5 bg-slate-50 rounded-2xl border border-slate-200/80 text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $module->tp ?: 'Tujuan Pembelajaran untuk kegiatan belajar ini tercantum lengkap di dalam dokumen modul.' }}
                </div>
            </div>

            <!-- 2. MATERI DOKUMEN (DOWNLOAD FILE ASLI) CARD -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 text-blue-600">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center text-sm border border-blue-200/60">
                        <i class="fa-solid fa-file-lines"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wider">
                        2. Materi Pembelajaran & Dokumen
                    </h3>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-blue-50/50 rounded-2xl border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-sm">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">{{ $module->judul }}</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Format file dokumen resmi dari pengajar TKJ</p>
                        </div>
                    </div>

                    <!-- Tombol Download File Asli (FR-16) -->
                    <a href="{{ route('modules.download', $module->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 flex-shrink-0">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Dokumen Materi</span>
                    </a>
                </div>
            </div>

            <!-- 3. VIDEO PEMBELAJARAN CARD -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 text-rose-600">
                    <div class="w-8 h-8 rounded-xl bg-rose-50 flex items-center justify-center text-sm border border-rose-200/60">
                        <i class="fa-brands fa-youtube"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wider">
                        3. Video Pembelajaran Pendukung
                    </h3>
                </div>

                @if($module->video_url)
                    @php
                        // Cek apakah YouTube URL untuk embed iframe
                        $isYoutube = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $module->video_url, $matches);
                        $youtubeId = $isYoutube ? $matches[1] : null;
                    @endphp

                    @if($youtubeId)
                        <div class="aspect-video w-full rounded-2xl overflow-hidden shadow-md border border-slate-200">
                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $youtubeId }}" title="Video Pembelajaran" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    @endif

                    <div class="flex items-center justify-between p-4 bg-slate-50 rounded-2xl border border-slate-200">
                        <span class="text-xs text-slate-600 truncate flex-1 mr-3">{{ $module->video_url }}</span>
                        <a href="{{ $module->video_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex-shrink-0">
                            Buka di Tab Baru <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                @else
                    <div class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                        <i class="fa-brands fa-youtube text-2xl mb-1 text-slate-300 block"></i>
                        Belum ada video pembelajaran yang disematkan untuk KB ini.
                    </div>
                @endif
            </div>

            <!-- 4. LINK KUIS EVALUASI CARD -->
            <div class="bg-white p-6 sm:p-8 rounded-3xl border-2 border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-2.5 text-purple-600">
                    <div class="w-8 h-8 rounded-xl bg-purple-50 flex items-center justify-center text-sm border border-purple-200/60">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </div>
                    <h3 class="font-bold text-sm text-slate-800 uppercase tracking-wider">
                        4. Kuis Evaluasi Pemahaman
                    </h3>
                </div>

                @if($module->kuis_url)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5 bg-purple-50/50 rounded-2xl border border-purple-100">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Uji Pemahaman Anda</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Kerjakan kuis evaluasi materi {{ $module->kb_nomor }} untuk membuktikan pemahaman materi.</p>
                        </div>

                        <a href="{{ $module->kuis_url }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95 flex-shrink-0">
                            <span>Mulai Kerjakan Kuis</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                        </a>
                    </div>
                @else
                    <div class="p-6 bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-center text-xs text-slate-400">
                        <i class="fa-solid fa-pen-to-square text-2xl mb-1 text-slate-300 block"></i>
                        Belum ada link kuis eksternal untuk KB ini. Anda tetap dapat menggunakan fitur <strong>Latihan Soal AI</strong> di ruang tanya jawab!
                    </div>
                @endif
            </div>

            <!-- Bottom Back & Discussion Link -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 pb-8">
                <a href="{{ route('siswa.modules.index') }}" class="text-xs font-semibold text-slate-500 hover:text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Katalog E-Modul
                </a>

                <a href="{{ route('siswa.dashboard') }}?mapel={{ urlencode($module->mapel) }}" class="inline-flex items-center gap-2 px-5 py-3 bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold rounded-xl shadow-md transition-all">
                    <i class="fa-solid fa-robot text-emerald-400"></i>
                    <span>Tanya AI Mengenai Materi Ini</span>
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
