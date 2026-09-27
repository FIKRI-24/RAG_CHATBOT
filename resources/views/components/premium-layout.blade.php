<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" content="notranslate">

    <title>{{ config('app.name', 'TKJ AI') }} - Panel Guru</title>

    <!-- Scripts & Styles (bundled via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden selection:bg-emerald-200 selection:text-emerald-900">

    <!-- Mobile Overlay Backdrop -->
    <div id="mobile-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar (Desktop & Mobile Drawer) -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-64 bg-slate-900 text-white flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
        
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-800 bg-slate-900">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-tr from-[#008546] to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-500/20 text-white">
                    <i class="fa-solid fa-chalkboard-user text-sm"></i>
                </div>
                <div>
                    <h1 class="font-bold text-white tracking-tight leading-tight text-sm">Panel Guru TKJ</h1>
                    <p class="text-[10px] text-emerald-400 font-semibold uppercase">SMK N 1 Kinali</p>
                </div>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-white p-1 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Teacher Profile Card -->
        <div class="p-4 border-b border-slate-800 bg-slate-800/50">
            <div class="flex items-center gap-3">
                @if(auth()->user()?->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-10 h-10 rounded-xl object-cover shadow-sm border border-emerald-400/30 shrink-0">
                @else
                    <div class="w-10 h-10 rounded-xl bg-[#008546] text-white flex items-center justify-center font-bold text-sm shadow-sm border border-emerald-400/30 shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'G', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-white truncate">{{ auth()->user()?->name ?? 'Pengajar' }}</p>
                    <p class="text-[10px] text-emerald-400 font-semibold truncate flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span> Pengajar TKJ
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Navigation Links -->
        <nav class="flex-1 p-4 space-y-1.5 overflow-y-auto">
            <a href="{{ route('guru.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.dashboard') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-chart-pie text-sm w-5 text-center"></i>
                <span class="notranslate" translate="no">Dashboard</span>
            </a>

            <a href="{{ route('guru.modules.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.modules.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-book-bookmark text-sm w-5 text-center"></i>
                <span>Kelola Modul RAG</span>
            </a>

            <a href="{{ route('guru.siswa.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.siswa.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-user-graduate text-sm w-5 text-center"></i>
                <span>Data & Login Siswa</span>
            </a>

            <a href="{{ route('guru.quiz-reviews.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.quiz-reviews.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-clipboard-check text-sm w-5 text-center"></i><span>Tinjauan kuis</span>
            </a>

            <a href="{{ route('guru.quiz-recap.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.quiz-recap.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-square-poll-vertical text-sm w-5 text-center"></i>
                <span>Rekap Nilai Kuis</span>
            </a>
            <a href="{{ route('guru.petunjuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('guru.petunjuk') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-circle-question text-sm w-5 text-center"></i>
                <span>Petunjuk Guru</span>
            </a>

            <a href="{{ route('pengembang') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('pengembang') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                <i class="fa-solid fa-address-card text-sm w-5 text-center"></i>
                <span>Profil Pengembang</span>
            </a>

            <div class="pt-4 border-t border-slate-800 mt-4">
                <span class="text-[10px] font-bold text-slate-500 uppercase tracking-wider block px-3 mb-2">Pengaturan</span>
                
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold transition-all {{ request()->routeIs('profile.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-user-gear text-sm w-5 text-center"></i>
                    <span>Profil Saya</span>
                </a>

                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 transition-all text-left">
                        <i class="fa-solid fa-right-from-bracket text-sm w-5 text-center"></i>
                        <span>Keluar (Logout)</span>
                    </button>
                </form>
            </div>
        </nav>

        <!-- Sidebar Footer -->
        <div class="p-4 border-t border-slate-800 text-center">
            <span class="text-[10px] text-slate-500">RAG AI System v2.0 • SMK N 1 Kinali</span>
        </div>
    </aside>

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden relative">
        
        <!-- Header Topbar -->
        <header class="h-16 bg-white border-b border-slate-200/80 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20 shadow-xs">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#008546]"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Panel Kendali Guru</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/60">
                    <i class="fa-solid fa-shield-halved text-xs"></i> Mode Pengajar
                </span>
            </div>
        </header>

        <!-- Main Page Content -->
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    <!-- Layout Drawer Scripts -->
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
