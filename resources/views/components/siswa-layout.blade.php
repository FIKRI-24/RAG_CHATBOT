<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="google" content="notranslate">

    <title>{{ config('app.name', 'TKJ AI') }} - Panel Siswa</title>

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
            
            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('siswa.dashboard') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-robot {{ request()->routeIs('siswa.dashboard') ? 'text-[#008546]' : 'text-slate-400' }} text-sm w-5 text-center"></i>
                <span>Tanya Jawab AI (Chatbot)</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('siswa.modules.*') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-book-bookmark {{ request()->routeIs('siswa.modules.*') ? 'text-[#008546]' : 'text-blue-600' }} text-sm w-5 text-center"></i>
                <span>Katalog E-Modul (KB 1-3)</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('siswa.petunjuk') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-circle-question {{ request()->routeIs('siswa.petunjuk') ? 'text-[#008546]' : 'text-slate-400' }} text-sm w-5 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <div class="pt-4 border-t border-slate-100 mt-4">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block px-2 mb-2">Akun</span>
                <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-semibold {{ request()->routeIs('profile.*') ? 'bg-[#008546] text-white shadow-md' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                    <i class="fa-solid fa-user-gear text-sm w-5 text-center {{ request()->routeIs('profile.*') ? 'text-white' : 'text-slate-400' }}"></i>
                    <span>Profil Saya</span>
                </a>
            </div>
        </nav>
        
        <!-- User Profile Area -->
        <div class="p-3.5 border-t border-slate-100 bg-white">
            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 border border-slate-100">
                @if(auth()->user()?->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-xl object-cover shadow-sm shrink-0 border border-slate-200">
                @else
                    <div class="w-9 h-9 rounded-xl bg-[#008546] text-white flex items-center justify-center font-bold text-sm shadow-sm shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()?->name ?? 'Pengguna' }}</p>
                    <p class="text-[10px] text-emerald-600 font-semibold truncate flex items-center gap-1">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span> Siswa TKJ
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
    <div class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden relative">
        
        <!-- Top Bar Header -->
        <header class="h-16 flex items-center justify-between px-4 sm:px-8 border-b border-slate-200/80 bg-white shrink-0 z-20 shadow-xs">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#008546]"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Panel Belajar Siswa</h2>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/60">
                    <i class="fa-solid fa-user-check text-xs"></i> Mode Siswa
                </span>
            </div>
        </header>

        <!-- Main Page Content -->
        <main class="flex-1 p-4 sm:p-6 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    <!-- Drawer Scripts -->
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
