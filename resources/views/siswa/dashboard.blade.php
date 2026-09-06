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
        
        /* Prose Markdown Styling */
        .prose p { margin-bottom: 0.75rem; line-height: 1.65; }
        .prose p:last-child { margin-bottom: 0; }
        .prose strong { font-weight: 700; color: #0f172a; }
        .prose ul { list-style-type: disc; padding-left: 1.25rem; margin-bottom: 0.75rem; }
        .prose ol { list-style-type: decimal; padding-left: 1.25rem; margin-bottom: 0.75rem; }
        .prose li { margin-bottom: 0.35rem; }
        .prose code { background-color: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 0.375rem; font-size: 0.85em; font-family: monospace; color: #008546; font-weight: 600; }
        .prose pre { background-color: #0f172a; color: #f8fafc; padding: 1rem; border-radius: 0.75rem; overflow-x: auto; margin-bottom: 0.75rem; box-shadow: inset 0 2px 4px 0 rgba(0,0,0,0.06); }
        .prose pre code { background-color: transparent; color: inherit; padding: 0; }
        .prose table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; font-size: 0.9em; }
        .prose th, .prose td { border: 1px solid #e2e8f0; padding: 0.5rem 0.75rem; text-align: left; }
        .prose th { background-color: #f8fafc; font-weight: 600; }

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
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block px-2 mb-1">Menu Utama</span>
            <a href="{{ route('siswa.dashboard') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200/60 shadow-xs">
                <i class="fa-solid fa-robot text-xs"></i>
                <span>Tanya Jawab AI</span>
            </a>
            <a href="{{ route('siswa.modules.index') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-book-bookmark text-blue-600 text-xs"></i>
                <span>Katalog E-Modul (KB 1-3)</span>
            </a>
            <a href="{{ route('siswa.petunjuk') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-circle-question text-amber-500 text-xs"></i>
                <span>Petunjuk Siswa</span>
            </a>
            <a href="{{ route('pengembang') }}" class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors">
                <i class="fa-solid fa-address-card text-purple-500 text-xs"></i>
                <span>Profil Pengembang</span>
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
                            <div class="w-7 h-7 rounded-lg bg-white text-[#008546] flex items-center justify-center flex-shrink-0 shadow-sm border border-slate-100 group-hover:scale-105 transition-transform">
                                <i class="fa-solid fa-layer-group text-xs"></i>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <p class="text-xs font-semibold text-slate-800 truncate group-hover:text-[#008546] transition-colors">{{ $module->kb_nomor ?? 'KB' }}: {{ $module->judul }}</p>
                                <p class="text-[10px] text-slate-400 truncate">{{ $module->mapel }}</p>
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
                    <button class="quick-chip w-full text-left p-2 rounded-lg text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center gap-2">
                        <i class="fa-regular fa-lightbulb text-amber-500"></i> Apa saja materi di modul ini?
                    </button>
                    <button class="quick-chip w-full text-left p-2 rounded-lg text-xs text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-colors flex items-center gap-2">
                        <i class="fa-solid fa-network-wired text-blue-500"></i> Penjelasan tentang Router & Switch
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

            <!-- Subject Badge Dropdown Indicator -->
            <div class="flex items-center gap-2">
                <span class="hidden sm:inline text-xs font-medium text-slate-400">Status AI:</span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-[#008546] border border-emerald-200/50">
                    <i class="fa-solid fa-[#008546] fa-circle text-[8px] animate-pulse"></i> Ready
                </span>
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
                    
                    <h3 class="text-2xl font-extrabold text-slate-900 tracking-tight">
                        Halo, <span class="text-[#008546]">{{ explode(' ', auth()->user()->name)[0] }}</span>! 👋
                    </h3>
                    <p class="text-slate-500 text-sm mt-2 max-w-md mx-auto leading-relaxed">
                        Saya asisten pintar TKJ SMK N 1 Kinali. Tanyakan materi jaringan, mikrotik, hardware, atau minta soal latihan!
                    </p>

                    <!-- Interactive Suggestion Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl mx-auto mt-8 text-left">
                        <button class="quick-chip p-3.5 bg-slate-50 hover:bg-emerald-50/60 rounded-xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-emerald-100 text-[#008546] flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-network-wired"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-[#008546]">Konstruksi Jaringan</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Jelaskan mengenai konsep IP Address & Subnetting</p>
                                </div>
                            </div>
                        </button>

                        <button class="quick-chip p-3.5 bg-slate-50 hover:bg-emerald-50/60 rounded-xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-blue-600">Sistem Operasi Jaringan</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Bagaimana cara setting dasar Mikrotik Router?</p>
                                </div>
                            </div>
                        </button>

                        <button class="quick-chip p-3.5 bg-slate-50 hover:bg-emerald-50/60 rounded-xl border border-slate-200/80 hover:border-emerald-300 transition-all text-left group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center text-xs group-hover:scale-110 transition-transform">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-800 group-hover:text-purple-600">Keamanan Jaringan</h4>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Apa fungsi utama Firewall dalam jaringan?</p>
                                </div>
                            </div>
                        </button>

                        <button id="btn-quick-kuis" class="p-3.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white rounded-xl shadow-md hover:shadow-lg transition-all text-left group">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center text-xs group-hover:rotate-12 transition-transform">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold">Mulai Latihan Soal AI 🚀</h4>
                                    <p class="text-[11px] text-emerald-100 mt-0.5">Uji pemahaman materi Anda sekarang</p>
                                </div>
                            </div>
                        </button>
                    </div>
                </div>
                @endif

                <!-- Chat History Loop -->
                @foreach($chats as $chat)
                    <!-- User Message Bubble -->
                    <div class="flex items-start justify-end gap-3 group">
                        <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%]">
                            <div class="bg-[#008546] text-white px-4 py-3 rounded-2xl rounded-tr-xs shadow-md text-sm leading-relaxed">
                                <span class="whitespace-pre-wrap">{{ $chat->pertanyaan === '[LATIHAN_SOAL]' ? 'Berikan saya latihan soal interaktif dari materi.' : $chat->pertanyaan }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">{{ $chat->created_at->format('H:i') }}</span>
                        </div>
                        @if(auth()->user()->avatar_url)
                            <img src="{{ auth()->user()->avatar_url }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-xl object-cover shrink-0 mt-0.5 shadow-sm border border-slate-300">
                        @else
                            <div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 shadow-sm border border-slate-300">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <!-- AI Message Bubble -->
                    <div class="flex items-start gap-3 group">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
                            <i class="fa-solid fa-robot text-xs"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-4 sm:p-5 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 text-sm text-slate-800 prose prose-emerald max-w-none w-full relative">
                                <div class="markdown-content hidden">{{ $chat->jawaban }}</div>
                                <div class="rendered-content">Memuat format...</div>

                                <!-- Copy button -->
                                <button onclick="copyResponse(this)" class="absolute top-2.5 right-2.5 text-slate-400 hover:text-[#008546] bg-slate-50 hover:bg-emerald-50 p-1.5 rounded-lg border border-slate-200 text-xs transition-colors opacity-0 group-hover:opacity-100" title="Salin jawaban">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">{{ $chat->created_at->format('H:i') }}</span>
                        </div>
                    </div>
                @endforeach

                <!-- Typing indicator -->
                <div id="typing-indicator" class="hidden flex items-start gap-3">
                    <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
                        <i class="fa-solid fa-robot text-xs animate-bounce"></i>
                    </div>
                    <div class="bg-white px-4 py-3.5 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 flex items-center gap-1.5">
                        <span class="text-xs text-slate-500 font-medium mr-1">AI sedang berpikir</span>
                        <div class="w-1.5 h-1.5 bg-[#008546] rounded-full animate-bounce"></div>
                        <div class="w-1.5 h-1.5 bg-[#008546] rounded-full animate-bounce" style="animation-delay: 0.15s"></div>
                        <div class="w-1.5 h-1.5 bg-[#008546] rounded-full animate-bounce" style="animation-delay: 0.3s"></div>
                    </div>
                </div>

                <!-- Scroll anchor -->
                <div id="scroll-anchor" class="h-2"></div>
            </div>
        </div>

        <!-- Input Bar Section -->
        <div class="bg-white/80 backdrop-blur-md border-t border-slate-100 shrink-0 px-4 sm:px-6 py-3.5">
            <div class="max-w-4xl mx-auto space-y-2.5">
                
                <!-- Quick Tools & Filter Bar -->
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-2">
                        <!-- Subject Selector -->
                        <div class="inline-flex items-center bg-slate-100 border border-slate-200/80 rounded-xl px-2.5 py-1 text-xs text-slate-600 focus-within:border-emerald-400 focus-within:ring-2 focus-within:ring-emerald-100 transition-all">
                            <i class="fa-solid fa-filter text-slate-400 text-[10px] mr-1.5"></i>
                            <select id="mapel-select" class="bg-transparent border-0 outline-none text-xs font-semibold text-slate-700 cursor-pointer pr-2">
                                <option value="Semua" {{ $selectedMapel === 'Semua' ? 'selected' : '' }}>Semua Mata Pelajaran</option>
                                @foreach($mapels as $m)
                                    <option value="{{ $m }}" {{ $selectedMapel === $m ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Quiz Button -->
                    <button type="button" id="btn-kuis" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#008546] bg-emerald-50 hover:bg-[#008546] hover:text-white px-3 py-1.5 rounded-xl border border-emerald-200/80 transition-all shadow-xs group">
                        <i class="fa-solid fa-brain group-hover:rotate-12 transition-transform"></i>
                        <span>Latihan Soal AI</span>
                    </button>
                </div>

                <!-- Chat Input Form -->
                <form id="chat-form" class="relative flex items-center">
                    <input type="text" id="pertanyaan" autocomplete="off" placeholder="Ketik pertanyaan seputar materi TKJ..." 
                           class="w-full bg-slate-50 border border-slate-200 text-slate-800 rounded-xl pl-4 pr-12 py-3 text-sm focus:bg-white focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20 transition-all outline-none placeholder-slate-400 font-medium">
                    
                    <button type="submit" id="submit-btn" 
                            class="absolute right-1.5 top-1.5 bottom-1.5 bg-[#008546] hover:bg-[#00703c] text-white rounded-lg px-3.5 transition-all flex items-center justify-center disabled:opacity-40 disabled:cursor-not-allowed shadow-sm hover:scale-105 active:scale-95">
                        <i class="fa-solid fa-paper-plane text-xs"></i>
                    </button>
                </form>

                <div class="text-center">
                    <span class="text-[10px] text-slate-400">RAG Chatbot Pintar • SMK N 1 Kinali</span>
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
            const scrollAnchor = document.getElementById('scroll-anchor');
            const sidebar = document.getElementById('sidebar');
            const openSidebarBtn = document.getElementById('open-sidebar-btn');
            const closeSidebarBtn = document.getElementById('close-sidebar-btn');
            const mobileBackdrop = document.getElementById('mobile-backdrop');

            // Render existing markdown messages
            document.querySelectorAll('.markdown-content').forEach(el => {
                const rawMarkdown = el.textContent;
                const renderedDiv = el.nextElementSibling;
                renderedDiv.innerHTML = marked.parse(rawMarkdown);
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
                inputField.value = '[LATIHAN_SOAL]';
                chatForm.dispatchEvent(new Event('submit'));
            }

            if (btnKuis) btnKuis.addEventListener('click', triggerKuis);
            if (btnQuickKuis) btnQuickKuis.addEventListener('click', triggerKuis);

            chatForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const question = inputField.value.trim();
                const selectedMapel = mapelSelect ? mapelSelect.value : 'Semua';
                
                if (!question) return;

                // Disable input
                inputField.disabled = true;
                submitBtn.disabled = true;
                if (btnKuis) btnKuis.disabled = true;
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
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ 
                            pertanyaan: question,
                            mapel: selectedMapel
                        })
                    });

                    const result = await response.json();
                    
                    typingIndicator.classList.add('hidden');

                    if (result.success) {
                        appendAIMessage(result.data.jawaban, result.data.created_at);
                    } else {
                        appendAIMessage('**Terjadi kesalahan:** ' + (result.message || 'Gagal memproses jawaban.'));
                    }
                } catch (error) {
                    typingIndicator.classList.add('hidden');
                    appendAIMessage('**Error koneksi ke server.** Mohon coba lagi beberapa saat.');
                    console.error('Error:', error);
                } finally {
                    inputField.disabled = false;
                    submitBtn.disabled = false;
                    if (btnKuis) btnKuis.disabled = false;
                    inputField.focus();
                    setTimeout(scrollToBottom, 50);
                }
            });

            function appendUserMessage(text) {
                const now = new Date();
                const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                const userAvatarUrl = "{{ auth()->user()->avatar_url ?? '' }}";
                const userInit = "{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}";

                const avatarHtml = userAvatarUrl 
                    ? `<img src="${userAvatarUrl}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-xl object-cover shrink-0 mt-0.5 shadow-sm border border-slate-300">`
                    : `<div class="w-8 h-8 rounded-xl bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5 shadow-sm border border-slate-300">${userInit}</div>`;

                const html = `
                    <div class="flex items-start justify-end gap-3 group animate-fade-in">
                        <div class="flex flex-col items-end max-w-[85%] sm:max-w-[75%]">
                            <div class="bg-[#008546] text-white px-4 py-3 rounded-2xl rounded-tr-xs shadow-md text-sm leading-relaxed">
                                <span class="whitespace-pre-wrap">${escapeHtml(text)}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">${timeStr}</span>
                        </div>
                        ${avatarHtml}
                    </div>
                `;
                typingIndicator.insertAdjacentHTML('beforebegin', html);
            }

            function appendAIMessage(markdownText, timeStr = null) {
                if (!timeStr) {
                    const now = new Date();
                    timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
                }

                const htmlContent = marked.parse(markdownText);

                const html = `
                    <div class="flex items-start gap-3 group animate-fade-in">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-[#008546] to-emerald-500 text-white flex items-center justify-center shrink-0 mt-0.5 shadow-md shadow-emerald-600/10">
                            <i class="fa-solid fa-robot text-xs"></i>
                        </div>
                        <div class="flex flex-col items-start max-w-[92%] sm:max-w-[85%] w-full">
                            <div class="bg-white p-4 sm:p-5 rounded-2xl rounded-tl-xs shadow-sm border border-slate-200/80 text-sm text-slate-800 prose prose-emerald max-w-none w-full relative">
                                ${htmlContent}
                                <button onclick="copyResponse(this)" class="absolute top-2.5 right-2.5 text-slate-400 hover:text-[#008546] bg-slate-50 hover:bg-emerald-50 p-1.5 rounded-lg border border-slate-200 text-xs transition-colors opacity-0 group-hover:opacity-100" title="Salin jawaban">
                                    <i class="fa-regular fa-copy"></i>
                                </button>
                            </div>
                            <span class="text-[10px] text-slate-400 mt-1 opacity-0 group-hover:opacity-100 transition-opacity font-medium">${timeStr}</span>
                        </div>
                    </div>
                `;
                typingIndicator.insertAdjacentHTML('beforebegin', html);
            }

            function escapeHtml(unsafe) {
                return unsafe
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
