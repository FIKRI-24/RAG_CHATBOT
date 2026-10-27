<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $module->kb_nomor }} - {{ $module->judul }}</title>

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
                <div class="flex items-center gap-2">
                    <a href="{{ route('siswa.modules.index') }}" class="text-xs font-medium text-slate-500 hover:text-[#008546] transition-colors flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left text-[10px]"></i>
                        <span>Katalog E-Modul</span>
                    </a>
                    <span class="text-slate-300">/</span>
                    <span class="text-xs font-bold text-slate-800 truncate max-w-[200px] sm:max-w-xs">{{ $module->kb_nomor }}</span>
                </div>
            </div>

            <a href="{{ route('siswa.dashboard') }}?mapel={{ urlencode($module->mapel) }}&module_id={{ $module->id }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 text-[#008546] border border-emerald-200/80 hover:bg-[#008546] hover:text-white transition-all shadow-2xs">
                <i class="fa-solid fa-robot text-xs"></i>
                <span>Tanya AI Soal Materi Ini</span>
            </a>
        </header>

        <!-- Body Content -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-8 space-y-6 max-w-5xl mx-auto w-full">
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

            <!-- Cyber-Network Glassmorphism Header (Opsi C) -->
            <div class="relative bg-slate-950 bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 rounded-3xl border border-emerald-500/20 shadow-2xl shadow-emerald-950/15 overflow-hidden backdrop-blur-xl">
                <!-- Subtle Cyber-Network Circuit & Ambient Glow -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-72 h-72 bg-teal-500/10 rounded-full blur-3xl pointer-events-none"></div>
                
                <svg class="absolute inset-0 w-full h-full pointer-events-none" style="opacity: 0.06;" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%">
                    <defs>
                        <pattern id="cyber-grid-detail" width="32" height="32" patternUnits="userSpaceOnUse">
                            <circle cx="2" cy="2" r="1.5" fill="#34d399" />
                            <path d="M 2 2 L 32 2 M 2 2 L 2 32" stroke="#34d399" stroke-width="0.5" stroke-dasharray="2 4" fill="none" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#cyber-grid-detail)" />
                </svg>

                <div class="relative z-10 space-y-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 backdrop-blur-md">
                            <i class="fa-solid fa-layer-group text-[10px]"></i>
                            {{ $module->kb_nomor ?? 'KB 1' }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white/10 text-slate-200 border border-white/15 backdrop-blur-md">
                            {{ $module->mapel }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-white/5 text-slate-300 border border-white/10 backdrop-blur-md">
                            <i class="fa-solid fa-school text-emerald-400 text-[10px]"></i>
                            SMK Negeri 1 Kinali
                        </span>
                    </div>

                    <h1 class="text-xl sm:text-3xl font-extrabold text-white tracking-tight leading-snug drop-shadow-sm">
                        {{ $module->judul }}
                    </h1>
                </div>
            </div>

            <!-- 1. TUJUAN PEMBELAJARAN (TP) CARD -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-3.5">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-bullseye text-xs"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">
                            Tujuan Pembelajaran (TP)
                        </h3>
                        <p class="text-xs text-slate-500">Capaian kompetensi yang diharapkan setelah mempelajari modul</p>
                    </div>
                </div>

                <div class="p-4 bg-slate-50/90 rounded-xl border border-slate-200 text-xs sm:text-sm text-slate-700 leading-relaxed whitespace-pre-line">
                    {{ $module->tp ?: 'Tujuan Pembelajaran untuk kegiatan belajar ini tercantum lengkap di dalam dokumen modul.' }}
                </div>
            </div>

            <!-- 2. MATERI DOKUMEN (DOWNLOAD FILE ASLI) CARD -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-file-pdf text-xs"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">
                            Materi Pembelajaran & Dokumen
                        </h3>
                        <p class="text-xs text-slate-500">Unduh bahan bacaan resmi dan panduan praktikum guru</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4.5 bg-slate-50/90 rounded-xl border border-slate-200">
                    <div>
                        <h4 class="font-bold text-sm text-slate-900">{{ $module->judul }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Dokumen materi ajar resmi dari pengajar TKJ SMK Negeri 1 Kinali</p>
                    </div>

                    <!-- Tombol Download File Asli (FR-16) -->
                    <a href="{{ route('modules.download', $module->id) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl transition-all shadow-xs flex-shrink-0 active:scale-95">
                        <i class="fa-solid fa-download"></i>
                        <span>Unduh Dokumen Materi</span>
                    </a>
                </div>
            </div>

            <!-- 3. VIDEO PEMBELAJARAN CARD -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-play text-xs"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">
                            Video Pembelajaran Pendukung
                        </h3>
                        <p class="text-xs text-slate-500">Visualisasi konsep, topologi, dan demonstrasi konfigurasi</p>
                    </div>
                </div>

                @if($module->video_url)
                    @php
                        $isYoutube = preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $module->video_url, $matches);
                        $youtubeId = $isYoutube ? $matches[1] : null;

                        $isDrive = preg_match('/drive\.google\.com\/file\/d\/([a-zA-Z0-9_\-]+)/', $module->video_url, $driveMatches);
                        $driveId = $isDrive ? $driveMatches[1] : null;
                    @endphp

                    @if($youtubeId)
                        <div class="aspect-video w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-950 shadow-md">
                            <iframe class="w-full h-full" src="https://www.youtube.com/embed/{{ $youtubeId }}" title="Video Pembelajaran" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                    @elseif($driveId)
                        <div class="aspect-video w-full rounded-2xl overflow-hidden border border-slate-200 bg-slate-950 shadow-md">
                            <iframe class="w-full h-full" src="https://drive.google.com/file/d/{{ $driveId }}/preview" title="Video Pembelajaran Google Drive" frameborder="0" allow="autoplay" allowfullscreen></iframe>
                        </div>
                    @endif

                    <div class="flex items-center justify-between p-3.5 bg-slate-50/90 rounded-xl border border-slate-200">
                        <span class="text-xs text-slate-600 truncate flex-1 mr-3">{{ $module->video_url }}</span>
                        <a href="{{ $module->video_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 px-4 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-colors flex-shrink-0">
                            Buka di Tab Baru <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                @else
                    <div class="p-6 bg-slate-50/90 rounded-xl border border-slate-200 text-center text-xs text-slate-500">
                        Belum ada video pembelajaran yang disematkan untuk KB ini.
                    </div>
                @endif

                @php $additionalVideos = $module->additionalVideos(); @endphp

                @if(!empty($additionalVideos))
                    <div class="pt-3 border-t border-slate-100">
                        <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider block mb-2.5">Video Pembelajaran Tambahan:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                            @foreach($additionalVideos as $extra)
                                <a href="{{ $extra['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-3 bg-slate-50 hover:bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 transition-colors">
                                    <span class="flex items-center gap-2.5">
                                        <i class="{{ $extra['icon'] }} {{ $extra['color'] }} text-sm"></i>
                                        <span>{{ $extra['title'] }}</span>
                                    </span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- 4. KUIS EVALUASI CARD -->
            <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
                <div class="flex items-center gap-3 border-b border-slate-100 pb-3.5">
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-200/80 font-bold text-xs flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-clipboard-check text-xs"></i>
                    </span>
                    <div>
                        <h3 class="font-bold text-sm text-slate-900 uppercase tracking-wider">
                            Kuis Evaluasi Pemahaman
                        </h3>
                        <p class="text-xs text-slate-500">Uji tingkat pemahaman materi setelah menyelesaikan modul</p>
                    </div>
                </div>

                @if($module->quiz?->is_published)
                    @if(isset($userAttempt) && $userAttempt)
                        <div class="p-5 bg-emerald-50/70 rounded-2xl border border-emerald-200 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-bold text-sm text-slate-900">
                                    {{ $module->quiz->title }}
                                </h4>
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-[#008546] text-white">
                                    Sudah Dikerjakan
                                </span>
                            </div>
                            <p class="text-xs text-slate-700">
                                Skor hasil pengerjaan Anda: <strong class="text-[#008546] text-sm">{{ $userAttempt->score }}/100</strong> (Benar {{ $userAttempt->correct_count }} dari {{ $userAttempt->question_count }} butir soal). Sesuai ketentuan, kuis hanya dapat dikerjakan satu kali.
                            </p>
                            <a href="{{ route('siswa.quiz-attempts.show', $userAttempt) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-white hover:bg-emerald-50 text-[#008546] text-xs font-bold rounded-xl border border-emerald-300 transition-colors shadow-2xs">
                                <span>Lihat Rincian Jawaban & Pembahasan →</span>
                            </a>
                        </div>
                    @else
                        <div class="p-5 bg-slate-50/90 rounded-2xl border border-slate-200 space-y-3">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h4 class="font-bold text-sm text-slate-900">{{ $module->quiz->title }}</h4>
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-slate-200 text-slate-800">
                                    1x Kesempatan
                                </span>
                            </div>
                            <p class="text-xs text-slate-600">Kuis pilihan ganda dari guru. <strong>Kuis hanya dapat dikerjakan 1 kali</strong> dan nilai langsung tercatat pada rekap guru.</p>
                            <a href="{{ route('siswa.modules.quiz.show', $module) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl transition-all shadow-xs active:scale-95">
                                <span>Kerjakan Kuis Objektif</span>
                            </a>
                        </div>
                    @endif
                @endif
                @if($module->kuis_url)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4.5 bg-slate-50/90 rounded-xl border border-slate-200">
                        <div>
                            <h4 class="font-bold text-sm text-slate-900">Evaluasi Eksternal Tambahan</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Tautan evaluasi tambahan untuk materi {{ $module->kb_nomor }}.</p>
                        </div>

                        <a href="{{ $module->kuis_url }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-colors flex-shrink-0">
                            <span>Mulai Kerjakan Kuis</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[10px]"></i>
                        </a>
                    </div>
                @elseif(! $module->quiz?->is_published)
                    <div class="p-6 bg-slate-50/90 rounded-xl border border-slate-200 text-center text-xs text-slate-500">
                        Belum ada kuis yang tersedia untuk KB ini. Anda dapat menggunakan fitur <strong>Latihan Soal AI</strong> di ruang tanya jawab.
                    </div>
                @endif
            </div>

            <!-- Bottom Back & Discussion Link -->
            <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-2 pb-8">
                <a href="{{ route('siswa.modules.index') }}" class="text-xs font-semibold text-slate-600 hover:text-slate-900 flex items-center gap-1.5">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Katalog E-Modul
                </a>

                <a href="{{ route('siswa.dashboard') }}?mapel={{ urlencode($module->mapel) }}&amp;module_id={{ $module->id }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-xl transition-colors shadow-2xs">
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
