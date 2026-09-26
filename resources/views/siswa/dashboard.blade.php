<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TKJ AI Assistant - SMK N 1 Kinali</title>

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

        /* Sources text */
        .chat-sources-block {
            font-size: var(--chat-details-size, 1rem);
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
    <aside id="sidebar" class="fixed md:static inset-y-0 left-0 w-72 bg-white border-r border-slate-200 flex flex-col h-full z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out shadow-2xl md:shadow-none">
        
        <!-- Logo Area -->
        <div class="h-16 flex items-center justify-between px-5 border-b border-slate-100 bg-white">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-tr from-[#008546] to-emerald-500 rounded-xl flex items-center justify-center shadow-lg shadow-emerald-600/20 text-white">
                    <i class="fa-solid fa-robot text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-slate-900 tracking-tight leading-tight">TKJ AI Assistant</h1>
                    <p class="text-[10px] text-emerald-600 font-semibold tracking-wide uppercase">SMK N 1 Kinali</p>
                </div>
            </div>
            <button id="close-sidebar-btn" class="md:hidden text-slate-400 hover:text-slate-600 p-1.5 rounded-lg">
                <i class="fa-solid fa-xmark text-lg"></i>
            </button>
        </div>
        
        <!-- Navigation Links Section -->
        <div class="px-4 pt-4 pb-2 border-b border-slate-100 space-y-1.5">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block px-2 mb-1">Menu Utama</span>
            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs">
                <i class="fa-solid fa-robot text-sm"></i>
                <span>Tanya Jawab AI</span>
            </a>
            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-book-bookmark text-blue-600 text-sm"></i>
                <span>Katalog E-Modul</span>
            </a>
            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-2.5 px-3 py-2.5 rounded-xl text-xs sm:text-sm font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-circle-question text-amber-500 text-sm"></i>
                <span>Petunjuk Siswa</span>
            </a>
        </div>

        <!-- Modules Context Section -->
        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <div class="flex items-center justify-between px-1">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Modul Terindeks AI</span>
                <span class="bg-emerald-50 text-[#008546] text-xs font-bold px-2.5 py-0.5 rounded-full border border-emerald-200/60">{{ $modules->count() }} Modul</span>
            </div>
            
            @if($modules->isEmpty())
                <div class="p-4 bg-slate-50 rounded-xl border border-dashed border-slate-200 text-xs text-slate-400 text-center">
                    <i class="fa-solid fa-folder-open text-2xl mb-2 text-slate-300 block"></i>
                    Belum ada modul aktif yang diunggah.
                </div>
            @else
                <div class="space-y-2">
                    @foreach($modules as $module)
                    <div class="group p-2.5 bg-slate-50 hover:bg-emerald-50/50 rounded-xl border border-slate-200/60 hover:border-emerald-300 transition-all duration-200 flex items-center justify-between gap-2">
                        <a href="{{ route('siswa.modules.show', $module->id) }}" class="flex items-center gap-2.5 flex-1 overflow-hidden" title="Lihat detail KB {{ $module->judul }}">
                            <div class="w-8 h-8 rounded-lg bg-white text-[#008546] flex items-center justify-center flex-shrink-0 shadow-sm border border-slate-100 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-layer-group text-xs"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-xs sm:text-sm font-semibold text-slate-800 truncate group-hover:text-[#008546] transition-colors">{{ $module->kb_nomor ?? 'KB' }}: {{ $module->judul }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ $module->mapel }}</p>
                            </div>
                        </a>
                        <!-- Download File Asli (FR-16) -->
                        <a href="{{ route('modules.download', $module->id) }}" class="p-1.5 rounded-lg text-slate-400 hover:text-[#008546] hover:bg-white transition-colors" title="Download Materi Asli">
                            <i class="fa-solid fa-download text-xs"></i>
                        </a>
                    </div>
                    @endforeach
                </div>
            @endif

            <!-- Quick Action Chips in Sidebar -->
            <div class="pt-4 border-t border-slate-100">
                <span class="text-xs font-bold text-slate-400 uppercase tracking-wider block mb-2 px-1">Panduan Cepat</span>
                <div class="space-y-1.5">
                    <button class="quick-chip w-full text-left p-2.5 rounded-xl text-xs sm:text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center gap-2.5">
                        <i class="fa-regular fa-lightbulb text-amber-500 text-sm"></i> <span>Apa saja materi di modul ini?</span>
                    </button>
                    <button class="quick-chip w-full text-left p-2.5 rounded-xl text-xs sm:text-sm text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center gap-2.5">
                        <i class="fa-solid fa-network-wired text-blue-500 text-sm"></i> <span>Penjelasan tentang Router & Switch</span>
                    </button>
                </div>
            </div>
        </div>
        
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
    <main class="flex-1 flex flex-col h-full bg-white relative overflow-hidden">
        
        <!-- Top Bar Header -->
        <header class="h-16 flex items-center justify-between px-4 sm:px-6 border-b border-slate-100 bg-white/90 backdrop-blur-md shrink-0 z-20">
            <div class="flex items-center gap-3">
                <button id="open-sidebar-btn" class="md:hidden text-slate-600 p-2 rounded-lg hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                    <h2 class="text-sm sm:text-base font-bold text-slate-800">Ruang Tanya Jawab AI</h2>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-3">
                <!-- Font Size Selector (A Standard, A+ Besar, A++ Ekstra Besar) -->
                <div class="inline-flex items-center bg-slate-100 p-0.5 rounded-xl border border-slate-200/80" title="Atur Ukuran Teks">
                    <span class="hidden sm:inline text-[11px] font-semibold text-slate-500 px-2">Ukuran Teks:</span>
                    <button type="button" data-size="standard" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-semibold text-slate-600 hover:text-slate-900 transition-all" title="Ukuran Normal">
                        A
                    </button>
                    <button type="button" data-size="large" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-bold text-[#008546] bg-white shadow-xs transition-all" title="Ukuran Besar (Default)">
                        A+
                    </button>
                    <button type="button" data-size="xl" class="font-size-btn px-2.5 py-1 rounded-lg text-xs font-extrabold text-slate-600 hover:text-slate-900 transition-all" title="Ukuran Ekstra Besar">
                        A++
                    </button>
                </div>

                <!-- Subject Badge Dropdown Indicator -->
                <div class="hidden md:flex items-center gap-2">
                    <span class="text-xs font-medium text-slate-400">Status AI:</span>
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-[#008546] border border-emerald-200/50">
                        <i class="fa-solid fa-circle text-[8px] animate-pulse"></i> {{ config('gemini.api_key') ? 'Siap menerima pertanyaan' : 'Belum dikonfigurasi' }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Messages Area -->
        <div id="chat-container" class="flex-1 overflow-y-auto scroll-smooth">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-6 flex flex-col min-h-full">
                
                <!-- Welcome Banner (Shows when chats are fresh or as hero header) -->
                @if($chats->isEmpty())
                <div id="welcome-hero" class="my-auto py-8 text-center animate-fade-in">
                    <div class="w-20 h-20 bg-emerald-50 rounded-2xl mx-auto flex items-center justify-center shadow-lg shadow-emerald-500/10 border border-emerald-100 mb-5 relative">
                        <i class="fa-solid fa-robot text-3xl text-[#008546]"></i>
                        <span class="absolute -bottom-1 -right-1 w-6 h-6 rounded-full bg-amber-400 text-white flex items-center justify-center text-xs shadow">
                            <i class="fa-solid fa-bolt"></i>
                        </span>
                    </div>
                    
                    <h3 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">
                        Halo, <span class="text-[#008546]">{{ explode(' ', auth()->user()->name)[0] }}</span>! 👋
                    </h3>
                    <p class="text-slate-600 text-base sm:text-lg mt-2.5 max-w-lg mx-auto leading-relaxed">
                        Saya asisten pintar TKJ SMK N 1 Kinali. Tanyakan materi jaringan, mikrotik, hardware, atau minta soal latihan!
                    </p>

                    <!-- Interactive Suggestion Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 max-w-2xl mx-auto mt-8 text-left">
                        <button class="quick-chip p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-2xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-[#008546] flex items-center justify-center text-sm group-hover:scale-110 transition-transform shrink-0">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-[#008546]">Konstruksi Jaringan</h4>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Jelaskan mengenai konsep IP Address & Subnetting</p>
                                </div>
                            </div>
                        </button>

                        <button class="quick-chip p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-2xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shrink-0">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-blue-600">Sistem Operasi Jaringan</h4>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Bagaimana cara setting dasar Mikrotik Router?</p>
                                </div>
                            </div>
                        </button>

                        <button class="quick-chip p-4 bg-slate-50 hover:bg-emerald-50/60 rounded-2xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-600 flex items-center justify-center text-sm group-hover:scale-110 transition-transform shrink-0">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm sm:text-base font-bold text-slate-800 group-hover:text-purple-600">Keamanan Jaringan</h4>
                                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Apa fungsi utama Firewall dalam jaringan?</p>
                                </div>
                            </div>
                        </button>

                        <button id="btn-quick-kuis" class="p-4 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-2xl shadow-md hover:shadow-lg transition-all text-left group">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center text-sm group-hover:rotate-12 transition-transform shrink-0">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <h4 class="text-sm sm:text-base font-bold">Mulai Latihan Soal AI 🚀</h4>
                                    <p class="text-xs sm:text-sm text-emerald-100 mt-0.5">Uji pemahaman materi Anda sekarang</p>
                                </div>
                            </div>
                        </button>
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
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
                            <i class="fa-solid fa-robot text-sm"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-5 sm:p-6 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 text-slate-800 prose prose-emerald max-w-none w-full relative">
                                <div class="markdown-content hidden">{{ $chat->jawaban }}</div>
                                <div class="rendered-content">Memuat format...</div>
                                @if($chat->reviewed_at)<p class="text-sm text-emerald-800 mt-3">Nilai guru: {{ $chat->score }}/100 ? {{ $chat->review_note }}</p>@endif
                                @if($chat->sources)
                                    <details class="mt-4 pt-3 border-t border-slate-100 text-sm text-slate-600 chat-sources-block">
                                        <summary class="font-semibold text-slate-700 cursor-pointer hover:text-[#008546] transition-colors inline-flex items-center gap-2 py-1">
                                            <i class="fa-solid fa-book-open-reader text-xs text-[#008546]"></i>
                                            <span>Sumber materi ({{ count($chat->sources) }})</span>
                                        </summary>
                                        <div class="mt-2.5 space-y-2.5 pl-1">
                                            @foreach($chat->sources as $source)
                                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs sm:text-sm">
                                                    <p class="font-bold text-slate-800">[{{ $loop->iteration }}] {{ $source['judul'] }} <span class="font-normal text-slate-500">— {{ $source['mapel'] }} / {{ $source['kb_nomor'] }}</span></p>
                                                    <blockquote class="whitespace-pre-wrap mt-1 text-slate-600 italic bg-white p-2 rounded-lg border border-slate-100">{{ $source['text'] }}</blockquote>
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
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
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
        <div class="bg-white/90 backdrop-blur-md border-t border-slate-200/80 shrink-0 px-4 sm:px-6 py-4 shadow-lg shadow-slate-200/40">
            <div class="max-w-4xl mx-auto space-y-3">
                
                <!-- Quick Tools & Filter Bar -->
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <div class="flex items-center gap-2">
                        <!-- Subject Selector -->
                        <div class="inline-flex items-center bg-slate-100 border border-slate-200/80 rounded-xl px-3 py-1.5 text-xs sm:text-sm text-slate-600 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                            <i class="fa-solid fa-filter text-slate-400 text-xs mr-2"></i>
                            <select id="mapel-select" class="bg-transparent border-0 outline-none text-xs sm:text-sm font-semibold text-slate-700 cursor-pointer pr-2">
                                <option value="Semua" {{ $selectedMapel === 'Semua' ? 'selected' : '' }}>Semua Mata Pelajaran</option>
                                @foreach($mapels as $m)
                                    <option value="{{ $m }}" {{ $selectedMapel === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <label class="text-sm">Modul / KB
                        <select id="module-select" class="rounded-xl text-sm"><option value="">Semua modul</option>@foreach($modules->filter(fn ($item) => $selectedMapel === 'Semua' || $item->mapel === $selectedMapel) as $scopeModule)<option value="{{ $scopeModule->id }}" @selected($selectedModule === $scopeModule->id)>{{ $scopeModule->kb_nomor }}: {{ $scopeModule->judul }}</option>@endforeach</select>
                    </label>
                    <!-- Quiz Button -->
                    <button type="button" id="btn-kuis" class="inline-flex items-center gap-2 text-xs sm:text-sm font-bold text-[#008546] bg-emerald-50 hover:bg-[#008546] hover:text-white px-3.5 py-1.5 rounded-xl border border-emerald-200/80 transition-all shadow-xs group">
                        <i class="fa-solid fa-brain group-hover:rotate-12 transition-transform text-xs"></i>
                        <span>Latihan Soal AI</span>
                    </button>
                </div>

                <!-- Chat Input Form -->
                <div id="quiz-state" class="hidden text-sm text-emerald-800 bg-emerald-50 p-2.5 rounded-xl border border-emerald-200/60" role="status">
                    <span>Mode jawaban kuis aktif. Pesan berikutnya akan dinilai sebagai jawaban soal.</span>
                    <button id="cancel-quiz" type="button" class="underline font-semibold ml-2 hover:text-[#008546]">Kembali ke tanya jawab</button>
                </div>
                <form id="chat-form" class="relative flex items-center">
                    <input type="text" id="pertanyaan" maxlength="1000" autocomplete="off" placeholder="Ketik pertanyaan seputar materi TKJ..."
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-2xl pl-5 pr-14 py-3.5 text-base sm:text-lg focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none placeholder-slate-400 font-medium shadow-xs">
                    
                    <button type="submit" id="submit-btn" 
                            class="absolute right-2 top-2 bottom-2 bg-[#008546] hover:bg-[#00703c] text-white rounded-xl px-4 transition-all flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed shadow-sm hover:scale-105 active:scale-95" title="Kirim Pertanyaan">
                        <i class="fa-solid fa-paper-plane text-sm"></i>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-xs text-slate-400 font-medium">RAG Chatbot Pintar • SMK N 1 Kinali</span>
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
                    <details class="mt-4 pt-3 border-t border-slate-100 text-sm text-slate-600 chat-sources-block">
                        <summary class="font-semibold text-slate-700 cursor-pointer hover:text-[#008546] transition-colors inline-flex items-center gap-2 py-1">
                            <i class="fa-solid fa-book-open-reader text-xs text-[#008546]"></i>
                            <span>Sumber materi (${sources.length})</span>
                        </summary>
                        <div class="mt-2.5 space-y-2.5 pl-1">
                            ${sources.map((source, index) => `
                                <div class="p-3 bg-slate-50 rounded-xl border border-slate-200/80 text-xs sm:text-sm">
                                    <p class="font-bold text-slate-800">[${index + 1}] ${escapeHtml(source.judul)} <span class="font-normal text-slate-500">— ${escapeHtml(source.mapel)} / ${escapeHtml(source.kb_nomor || '')}</span></p>
                                    <blockquote class="whitespace-pre-wrap mt-1 text-slate-600 italic bg-white p-2 rounded-lg border border-slate-100">${escapeHtml(source.text)}</blockquote>
                                </div>
                            `).join('')}
                        </div>
                    </details>` : '';

                const html = `
                    <div class="flex items-start gap-3 group animate-fade-in">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
                            <i class="fa-solid fa-robot text-sm"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-5 sm:p-6 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 text-slate-800 prose prose-emerald max-w-none w-full relative">
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
