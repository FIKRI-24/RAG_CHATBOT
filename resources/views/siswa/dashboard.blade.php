<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>E-Modul TKJ - SMK N 1 Kinali</title>

    <!-- Scripts & Styles (bundled via Vite) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])


    <style>
        body { font-family: 'Inter', sans-serif; }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background-color: #94a3b8; }

        /* Accessibility & Font Size Controls */
        :root {
            --chat-base-size: 1.25rem;      /* ~20px base for AI answers (mudah dibaca siswa) */
            --chat-user-size: 1.125rem;     /* ~18px for user questions */
            --chat-input-size: 1.125rem;    /* ~18px for input bar */
            --chat-details-size: 1rem;      /* ~16px for sources accordion */
            --chat-line-height: 1.88;
        }

        body.font-size-standard {
            --chat-base-size: 1.0625rem;
            --chat-user-size: 1rem;
            --chat-input-size: 1rem;
            --chat-details-size: 0.875rem;
            --chat-line-height: 1.75;
        }

        body.font-size-large { /* DEFAULT - Jelas, Besar & Nyaman Dibaca */
            --chat-base-size: 1.25rem;      /* 20px base */
            --chat-user-size: 1.125rem;     /* 18px */
            --chat-input-size: 1.125rem;    /* 18px */
            --chat-details-size: 1rem;      /* 16px */
            --chat-line-height: 1.88;
        }

        body.font-size-xl { /* EKSTRA BESAR - Sangat Ramah Pembaca / Penglihatan */
            --chat-base-size: 1.4rem;       /* 22.4px base */
            --chat-user-size: 1.25rem;      /* 20px */
            --chat-input-size: 1.2rem;      /* 19.2px */
            --chat-details-size: 1.1rem;    /* 17.6px */
            --chat-line-height: 1.95;
        }

        /* User Message Bubble Typography */
        .chat-user-bubble {
            font-size: var(--chat-user-size, 1.125rem);
            line-height: 1.65;
        }

        /* Input typography */
        #pertanyaan {
            font-size: var(--chat-input-size, 1.125rem);
        }

        /* Sources text & excerpt */
        .chat-sources-block {
            font-size: var(--chat-details-size, 1rem);
        }
        .source-excerpt {
            border: 1px solid #e2e8f0 !important;
            border-left: 1px solid #e2e8f0 !important;
            background-color: #ffffff !important;
            font-style: normal !important;
            font-family: inherit !important;
            color: #334155 !important;
        }

        /* Prose Markdown Styling (AI Responses) */
        .prose {
            font-size: var(--chat-base-size, 1.25rem);
            line-height: var(--chat-line-height, 1.88);
            color: #1e293b;
            overflow-wrap: break-word;
        }
        @media (min-width: 640px) {
            .prose {
                font-size: calc(var(--chat-base-size, 1.25rem) * 1.04);
            }
        }
        .prose p { margin-bottom: 1.15rem; line-height: inherit; }
        .prose p:last-child { margin-bottom: 0; }
        .prose strong { font-weight: 700; color: #0f172a; }
        .prose ul { list-style-type: disc; padding-left: 1.6rem; margin-bottom: 1.1rem; }
        .prose ol { list-style-type: decimal; padding-left: 1.6rem; margin-bottom: 1.1rem; }
        .prose li { margin-bottom: 0.6rem; line-height: inherit; }
        .prose li::marker { color: #008546; font-weight: bold; }
        .prose h1, .prose h2, .prose h3, .prose h4 { font-weight: 700; line-height: 1.4; margin: 1.4rem 0 0.8rem; color: #0f172a; }
        .prose h1 { font-size: 1.55em; border-bottom: 2px solid #e2e8f0; padding-bottom: 0.4rem; }
        .prose h2 { font-size: 1.35em; border-bottom: 1px solid #e2e8f0; padding-bottom: 0.3rem; }
        .prose h3 { font-size: 1.2em; color: #008546; }
        .prose h4 { font-size: 1.1em; }
        .prose blockquote {
            border-left: 4px solid #008546;
            background-color: #f8fafc;
            padding: 0.85rem 1.25rem;
            border-radius: 0 0.5rem 0.5rem 0;
            margin: 1.15rem 0;
            font-style: italic;
            color: #334155;
            box-shadow: inset 0 1px 2px 0 rgba(0,0,0,0.02);
        }
        .prose details { font-size: var(--chat-details-size, 1rem); line-height: 1.75; }
        .prose code {
            background-color: #f1f5f9;
            padding: 0.25rem 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.92em;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            color: #008546;
            font-weight: 600;
            border: 1px solid #e2e8f0;
        }
        .prose pre {
            background-color: #0f172a;
            color: #f8fafc;
            padding: 1.2rem;
            border-radius: 0.75rem;
            overflow-x: auto;
            margin: 1.1rem 0;
            font-size: 0.9em;
            line-height: 1.7;
            box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06);
        }
        .prose pre code {
            background-color: transparent;
            color: inherit;
            padding: 0;
            border: none;
            font-size: inherit;
        }
        .prose table {
            display: block;
            max-width: 100%;
            overflow-x: auto;
            width: 100%;
            border-collapse: collapse;
            margin: 1.25rem 0;
            font-size: 0.95em;
        }
        .prose th, .prose td {
            border: 1px solid #cbd5e1;
            padding: 0.65rem 1rem;
            text-align: left;
        }
        .prose th {
            background-color: #f8fafc;
            font-weight: 700;
            color: #0f172a;
        }
        .prose td {
            background-color: #ffffff;
        }

        @keyframes pulseGlow {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .pulse-glow { animation: pulseGlow 3s infinite ease-in-out; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 h-screen flex overflow-hidden selection:bg-emerald-200 selection:text-emerald-900">

    <!-- Mobile Overlay Backdrop -->
    <div id="mobile-backdrop" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-30 hidden md:hidden transition-opacity"></div>

    <!-- Sidebar (Desktop & Mobile Drawer) -->
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-200 ease-out">

        <!-- Logo Area -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-200 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg flex items-center justify-center overflow-hidden shrink-0">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo SMK N 1 Kinali" class="w-10 h-10 object-contain">
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 tracking-tight leading-tight text-sm">E-Modul TKJ</h1>
                    <p class="text-[10px] text-[#008546] font-mono font-semibold tracking-wider uppercase">SMK N 1 Kinali</p>
                </div>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>

        <!-- Navigation Links Section -->
        <nav class="p-3.5 border-b border-slate-200 space-y-1">
            <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block px-2.5 mb-1.5">Menu Utama</span>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-user-gear text-slate-400 text-xs w-4 text-center"></i>
                <span>Profil Saya</span>
            </a>

            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-circle-question text-slate-400 text-xs w-4 text-center"></i>
                <span>Petunjuk Siswa</span>
            </a>

            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-book-bookmark text-slate-400 text-xs w-4 text-center"></i>
                <span>Katalog E-Modul</span>
            </a>

            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/80 transition-colors">
                <i class="fa-solid fa-robot text-[#008546] text-xs w-4 text-center"></i>
                <span>Tanya Jawab AI (Chatbot)</span>
            </a>
        </nav>

        <!-- Modules Context Section -->
        <div class="flex-1 overflow-y-auto overflow-x-hidden px-3.5 py-3 space-y-3">
            <div class="flex items-center justify-between px-1">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Modul Terindeks AI</span>
                <span class="bg-emerald-100 text-[#008546] text-xs font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-300/80">{{ $modules->count() }} Modul</span>
            </div>

            @if($modules->isEmpty())
                <div class="p-4 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-200 text-xs text-slate-400 text-center">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300 block"></i>
                    Belum ada modul aktif yang diunggah.
                </div>
            @else
                <div class="space-y-2.5">
                    @foreach($modules as $module)
                    @php
                        $isSelected = $selectedModule === $module->id;
                    @endphp
                    <div class="group relative p-3 rounded-2xl border-2 transition-all duration-200 {{ $isSelected ? 'border-[#008546] bg-emerald-50/60 shadow-sm ring-2 ring-emerald-500/20' : 'border-slate-200 bg-white hover:border-emerald-400 hover:shadow-md hover:-translate-y-0.5' }}">
                        <!-- Header Row: KB Badge + Quiz Tag / Active Badge -->
                        <div class="flex items-center justify-between gap-1.5 mb-1.5">
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[11px] font-extrabold {{ $isSelected ? 'bg-[#008546] text-white shadow-2xs' : 'bg-emerald-100 text-[#008546] border border-emerald-300/80' }}">
                                    {{ $module->kb_nomor ?? 'KB' }}
                                </span>
                                @if($module->quiz?->is_published)
                                    <span class="inline-flex items-center gap-1 text-[10px] font-bold text-purple-700 bg-purple-50 px-1.5 py-0.5 rounded-md border border-purple-200" title="Kuis Objektif Tersedia">
                                        <i class="fa-solid fa-clipboard-check text-[9px]"></i> Kuis
                                    </span>
                                @endif
                            </div>

                            @if($isSelected)
                                <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-800 bg-emerald-200/70 px-2 py-0.5 rounded-full border border-emerald-300">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                                    Aktif
                                </span>
                            @endif
                        </div>

                        <!-- Title & Subject -->
                        <a href="{{ route('siswa.modules.show', $module->id) }}" class="block group/link" title="Buka materi {{ $module->judul }}">
                            <h4 class="text-xs font-bold text-slate-900 group-hover/link:text-[#008546] line-clamp-2 leading-snug transition-colors">
                                {{ $module->judul }}
                            </h4>
                            <p class="text-[10px] font-semibold text-slate-400 mt-1 truncate">
                                {{ $module->mapel }}
                            </p>
                        </a>

                        <!-- Interactive Action Toolbar -->
                        <div class="mt-2.5 pt-2 border-t border-slate-100 flex items-center justify-between gap-1.5">
                            @if($isSelected)
                                <a href="{{ route('siswa.dashboard', ['mapel' => $selectedMapel]) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-1 py-1.5 px-2 rounded-xl text-[10px] font-bold bg-[#008546] text-white hover:bg-emerald-800 shadow-2xs transition-all"
                                   title="Klik untuk melepas fokus dan bertanya ke semua modul">
                                    <i class="fa-solid fa-check text-[9px]"></i>
                                    <span>Fokus Aktif</span>
                                </a>
                            @else
                                <a href="{{ route('siswa.dashboard', ['mapel' => $selectedMapel, 'module_id' => $module->id]) }}" 
                                   class="flex-1 inline-flex items-center justify-center gap-1 py-1.5 px-2 rounded-xl text-[10px] font-bold text-emerald-800 bg-emerald-50 hover:bg-[#008546] hover:text-white border border-emerald-200 shadow-2xs transition-all active:scale-95"
                                   title="Fokuskan tanya jawab AI hanya ke materi KB ini">
                                    <i class="fa-solid fa-crosshairs text-[9px]"></i>
                                    <span>Tanya KB Ini</span>
                                </a>
                            @endif

                            <a href="{{ route('siswa.modules.show', $module->id) }}" 
                               class="w-7 h-7 flex items-center justify-center rounded-xl text-slate-500 hover:text-[#008546] hover:bg-emerald-50 border border-slate-200 transition-colors" 
                               title="Buka Materi Lengkap">
                                <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                            </a>

                            <a href="{{ route('modules.download', $module->id) }}" 
                               class="w-7 h-7 flex items-center justify-center rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 border border-slate-200 transition-colors" 
                               title="Unduh File Modul">
                                <i class="fa-solid fa-download text-[10px]"></i>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif

            <!-- Quick Action Chips in Sidebar -->
            <div class="pt-3 border-t border-slate-100">
                <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider block mb-2 px-1">Pertanyaan Cepat</span>
                <div class="space-y-1.5">
                    <button class="quick-chip w-full text-left p-2.5 bg-slate-50 hover:bg-emerald-50/50 rounded-lg border border-slate-200 hover:border-[#008546]/40 text-xs font-medium text-slate-700 hover:text-[#008546] transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-emerald-600 text-[10px] shrink-0"></i>
                        <span class="truncate">Ada materi apa saja di sistem?</span>
                    </button>
                    <button class="quick-chip w-full text-left p-2.5 bg-slate-50 hover:bg-emerald-50/50 rounded-lg border border-slate-200 hover:border-[#008546]/40 text-xs font-medium text-slate-700 hover:text-[#008546] transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-emerald-600 text-[10px] shrink-0"></i>
                        <span class="truncate">Jelaskan Firewall & Keamanan (KB 4)</span>
                    </button>
                    <button class="quick-chip w-full text-left p-2.5 bg-slate-50 hover:bg-emerald-50/50 rounded-lg border border-slate-200 hover:border-[#008546]/40 text-xs font-medium text-slate-700 hover:text-[#008546] transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-bolt text-emerald-600 text-[10px] shrink-0"></i>
                        <span class="truncate">Frekuensi & Perawatan Jaringan (KB 3)</span>
                    </button>
                </div>
            </div>
        </div>

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
    <main class="flex-1 flex flex-col h-full bg-white relative overflow-hidden">

        <!-- Top Bar Header -->
        <header class="h-16 flex items-center justify-between px-4 sm:px-6 border-b border-slate-200 bg-white shrink-0 z-20 shadow-2xs">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-xl hover:bg-slate-100 border border-slate-200">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-[#008546] to-emerald-700 text-white flex items-center justify-center text-sm shadow-xs shrink-0">
                        <i class="fa-solid fa-microchip"></i>
                    </div>
                    <div>
                        <h2 class="text-sm sm:text-base font-bold text-slate-900 leading-tight flex items-center gap-2">
                            <span>Ruang Tanya Jawab AI</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200/80">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> RAG Ready
                            </span>
                        </h2>
                        <p class="text-[11px] text-slate-500 font-medium hidden sm:block">Asisten Materi TKJ Terverifikasi Dokumen — SMK Negeri 1 Kinali</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Font Size Selector (A Standard, A+ Besar, A++ Ekstra Besar) -->
                <div class="inline-flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200" title="Atur Ukuran Teks">
                    <span class="hidden sm:inline text-[11px] font-bold text-slate-400 px-2 uppercase tracking-wider">Ukuran Teks:</span>
                    <button type="button" data-size="standard" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all" title="Ukuran Normal">
                        A
                    </button>
                    <button type="button" data-size="large" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-bold text-[#008546] bg-white shadow-2xs transition-all" title="Ukuran Besar (Default)">
                        A+
                    </button>
                    <button type="button" data-size="xl" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-extrabold text-slate-600 hover:text-slate-900 transition-all" title="Ukuran Ekstra Besar">
                        A++
                    </button>
                </div>
            </div>
        </header>

        <!-- Messages Area -->
        <div id="chat-container" class="flex-1 overflow-y-auto scroll-smooth">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-6 flex flex-col min-h-full">

                <!-- Welcome Banner (Shows when chats are fresh or as hero header) -->
                @if($chats->isEmpty())
                <div id="welcome-hero" class="my-auto py-6 animate-fade-in">
                    <div class="max-w-2xl mx-auto bg-white rounded-3xl border border-slate-200/90 shadow-sm overflow-hidden">
                        <!-- Cyber-Network Hero Header -->
                        <div class="relative overflow-hidden bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8">
                            <!-- Cyber Grid Overlay -->
                            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 20px 20px;"></div>
                            <div class="absolute -top-16 -right-16 w-56 h-56 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

                            <div class="relative z-10 space-y-2.5">
                                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold backdrop-blur-md">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>RAG Knowledge Assistant • TKJ</span>
                                </div>
                                <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                                    Halo, {{ explode(' ', auth()->user()->name)[0] }} 👋
                                </h3>
                                <p class="text-slate-300 text-xs sm:text-sm leading-relaxed max-w-xl">
                                    Ajukan pertanyaan seputar materi Kegiatan Belajar (KB 1–KB 4), konfigurasi perangkat, atau uji pemahaman Anda dengan soal interaktif.
                                </p>
                            </div>
                        </div>

                        <!-- Interactive Suggestion Matrix -->
                        <div class="p-5 sm:p-6 bg-slate-50/70">
                            <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider block mb-3">Rekomendasi Topik Diskusi</span>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">
                                <button class="quick-chip p-3.5 bg-white hover:bg-emerald-50/40 rounded-2xl border border-slate-200/90 hover:border-emerald-400/80 transition-all text-left group shadow-2xs hover:shadow-xs">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-[#008546] text-slate-600 group-hover:text-white flex items-center justify-center text-xs transition-colors shrink-0 mt-0.5">
                                            <i class="fa-solid fa-network-wired"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#008546] transition-colors">Konstruksi Jaringan</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Jelaskan mengenai konsep IP Address & Subnetting</p>
                                        </div>
                                    </div>
                                </button>

                                <button class="quick-chip p-3.5 bg-white hover:bg-emerald-50/40 rounded-2xl border border-slate-200/90 hover:border-emerald-400/80 transition-all text-left group shadow-2xs hover:shadow-xs">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-[#008546] text-slate-600 group-hover:text-white flex items-center justify-center text-xs transition-colors shrink-0 mt-0.5">
                                            <i class="fa-solid fa-server"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#008546] transition-colors">Sistem Operasi Jaringan</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Bagaimana cara setting dasar Mikrotik Router?</p>
                                        </div>
                                    </div>
                                </button>

                                <button class="quick-chip p-3.5 bg-white hover:bg-emerald-50/40 rounded-2xl border border-slate-200/90 hover:border-emerald-400/80 transition-all text-left group shadow-2xs hover:shadow-xs">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-slate-100 group-hover:bg-[#008546] text-slate-600 group-hover:text-white flex items-center justify-center text-xs transition-colors shrink-0 mt-0.5">
                                            <i class="fa-solid fa-shield-halved"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-slate-800 group-hover:text-[#008546] transition-colors">Keamanan Jaringan</h4>
                                            <p class="text-xs text-slate-500 mt-0.5">Apa fungsi utama Firewall dalam jaringan?</p>
                                        </div>
                                    </div>
                                </button>

                                <button id="btn-quick-kuis" class="p-3.5 bg-gradient-to-br from-slate-900 to-emerald-950 hover:from-slate-800 hover:to-emerald-900 text-white rounded-2xl border border-emerald-500/30 transition-all text-left group shadow-2xs hover:shadow-xs">
                                    <div class="flex items-start gap-3">
                                        <div class="w-8 h-8 rounded-xl bg-white/10 text-emerald-400 flex items-center justify-center text-xs shrink-0 mt-0.5">
                                            <i class="fa-solid fa-clipboard-question"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-xs sm:text-sm font-bold text-white">Mulai Latihan Soal AI</h4>
                                            <p class="text-xs text-slate-300 mt-0.5">Uji pemahaman materi Anda sekarang</p>
                                        </div>
                                    </div>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                @if($historyPages->hasPages())
                    <nav class="flex justify-between text-sm sm:text-base font-semibold text-emerald-700" aria-label="Riwayat percakapan">
                        @if($historyPages->nextPageUrl())
                            <a href="{{ $historyPages->nextPageUrl() }}">Percakapan lebih lama</a>
                        @endif
                        @if($historyPages->previousPageUrl())
                            <a href="{{ $historyPages->previousPageUrl() }}">Percakapan lebih baru</a>
                        @endif
                    </nav>
                @endif
                <!-- Chat History Loop -->
                @foreach($chats as $chat)
                    <!-- User Message Bubble -->
                    <div class="flex items-start justify-end gap-3 group">
                        <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%]">
                            <div class="bg-[#008546] text-white px-5 py-3.5 rounded-2xl rounded-tr-xs shadow-md text-base sm:text-[1.0625rem] leading-relaxed chat-user-bubble">
                                <span class="whitespace-pre-wrap">{{ $chat->pertanyaan === '[LATIHAN_SOAL]' ? 'Berikan saya latihan soal interaktif dari materi.' : $chat->pertanyaan }}</span>
                            </div>
                            <span class="text-xs text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">{{ $chat->created_at->timezone(config('app.display_timezone'))->format('H:i') }}</span>
                        </div>
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-9 h-9 rounded-xl object-cover shrink-0 mt-0.5 shadow-sm border border-slate-300">
                        @else
                            <div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 mt-0.5 shadow-sm border border-slate-300">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- AI Message Bubble -->
                    <div class="flex items-start gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-900 to-emerald-950 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <i class="fa-solid fa-robot text-sm"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-5 sm:p-6 rounded-2xl rounded-tl-xs shadow-2xs border border-slate-200/90 text-slate-800 prose prose-emerald max-w-none w-full relative">
                                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 not-prose">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold text-[#008546] flex items-center gap-1.5">
                                            <i class="fa-solid fa-robot"></i> Asisten AI Pembelajaran TKJ
                                        </span>
                                        <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-bold border border-slate-200/80">
                                            RAG Verified
                                        </span>
                                    </div>
                                </div>
                                <div class="markdown-content hidden">{{ $chat->jawaban }}</div>
                                <div class="rendered-content">Memuat format...</div>
                                @if($chat->reviewed_at)<p class="text-sm text-emerald-800 mt-3">Nilai guru: {{ $chat->score }}/100 ? {{ $chat->review_note }}</p>@endif
                                @if($chat->sources)
                                    <details class="not-prose mt-4 pt-3 border-t border-slate-100 text-slate-700 chat-sources-block group/details">
                                        <summary class="font-semibold text-slate-700 cursor-pointer hover:text-slate-900 transition-colors inline-flex items-center gap-2 py-1 select-none">
                                            <i class="fa-solid fa-book-bookmark text-xs text-slate-500"></i>
                                            <span class="text-xs sm:text-sm font-semibold">Sumber materi referensi</span>
                                            <span class="bg-slate-100 text-slate-600 text-[11px] font-bold px-2 py-0.5 rounded-md border border-slate-200">{{ count($chat->sources) }} bagian</span>
                                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1 transition-transform group-open/details:rotate-180"></i>
                                        </summary>
                                        <div class="mt-3 space-y-2.5">
                                            @foreach($chat->sources as $source)
                                                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-200/90 text-xs sm:text-sm">
                                                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                                                        <p class="font-bold text-slate-800 text-xs sm:text-sm">
                                                            <span class="text-slate-500 font-semibold mr-1">[{{ $loop->iteration }}]</span>{{ $source['judul'] }}
                                                        </p>
                                                        <span class="text-[11px] text-slate-500 font-medium bg-white px-2 py-0.5 rounded border border-slate-200">
                                                            {{ $source['mapel'] }} {{ !empty($source['kb_nomor']) ? '• ' . $source['kb_nomor'] : '' }}
                                                        </span>
                                                    </div>
                                                    <div class="source-excerpt whitespace-pre-wrap text-slate-700 not-italic bg-white p-3 rounded-lg border border-slate-200 text-xs sm:text-[13px] leading-relaxed max-h-56 overflow-y-auto">{{ $source['text'] }}</div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @endif

                                <!-- Copy button -->
                                <button onclick="copyResponse(this)" class="absolute top-3 right-3 text-slate-400 hover:text-[#008546] bg-slate-50 hover:bg-emerald-50 p-2 rounded-lg border border-slate-200 text-xs transition-colors opacity-0 group-hover:opacity-100" title="Salin jawaban">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <span class="text-xs text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">{{ $chat->created_at->timezone(config('app.display_timezone'))->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach

                <!-- Typing indicator -->
                <div id="typing-indicator" class="hidden flex items-start gap-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-900 to-emerald-950 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                        <i class="fa-solid fa-robot text-sm animate-bounce"></i>
                    </div>
                    <div class="bg-white px-5 py-3.5 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 flex items-center gap-2">
                        <span class="text-sm sm:text-base text-slate-600 font-medium mr-1">AI sedang berpikir</span>
                        <div class="w-2 h-2 bg-[#008546] rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-[#008546] rounded-full animate-bounce" style="animation-delay: 0.15s"></div>
                        <div class="w-2 h-2 bg-[#008546] rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                    </div>
                </div>

                <!-- Scroll anchor -->
                <div id="scroll-anchor" class="h-2"></div>
            </div>
        </div>

        <!-- Input Bar Section -->
        <div class="bg-white border-t-2 border-slate-100 shrink-0 px-4 sm:px-6 py-4 shadow-xl">
            <div class="max-w-4xl mx-auto space-y-3">

                <!-- Quick Tools & Filter Bar -->
                <div class="flex items-center justify-between gap-2.5 flex-wrap sm:flex-nowrap">
                    <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap flex-1">
                        <!-- Subject Selector -->
                        <div class="inline-flex items-center bg-slate-50 border-2 border-slate-200/90 rounded-xl px-3 py-1.5 text-xs text-slate-700 shadow-2xs focus-within:border-[#008546] focus-within:bg-white transition-all shrink-0">
                            <i class="fa-solid fa-book-open text-slate-400 text-xs mr-2"></i>
                            <select id="mapel-select" class="bg-transparent border-0 outline-none text-xs font-bold text-slate-800 cursor-pointer pr-1">
                                <option value="Semua" {{ $selectedMapel === 'Semua' ? 'selected' : '' }}>Semua Mapel</option>
                                @foreach($mapels as $m)
                                    <option value="{{ $m }}" {{ $selectedMapel === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Scope Module / KB Selector -->
                        <div class="inline-flex items-center {{ $selectedModule ? 'bg-emerald-50/70 border-emerald-400 text-emerald-900 ring-2 ring-emerald-500/20' : 'bg-slate-50 border-slate-200/90 text-slate-700' }} border-2 rounded-xl px-3 py-1.5 text-xs shadow-2xs focus-within:border-[#008546] focus-within:bg-white transition-all flex-1 min-w-[200px]">
                            <i class="fa-solid fa-layer-group {{ $selectedModule ? 'text-[#008546]' : 'text-slate-400' }} text-xs mr-2 shrink-0"></i>
                            <span class="text-slate-400 text-[10px] font-bold uppercase tracking-wider mr-1.5 shrink-0">Cakupan:</span>
                            <select id="module-select" class="bg-transparent border-0 outline-none text-xs font-bold {{ $selectedModule ? 'text-emerald-950' : 'text-slate-800' }} cursor-pointer w-full truncate">
                                <option value="">Semua Modul (KB 1 - KB 4)</option>
                                @foreach($modules->filter(fn ($item) => $selectedMapel === 'Semua' || $item->mapel === $selectedMapel) as $scopeModule)
                                    <option value="{{ $scopeModule->id }}" @selected($selectedModule === $scopeModule->id)>{{ $scopeModule->kb_nomor }}: {{ $scopeModule->judul }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Quiz Button -->
                    <button type="button" id="btn-kuis" class="inline-flex items-center gap-2 text-xs font-extrabold text-white bg-[#008546] hover:bg-[#00703c] px-4 py-2 rounded-xl border border-emerald-500 shadow-sm hover:shadow-md transition-all active:scale-95 shrink-0 group">
                        <i class="fa-solid fa-brain group-hover:rotate-12 transition-transform text-xs"></i>
                        <span>Latihan Soal AI</span>
                    </button>
                </div>

                @if($selectedModule)
                    @php
                        $activeScopeMod = $modules->firstWhere('id', $selectedModule);
                    @endphp
                    @if($activeScopeMod)
                        <div class="flex items-center justify-between gap-2 px-4 py-2.5 bg-emerald-50/90 rounded-2xl border border-emerald-200/90 text-xs text-emerald-900 animate-fade-in shadow-xs">
                            <div class="flex items-center gap-2 truncate">
                                <span class="w-2 h-2 rounded-full bg-emerald-600 animate-pulse shrink-0"></span>
                                <span class="truncate">Fokus Tanya Jawab AI: <strong>{{ $activeScopeMod->kb_nomor }}</strong> — {{ $activeScopeMod->judul }}</span>
                            </div>
                            <a href="{{ route('siswa.dashboard', ['mapel' => $selectedMapel]) }}" class="shrink-0 font-bold underline hover:text-emerald-950 text-[11px]">
                                Reset ke Semua KB
                            </a>
                        </div>
                    @endif
                @endif

                <!-- Chat Input Form -->
                <div id="quiz-state" class="hidden text-sm text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200/60" role="status">
                    <span>Mode jawaban kuis aktif. Pesan berikutnya akan dinilai sebagai jawaban soal.</span>
                    <button id="cancel-quiz" type="button" class="underline font-semibold ml-2 hover:text-[#008546]">Kembali ke tanya jawab</button>
                </div>
                <form id="chat-form" class="relative flex items-center">
                    <input type="text" id="pertanyaan" maxlength="1000" autocomplete="off" placeholder="Ketik pertanyaan seputar materi TKJ..."
                           class="w-full bg-slate-50 border-2 border-slate-200 text-slate-900 rounded-2xl pl-5 pr-14 py-3.5 text-base sm:text-lg focus:bg-white focus:border-[#008546] focus:ring-4 focus:ring-emerald-500/15 transition-all outline-none placeholder-slate-400 font-medium shadow-xs">

                    <button type="submit" id="submit-btn" 
                            class="absolute right-2.5 top-2.5 bottom-2.5 bg-[#008546] hover:bg-[#00703c] text-white rounded-xl px-4 transition-all flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed shadow-sm hover:scale-105 active:scale-95" title="Kirim Pertanyaan">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-xs text-slate-400 font-semibold tracking-wide">RAG Chatbot Pintar • SMK N 1 Kinali</span>
                </div>
            </div>
        </div>

    </main>

    <!-- UI Scripts -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chat-container');
            const chatForm = document.getElementById('chat-form');
            const inputField = document.getElementById('pertanyaan');
            const submitBtn = document.getElementById('submit-btn');
            const typingIndicator = document.getElementById('typing-indicator');
            const btnKuis = document.getElementById('btn-kuis');
            const btnQuickKuis = document.getElementById('btn-quick-kuis');
            const mapelSelect = document.getElementById('mapel-select');
            const moduleSelect = document.getElementById('module-select');
            const previousModule = moduleSelect.value;
            moduleSelect.addEventListener('change', async () => {
                if (inFlight || (activeQuizId && !(await cancelQuiz()))) {
                    moduleSelect.value = previousModule;
                    return;
                }
                const url = new URL(window.location.href);
                url.searchParams.set('module_id', moduleSelect.value);
                url.searchParams.set('mapel', mapelSelect.value);
                window.location.assign(url);
            });
            const scrollAnchor = document.getElementById('scroll-anchor');
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('open-sidebar-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const mobileBackdrop = document.getElementById('mobile-backdrop');
            const quizState = document.getElementById('quiz-state');
            const cancelQuizBtn = document.getElementById('cancel-quiz');
            let activeQuizId = @json($activeQuiz?->id);
            let inFlight = false;
            let previousMapel = mapelSelect.value;
            function updateQuizState(id) {
                activeQuizId = id;
                quizState.classList.toggle('hidden', !id);
                inputField.placeholder = id ? 'Ketik jawaban untuk soal kuis...' : 'Ketik pertanyaan seputar materi TKJ...';
            }
            updateQuizState(activeQuizId);

            async function cancelQuiz() {
                if (inFlight) return false;
                inFlight = true;
                cancelQuizBtn.disabled = true;
                try {
                    const response = await fetch("{{ route('siswa.chat.ask') }}", {
                        method: 'POST',
                        signal: AbortSignal.timeout(65000),
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: JSON.stringify({ action: 'cancel_quiz' })
                    });
                    const result = await response.json();
                    if (!response.ok || !result.success) throw new Error();
                    updateQuizState(null);
                    return true;
                } catch {
                    appendAIMessage('Kuis belum dapat dibatalkan. Silakan coba lagi.');
                    return false;
                } finally {
                    inFlight = false;
                    cancelQuizBtn.disabled = false;
                }
            }
            cancelQuizBtn.addEventListener('click', cancelQuiz);
            mapelSelect.addEventListener('change', async () => {
                if (activeQuizId && !(await cancelQuiz())) {
                    mapelSelect.value = previousMapel;
                    return;
                }
                previousMapel = mapelSelect.value;
                const url = new URL(window.location.href);
                url.searchParams.set('mapel', previousMapel);
                url.searchParams.delete('module_id');
                window.location.assign(url);
            });

            // Render existing markdown messages
            document.querySelectorAll('.markdown-content').forEach(el => {
                const rawMarkdown = el.textContent;
                const renderedDiv = el.nextElementSibling;
                renderedDiv.innerHTML = window.renderRagMarkdown(rawMarkdown);
            });

            // Mobile Drawer Toggle
            function toggleSidebar() {
                sidebar.classList.toggle('-translate-x-full');
                mobileBackdrop.classList.toggle('hidden');
            }

            if(openSidebarBtn) openSidebarBtn.addEventListener('click', toggleSidebar);
            if(closeSidebarBtn) closeSidebarBtn.addEventListener('click', toggleSidebar);
            if(mobileBackdrop) mobileBackdrop.addEventListener('click', toggleSidebar);

            // Quick Chips Handler
            document.querySelectorAll('.quick-chip').forEach(chip => {
                chip.addEventListener('click', function() {
                    const text = this.innerText.trim();
                    inputField.value = text;
                    inputField.focus();
                });
            });

            function scrollToBottom() {
                scrollAnchor.scrollIntoView({ behavior: 'smooth', block: 'end' });
            }

            setTimeout(scrollToBottom, 100);

            // Handle Kuis trigger
            function triggerKuis() {
                if (inFlight) return;
                inputField.value = '[LATIHAN_SOAL]';
                chatForm.dispatchEvent(new Event('submit'));
            }

            if (btnKuis) btnKuis.addEventListener('click', triggerKuis);
            if (btnQuickKuis) btnQuickKuis.addEventListener('click', triggerKuis);

            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                if (inFlight) return;

                const question = inputField.value.trim();
                const selectedMapel = mapelSelect ? mapelSelect.value : 'Semua';

                if (!question) return;
                inFlight = true;
                const action = question === '[LATIHAN_SOAL]' ? 'quiz' : (activeQuizId ? 'quiz_answer' : 'ask');

                // Disable input
                inputField.disabled = true;
                submitBtn.disabled = true;
                if (btnKuis) btnKuis.disabled = true;
                mapelSelect.disabled = true;
                moduleSelect.disabled = true;
                cancelQuizBtn.disabled = true;
                inputField.value = '';

                // Remove welcome hero if exists
                const welcomeHero = document.getElementById('welcome-hero');
                if(welcomeHero) welcomeHero.remove();

                // Add User Bubble
                if (question === '[LATIHAN_SOAL]') {
                    appendUserMessage("Berikan saya latihan soal interaktif dari materi.");
                } else {
                    appendUserMessage(question);
                }

                setTimeout(scrollToBottom, 50);

                // Show typing indicator
                typingIndicator.classList.remove('hidden');
                setTimeout(scrollToBottom, 50);

                try {
                    const token = document.querySelector('meta[name="csrf-token"]').content;

                    const response = await fetch("{{ route('siswa.chat.ask') }}", {
                        method: 'POST',
                        signal: AbortSignal.timeout(65000),
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            pertanyaan: question,
                            mapel: selectedMapel,
                            action,
                            quiz_id: activeQuizId,
                            module_id: moduleSelect.value ? Number(moduleSelect.value) : null
                        })
                    });

                    const result = await response.json();

                    typingIndicator.classList.add('hidden');

                    if (result.success) {
                        appendAIMessage(result.data.jawaban, result.data.created_at, result.data.sources);
                        updateQuizState(result.data.quiz_id);
                    } else {
                        inputField.value = question === '[LATIHAN_SOAL]' ? '' : question;
                        if (response.status === 409 && action === 'quiz_answer') updateQuizState(null);
                        appendAIMessage('**Terjadi kesalahan:** ' + (result.message || 'Gagal memproses jawaban.'));
                    }
                } catch (error) {
                    inputField.value = question === '[LATIHAN_SOAL]' ? '' : question;
                    typingIndicator.classList.add('hidden');
                    appendAIMessage('**Error koneksi ke server.** Mohon coba lagi beberapa saat.');
                    console.error('Error:', error);
                } finally {
                    inFlight = false;
                    mapelSelect.disabled = false;
                    moduleSelect.disabled = false;
                    cancelQuizBtn.disabled = false;
                    inputField.disabled = false;
                    submitBtn.disabled = false;
                    if (btnKuis) btnKuis.disabled = false;
                    inputField.focus();
                    setTimeout(scrollToBottom, 50);
                }
            });

            // Font Size Preference Controller
            const fontButtons = document.querySelectorAll('.font-size-btn');
            const savedFontSize = localStorage.getItem('rag_chat_font_size') || 'large'; // Default 'large'

            function applyFontSize(size) {
                document.body.classList.remove('font-size-standard', 'font-size-large', 'font-size-xl');
                document.body.classList.add(`font-size-${size}`);
                localStorage.setItem('rag_chat_font_size', size);

                fontButtons.forEach(btn => {
                    const isMatch = btn.dataset.size === size;
                    btn.className = isMatch
                        ? 'font-size-btn px-2.5 py-1 rounded-lg text-xs font-bold text-[#008546] bg-white shadow-xs transition-all'
                        : 'font-size-btn px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all';
                });
            }

            // Inisialisasi ukuran font saat halaman dimuat
            applyFontSize(savedFontSize);

            fontButtons.forEach(btn => {
                btn.addEventListener('click', () => {
                    applyFontSize(btn.dataset.size);
                });
            });

            function appendUserMessage(text) {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                const userAvatarUrl = {{ Illuminate\Support\Js::from(auth()->user()->avatar_url ?? '') }};
                const userInit = {{ Illuminate\Support\Js::from(mb_strtoupper(mb_substr(auth()->user()->name, 0, 1))) }};
                const userName = {{ Illuminate\Support\Js::from(auth()->user()->name) }};

                const avatarHtml = userAvatarUrl 
                    ? `<img src="${escapeHtml(userAvatarUrl)}" alt="${escapeHtml(userName)}" class="w-9 h-9 rounded-xl object-cover shrink-0 mt-0.5 shadow-sm border border-slate-300">`
                    : `<div class="w-9 h-9 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-sm shrink-0 mt-0.5 shadow-sm border border-slate-300">${escapeHtml(userInit)}</div>`;

                const html = `
                    <div class="flex items-start justify-end gap-3 group animate-fade-in">
                        <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%]">
                            <div class="bg-[#008546] text-white px-5 py-3.5 rounded-2xl rounded-tr-xs shadow-md text-base sm:text-[1.0625rem] leading-relaxed chat-user-bubble">
                                <span class="whitespace-pre-wrap">${escapeHtml(text)}</span>
                            </div>
                            <span class="text-xs text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">${timeStr}</span>
                        </div>
                        ${avatarHtml}
                    </div>
                `;
                typingIndicator.insertAdjacentHTML('beforebegin', html);
            }

            function appendAIMessage(markdownText, timeStr = null, sources = []) {
                if (!timeStr) {
                    const now = new Date();
                    timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                }

                const htmlContent = window.renderRagMarkdown(markdownText);
                const sourceHtml = sources?.length ? `
                    <details class="not-prose mt-4 pt-3 border-t border-slate-100 text-slate-700 chat-sources-block group/details">
                        <summary class="font-semibold text-slate-700 cursor-pointer hover:text-slate-900 transition-colors inline-flex items-center gap-2 py-1 select-none">
                            <i class="fa-solid fa-book-bookmark text-xs text-slate-500"></i>
                            <span class="text-xs sm:text-sm font-semibold">Sumber materi referensi</span>
                            <span class="bg-slate-100 text-slate-600 text-[11px] font-bold px-2 py-0.5 rounded-md border border-slate-200">${sources.length} bagian</span>
                            <i class="fa-solid fa-chevron-down text-[10px] text-slate-400 ml-1 transition-transform group-open/details:rotate-180"></i>
                        </summary>
                        <div class="mt-3 space-y-2.5">
                            ${sources.map((source, index) => `
                                <div class="p-3 bg-slate-50/90 rounded-xl border border-slate-200/90 text-xs sm:text-sm">
                                    <div class="flex items-center justify-between gap-2 mb-1.5 flex-wrap">
                                        <p class="font-bold text-slate-800 text-xs sm:text-sm">
                                            <span class="text-slate-500 font-semibold mr-1">[${index + 1}]</span>${escapeHtml(source.judul)}
                                        </p>
                                        <span class="text-[11px] text-slate-500 font-medium bg-white px-2 py-0.5 rounded border border-slate-200">
                                            ${escapeHtml(source.mapel)}${source.kb_nomor ? ' • ' + escapeHtml(source.kb_nomor) : ''}
                                        </span>
                                    </div>
                                    <div class="source-excerpt whitespace-pre-wrap text-slate-700 not-italic bg-white p-3 rounded-lg border border-slate-200 text-xs sm:text-[13px] leading-relaxed max-h-56 overflow-y-auto">${escapeHtml(source.text)}</div>
                                </div>
                            `).join('')}
                        </div>
                    </details>` : '';

                const html = `
                    <div class="flex items-start gap-3 group animate-fade-in">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-slate-900 to-emerald-950 text-emerald-400 border border-emerald-500/30 flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                            <i class="fa-solid fa-robot text-sm"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-5 sm:p-6 rounded-2xl rounded-tl-xs shadow-2xs border border-slate-200/90 text-slate-800 prose prose-emerald max-w-none w-full relative">
                                <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 not-prose">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-extrabold text-[#008546] flex items-center gap-1.5">
                                            <i class="fa-solid fa-robot"></i> Asisten AI Pembelajaran TKJ
                                        </span>
                                        <span class="text-[10px] text-slate-500 bg-slate-100 px-2 py-0.5 rounded-full font-bold border border-slate-200/80">
                                            RAG Verified
                                        </span>
                                    </div>
                                </div>
                                ${htmlContent}
                                ${sourceHtml}
                                <button onclick="copyResponse(this)" class="absolute top-3 right-3 text-slate-400 hover:text-[#008546] bg-slate-50 hover:bg-emerald-50 p-2 rounded-lg border border-slate-200 text-xs transition-colors opacity-0 group-hover:opacity-100" title="Salin jawaban">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <span class="text-xs text-slate-400 mt-1.5 opacity-0 group-hover:opacity-100 transition-opacity font-medium">${timeStr}</span>
                        </div>
                    </div>
                `;
                typingIndicator.insertAdjacentHTML('beforebegin', html);
            }

            function escapeHtml(unsafe) {
                return String(unsafe ?? '')
                     .replace(/&/g, "&amp;")
                     .replace(/</g, "&lt;")
                     .replace(/>/g, "&gt;")
                     .replace(/"/g, "&quot;")
                     .replace(/'/g, "&#039;");
            }
        });

        function copyResponse(btn) {
            const textToCopy = btn.parentElement.innerText;
            navigator.clipboard.writeText(textToCopy).then(() => {
                const icon = btn.querySelector('i');
                icon.className = 'fa-solid fa-check text-emerald-600';
                setTimeout(() => {
                    icon.className = 'fa-regular fa-copy';
                }, 2000);
            });
        }
    </script>
</body>
</html>
