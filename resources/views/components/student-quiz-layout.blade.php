<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Kuis Objektif | {{ config('app.name', 'E-Modul TKJ') }} - SMK N 1 Kinali</title>

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

    <!-- Sidebar Siswa (Consistent w-72 Across All Student Views) -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Kinali" class="w-10 h-10 object-contain">
                </div>
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

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('profile.*') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-user-gear {{ request()->routeIs('profile.*') ? 'text-[#008546]' : 'text-slate-400' }} text-xs w-4 text-center"></i>
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('siswa.petunjuk') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-circle-question {{ request()->routeIs('siswa.petunjuk') ? 'text-[#008546]' : 'text-slate-400' }} text-xs w-4 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold transition-colors">
                <i class="fa-solid fa-book-bookmark text-[#008546] text-xs w-4 text-center"></i>
                <span>Katalog E-Modul</span>
            </a>

            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold {{ request()->routeIs('siswa.dashboard') ? 'bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900' }} transition-colors">
                <i class="fa-solid fa-robot {{ request()->routeIs('siswa.dashboard') ? 'text-[#008546]' : 'text-slate-400' }} text-xs w-4 text-center"></i>
                <span>Tanya Jawab AI (Chatbot)</span>
            </a>
        </nav>
        
        <!-- User Profile Area -->
        <div class="p-3.5 border-t border-slate-200 bg-white">
            <div class="flex items-center gap-3 p-2 rounded-lg bg-slate-50 border border-slate-200/80">
                @if(auth()->user()?->avatar_url)
                    <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-lg object-cover shrink-0 border border-slate-200">
                @else
                    <div class="w-8 h-8 rounded-lg bg-[#008546] text-white flex items-center justify-center font-bold text-xs shrink-0">
                        {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                    </div>
                @endif
                <div class="flex-1 overflow-hidden">
                    <p class="text-xs font-bold text-slate-800 truncate">{{ auth()->user()?->name ?? 'Pengguna' }}</p>
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

    <!-- Main Content Container -->
    <div class="flex-1 flex flex-col h-full bg-slate-50 overflow-hidden relative">
        
        <!-- Header Topbar -->
        <header class="h-14 bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-8 shrink-0 z-20">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-base"></i>
                </button>
                <div class="flex items-center gap-2.5">
                    <span class="w-1.5 h-4 rounded-xs bg-[#008546]"></span>
                    <h2 class="text-sm font-bold text-slate-900 tracking-tight">Kuis Objektif E-Modul</h2>
                </div>
            </div>

            <a href="{{ route('siswa.modules.index') }}" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200 hover:bg-[#008546] hover:text-white hover:border-[#008546] transition-colors">
                <i class="fa-solid fa-book-bookmark text-xs"></i>
                <span class="hidden sm:inline">Katalog Modul</span>
            </a>
        </header>

        <!-- Main Page Content -->
        <main class="flex-1 overflow-y-auto">
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
