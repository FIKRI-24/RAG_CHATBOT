<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Katalog E-Modul Pembelajaran - SMK N 1 Kinali</title>

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
                    <span class="w-2.5 h-2.5 rounded-full bg-[#008546]"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Katalog E-Modul Pembelajaran</h2>
                </div>
            </div>

            <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 hover:bg-[#008546] hover:text-white transition-all">
                <i class="fa-solid fa-robot"></i>
                <span class="hidden sm:inline">Ruang Tanya AI</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 max-w-7xl mx-auto w-full">
            
            <!-- Filters & Search Bar -->
            <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
                <form method="GET" action="{{ route('siswa.modules.index') }}" class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari topik atau materi..." class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 pl-9 pr-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>

                    <!-- Mapel Filter -->
                    <div class="w-full sm:w-52">
                        <select name="mapel" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20" onchange="this.form.submit()">
                            <option value="Semua">Semua Mata Pelajaran</option>
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- KB Filter -->
                    <div class="w-full sm:w-44">
                        <select name="kb_nomor" class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20" onchange="this.form.submit()">
                            <option value="Semua">Semua KB (1, 2, 3)</option>
                            @foreach(['KB 1', 'KB 2', 'KB 3'] as $kb)
                                <option value="{{ $kb }}" {{ request('kb_nomor') == $kb ? 'selected' : '' }}>{{ $kb }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold px-4 py-2.5 rounded-xl text-xs transition-colors">
                        Filter
                    </button>

                    @if(request('search') || (request('mapel') && request('mapel') !== 'Semua') || (request('kb_nomor') && request('kb_nomor') !== 'Semua'))
                        <a href="{{ route('siswa.modules.index') }}" class="text-xs text-rose-600 hover:underline px-2">Reset</a>
                    @endif
                </form>

                <span class="text-xs font-medium text-slate-400">
                    Total: <strong class="text-slate-800">{{ $modules->total() }}</strong> Materi Kegiatan Belajar
                </span>
            </div>

            <!-- Modules Card Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                @forelse($modules as $module)
                <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 flex flex-col justify-between hover:border-emerald-300 hover:shadow-md transition-all group">
                    <div class="space-y-3">
                        <!-- Card Header Badges -->
                        <div class="flex items-center justify-between gap-2">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-xl text-xs font-extrabold bg-emerald-50 text-[#008546] border border-emerald-200/60">
                                <i class="fa-solid fa-layer-group text-[10px]"></i> {{ $module->kb_nomor ?? 'KB 1' }}
                            </span>
                            <span class="text-[11px] font-semibold text-slate-400 bg-slate-100 px-2.5 py-1 rounded-lg truncate max-w-[150px]" title="{{ $module->mapel }}">
                                {{ $module->mapel }}
                            </span>
                        </div>

                        <!-- Title -->
                        <h3 class="font-bold text-slate-900 text-base group-hover:text-[#008546] transition-colors line-clamp-2 leading-snug">
                            {{ $module->judul }}
                        </h3>

                        <!-- TP (Tujuan Pembelajaran) Excerpt -->
                        <div class="p-3 bg-slate-50 rounded-2xl border border-slate-100 text-xs text-slate-600 space-y-1">
                            <span class="font-bold text-[#008546] text-[10px] uppercase tracking-wider block flex items-center gap-1">
                                <i class="fa-solid fa-bullseye text-[9px]"></i> Tujuan Pembelajaran
                            </span>
                            <p class="line-clamp-3 leading-relaxed text-slate-700">
                                {{ $module->tp ?: 'Pelajari materi dasar dan panduan praktikum pada modul ini.' }}
                            </p>
                        </div>

                        <!-- Media Badges Indicator -->
                        <div class="flex items-center gap-2 pt-1">
                            @if($module->video_url)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-100">
                                    <i class="fa-brands fa-youtube"></i> Video
                                </span>
                            @endif
                            @if($module->kuis_url)
                                <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-100">
                                    <i class="fa-solid fa-pen-to-square"></i> Kuis
                                </span>
                            @endif
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                                <i class="fa-solid fa-robot"></i> RAG AI Ready
                            </span>
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="pt-4 border-t border-slate-100 mt-5 flex items-center gap-2">
                        <a href="{{ route('siswa.modules.show', $module->id) }}" class="flex-1 text-center py-2.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl shadow-sm transition-colors">
                            Buka Kegiatan Belajar
                        </a>

                        <!-- Download File Asli (FR-16) -->
                        <a href="{{ route('modules.download', $module->id) }}" class="p-2.5 bg-slate-100 hover:bg-emerald-50 text-slate-600 hover:text-[#008546] rounded-xl border border-slate-200 transition-colors" title="Download Materi Asli (PDF/DOCX)">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="col-span-full py-16 text-center text-slate-400 bg-white rounded-3xl border-2 border-dashed border-slate-200">
                    <i class="fa-solid fa-folder-open text-4xl mb-3 text-slate-300 block"></i>
                    <p class="font-bold text-sm text-slate-600">Belum ada modul yang cocok dengan kriteria pencarian.</p>
                    <p class="text-xs text-slate-400 mt-1">Coba ubah kata kunci atau reset filter mata pelajaran.</p>
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
