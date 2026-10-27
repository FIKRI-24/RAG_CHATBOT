<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog E-Modul - SMK N 1 Kinali</title>

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
                    <p class="text-[10px] text-[#008546] font-semibold tracking-wider uppercase">SMK N 1 Kinali</p>
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

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-circle-question text-slate-400 text-xs w-4 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/80">
                <i class="fa-solid fa-book-bookmark text-xs w-4 text-center"></i>
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
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Katalog E-Modul Pembelajaran</h2>
                </div>
            </div>

            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/80 hover:bg-[#008546] hover:text-white transition-all shadow-2xs">
                <i class="fa-solid fa-robot text-xs"></i>
                <span class="hidden sm:inline">Ruang Tanya AI</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 max-w-7xl mx-auto w-full">
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <!-- Cyber-Network Glassmorphism Hero (Opsi C) -->
            <div class="relative bg-slate-950 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 rounded-3xl border border-emerald-500/20 shadow-2xl shadow-emerald-950/15 overflow-hidden backdrop-blur-xl">
                <!-- Subtle Cyber-Network Circuit & Ambient Glow -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <svg class="absolute inset-0 w-full h-full pointer-events-none" style="opacity: 0.06;" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="cyber-grid-katalog" width="32" height="32" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1.5" fill="#34d399" />
                            <path d="M 2 2 L 32 2 M 2 2 L 2 32" stroke="#34d399" stroke-width="0.5" stroke-dasharray="2 4" fill="none" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cyber-grid-katalog)" />
                </svg>

                <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                    <div class="max-w-2xl space-y-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/15 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                            <i class="fa-solid fa-server text-[10px]"></i>
                            KURIKULUM TEKNIK KOMPUTER & JARINGAN
                        </span>
                        <h1 class="text-xl sm:text-3xl font-extrabold tracking-tight text-white drop-shadow-sm leading-tight">
                            E-Modul Kegiatan Belajar (KB)
                        </h1>
                        <p class="text-slate-300/90 text-xs sm:text-sm leading-relaxed font-medium">
                            Pilih materi modul di bawah untuk mulai belajar, mengunduh file resmi, menyimak video penjelasan, atau berkonsultasi langsung dengan AI RAG.
                        </p>
                    </div>

                    <!-- Telemetry Stats -->
                    <div class="flex items-center gap-3 shrink-0">
                        <div class="p-3.5 px-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md text-center">
                            <p class="text-xs text-slate-300">Total Materi</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-white mt-0.5">{{ $modules->total() }} <span class="text-xs font-medium text-emerald-400">KB</span></p>
                        </div>
                        <div class="p-3.5 px-4 rounded-2xl bg-white/5 border border-white/10 backdrop-blur-md text-center">
                            <p class="text-xs text-slate-300">AI Terindeks</p>
                            <p class="text-xl sm:text-2xl font-extrabold text-emerald-400 mt-0.5">100%</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters & Search Bar -->
            <div class="bg-white p-4 rounded-2xl border border-slate-200/90 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('siswa.modules.index') }}" class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari topik atau materi..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 pl-9 pr-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/15 transition-all">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>

                    <!-- Mapel Filter -->
                    <div class="w-full sm:w-56">
                        <select name="mapel" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/15 transition-all" onchange="this.form.submit()">
                            <option value="Semua">Semua Mata Pelajaran</option>
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KB Filter -->
                    <div class="w-full sm:w-48">
                        <select name="kb_nomor" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/15 transition-all" onchange="this.form.submit()">
                            <option value="Semua">Semua Kegiatan Belajar</option>
                            @foreach($kbList as $kb)
                                <option value="{{ $kb }}" {{ request('kb_nomor') == $kb ? 'selected' : '' }}>{{ $kb }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-[#008546] hover:bg-[#00703c] text-white font-bold px-4 py-2.5 rounded-xl text-xs transition-all shadow-xs">
                        Terapkan
                    </button>

                    @if(request('search') || (request('mapel') && request('mapel') !== 'Semua') || (request('kb_nomor') && request('kb_nomor') !== 'Semua'))
                        <a href="{{ route('siswa.modules.index') }}" class="text-xs font-semibold text-rose-600 hover:underline px-2">Reset Filter</a>
                    @endif
                </form>

                <span class="text-xs font-semibold text-slate-500">
                    Menampilkan <strong class="text-slate-900">{{ $modules->total() }}</strong> Modul KB
                </span>
            </div>

            <!-- Modules Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse($modules as $module)
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 flex flex-col justify-between hover:border-emerald-500/40 hover:shadow-lg hover:shadow-emerald-950/5 transition-all duration-200 group">
                    <div class="space-y-3">
                        <!-- Card Header Badges -->
                        <div class="flex items-center justify-between gap-2 pb-2.5 border-b border-slate-100">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-extrabold bg-emerald-50 text-[#008546] border border-emerald-200/80">
                                {{ $module->kb_nomor ?? 'KB 1' }}
                            </span>
                            <span class="text-[11px] font-medium text-slate-500 truncate max-w-[170px]" title="{{ $module->mapel }}">
                                {{ $module->mapel }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base group-hover:text-[#008546] transition-colors line-clamp-2 leading-snug">
                            {{ $module->judul }}
                        </h3>

                        <!-- TP (Tujuan Pembelajaran) Excerpt (Clean neutral card, no green line) -->
                        <div class="bg-slate-50/90 p-3 rounded-xl border border-slate-200/60 text-xs text-slate-600 space-y-1">
                            <span class="font-bold text-slate-800 text-[10px] uppercase tracking-wider block">
                                Target Pembelajaran
                            </span>
                            <p class="line-clamp-3 leading-relaxed text-slate-600">
                                {{ $module->tp ?: 'Pelajari materi dasar dan panduan praktikum pada modul ini.' }}
                            </p>
                        </div>

                        <!-- Media Badges Indicator -->
                        <div class="flex flex-wrap items-center gap-1.5 pt-1">
                            @if($module->video_url)
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                    <i class="fa-solid fa-play text-[9px] text-emerald-600"></i> Video
                                </span>
                            @endif
                            @if($module->kuis_url || $module->quiz?->is_published)
                                <span class="inline-flex items-center gap-1 text-[10px] font-medium text-slate-600 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200">
                                    <i class="fa-solid fa-list-check text-[9px] text-emerald-600"></i> Kuis
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> AI Ready
                            </span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-4 border-t border-slate-100 mt-4 flex items-center gap-2">
                        <a href="{{ route('siswa.modules.show', $module->id) }}" class="flex-1 text-center py-2.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl transition-all shadow-xs active:scale-95">
                            Buka Kegiatan Belajar
                        </a>

                        <!-- Download File Asli (FR-16) -->
                        <a href="{{ route('modules.download', $module->id) }}" class="p-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl border border-slate-200 transition-colors" title="Download Materi Asli (PDF/DOCX)">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-14 text-center text-slate-500 bg-white rounded-2xl border border-slate-200 shadow-xs">
                    <i class="fa-solid fa-magnifying-glass text-3xl text-slate-300 mb-3 block"></i>
                    <p class="font-bold text-sm text-slate-700">Belum ada modul yang cocok dengan kriteria pencarian.</p>
                    <p class="text-xs text-slate-500 mt-1">Ubah kata kunci atau reset filter mata pelajaran.</p>
                </div>
                @endforelse
            </div>

            <!-- Pagination -->
            <div class="pt-4">
                {{ $modules->links() }}
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
