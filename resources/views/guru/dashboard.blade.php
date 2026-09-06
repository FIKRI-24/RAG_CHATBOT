<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        
        <!-- Welcome Hero Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-[#008546] text-white p-6 sm:p-8 rounded-3xl shadow-lg relative overflow-hidden">
            <!-- Background Decorative Blobs -->
            <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold mb-3 backdrop-blur-xs">
                        <i class="fa-solid fa-graduation-cap text-emerald-400"></i> Panel Pengajar RAG AI • SMK Negeri 1 Kinali
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                        Selamat Datang, <span class="text-emerald-300">{{ auth()->user()->name }}</span>! 👋
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-2 max-w-2xl leading-relaxed">
                        Pantau interaksi belajar siswa secara <strong>real-time</strong>, kelola materi kurikulum Kegiatan Belajar (KB), dan awasi performa indeks pengetahuan kecerdasan buatan.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold px-4 py-2.5 rounded-xl shadow-md transition-all hover:scale-105 active:scale-95 border border-emerald-400/30">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Modul Baru</span>
                    </a>

                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 bg-white/10 hover:bg-white/20 text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all border border-white/20 backdrop-blur-xs">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Kelola Akun Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4-Column Balanced Real-Time KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            
            <!-- KPI 1: Total Modul -->
            <a href="{{ route('guru.modules.index') }}" class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm hover:border-emerald-300 hover:shadow-md transition-all group relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-[#008546] flex items-center justify-center text-lg font-bold border border-emerald-100 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-book-bookmark"></i>
                    </div>
                    <span class="text-[11px] text-slate-400 group-hover:text-[#008546] transition-colors font-medium flex items-center gap-1">
                        Lihat <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Modul Pembelajaran</h3>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalModul) }}</div>
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    <span>{{ $totalModulAktif }} materi aktif terbit</span>
                </p>
            </a>

            <!-- KPI 2: Total Chunks (Vektor RAG) -->
            <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg font-bold border border-blue-100">
                        <i class="fa-solid fa-cubes"></i>
                    </div>
                    <span class="text-[10px] bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-full border border-blue-200">
                        Vektor AI
                    </span>
                </div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Potongan Teks (Chunk)</h3>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalChunk) }}</div>
                <p class="text-[11px] text-slate-400 mt-1">
                    Rata-rata {{ $totalModul > 0 ? round($totalChunk / $totalModul) : 0 }} chunk / modul
                </p>
            </div>

            <!-- KPI 3: Total Siswa Terdaftar -->
            <a href="{{ route('guru.siswa.index') }}" class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm hover:border-purple-300 hover:shadow-md transition-all group relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-lg font-bold border border-purple-100 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <span class="text-[11px] text-slate-400 group-hover:text-purple-600 transition-colors font-medium flex items-center gap-1">
                        Kelola <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Siswa Terdaftar</h3>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalSiswa) }}</div>
                <p class="text-[11px] text-slate-400 mt-1 flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 inline-block"></span>
                    <span>{{ $siswaAktifCount }} aktif bertanya ({{ $siswaAktifPersen }}%)</span>
                </p>
            </a>

            <!-- KPI 4: Total Tanya Jawab AI (Realtime) -->
            <div class="bg-white p-5 rounded-3xl border-2 border-slate-100 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-11 h-11 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg font-bold border border-amber-100">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                    <span class="text-[10px] bg-emerald-50 text-[#008546] font-bold px-2 py-0.5 rounded-full border border-emerald-200">
                        Real-Time
                    </span>
                </div>
                <h3 class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Tanya Jawab AI Siswa</h3>
                <div class="text-2xl sm:text-3xl font-extrabold text-slate-900 mt-1">{{ number_format($totalChat) }}</div>
                <p class="text-[11px] text-slate-400 mt-1">
                    {{ $totalPertanyaanBiasa }} tanya materi • {{ $totalKuis }} kuis AI
                </p>
            </div>

        </div>

        <!-- Main Analytics Section (2-Column Grid: Chart + Performance) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Realtime Activity Bar Chart (col-span-8) -->
            <div class="lg:col-span-8 bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm flex flex-col justify-between space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base flex items-center gap-2">
                            <i class="fa-solid fa-chart-column text-[#008546]"></i>
                            <span>Tren Aktivitas Tanya Jawab Siswa & Unggah Modul</span>
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Data agregasi riil per bulan dari database percakapan chatbot dan kurikulum modul
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="fa-regular fa-calendar text-slate-500"></i> Tahun {{ $year }}
                        </span>
                    </div>
                </div>

                <div class="w-full h-[300px] relative">
                    <canvas id="realtimeMainChart"></canvas>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-[#008546] inline-block"></span>
                            <span>Pertanyaan Siswa (Chatbot)</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-3 h-3 rounded-md bg-[#38bdf8] inline-block"></span>
                            <span>Modul Terbit</span>
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400">Diperbarui secara real-time</span>
                </div>
            </div>

            <!-- Right: Real-time System Health & Participation Metrics (col-span-4) -->
            <div class="lg:col-span-4 bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm flex items-center gap-2">
                        <i class="fa-solid fa-microchip text-emerald-600"></i>
                        <span>Indikator Operasional Riil</span>
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Kesiapan RAG dan partisipasi belajar</p>
                </div>

                <div class="space-y-4">
                    <!-- Metric 1: Kesiapan Indeks Modul -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-book-open text-emerald-600"></i> Kesiapan Modul RAG
                            </span>
                            <span class="text-[#008546]">{{ $modulSiapPersen }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-[#008546] h-2 rounded-full transition-all duration-500" style="width: {{ $modulSiapPersen }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 block">
                            {{ $totalModul }} materi terindeks & siap dikonsumsi AI
                        </span>
                    </div>

                    <!-- Metric 2: Partisipasi Siswa Aktif -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-user-check text-blue-600"></i> Partisipasi Siswa
                            </span>
                            <span class="text-blue-600">{{ $siswaAktifPersen }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-500" style="width: {{ $siswaAktifPersen }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 block">
                            {{ $siswaAktifCount }} dari {{ $totalSiswa }} siswa telah mencoba chatbot
                        </span>
                    </div>

                    <!-- Metric 3: Rata-rata Pertanyaan per Siswa -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-slate-700 flex items-center gap-1.5">
                                <i class="fa-solid fa-calculator text-purple-600"></i> Intensitas Bertanya
                            </span>
                            <span class="text-purple-600">{{ $avgChatPerSiswa }}x</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-full h-2 overflow-hidden">
                            <div class="bg-purple-500 h-2 rounded-full transition-all duration-500" style="width: {{ min(100, (int)($avgChatPerSiswa * 10)) }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400 block">
                            Rata-rata {{ $avgChatPerSiswa }} pertanyaan diajukan per siswa
                        </span>
                    </div>

                    <!-- Metric 4: AI Engine Info -->
                    <div class="p-3.5 rounded-2xl bg-emerald-50/60 border border-emerald-200/70 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-robot text-emerald-600"></i> Model AI Aktif
                            </span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-600 text-white shadow-2xs">
                                Aktif
                            </span>
                        </div>
                        <p class="text-[11px] text-emerald-800 font-mono font-medium truncate">
                            {{ $activeAiModel }}
                        </p>
                        <span class="text-[10px] text-slate-500 block">
                            Strict RAG Grounding aktif (tanpa halusinasi)
                        </span>
                    </div>
                </div>

                <div class="pt-2 border-t border-slate-100 text-center">
                    <span class="text-[11px] text-slate-500 font-medium inline-flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i> Sistem E-Modul & AI Berjalan Optimal
                    </span>
                </div>
            </div>

        </div>

        <!-- 2-Column Bottom Section: Live Activity Feed + Latest Modules -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Live Feed Pertanyaan Siswa Terkini (col-span-7) -->
            <div class="lg:col-span-7 bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-7 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-sm border border-amber-100">
                            <i class="fa-solid fa-bolt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Aktivitas Tanya Jawab Siswa Terkini</h3>
                            <p class="text-[11px] text-slate-400">Pertanyaan riil siswa yang diajukan ke Chatbot AI</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block animate-pulse"></span> Live Stream
                    </span>
                </div>

                @if($recentChats->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentChats as $chat)
                            <div class="py-3 flex items-start gap-3.5 hover:bg-slate-50/80 p-2 rounded-2xl transition-colors">
                                <!-- Student Avatar -->
                                <div class="shrink-0 mt-0.5">
                                    @if($chat->siswa && $chat->siswa->avatar_url)
                                        <img src="{{ $chat->siswa->avatar_url }}" alt="{{ $chat->siswa->name }}" class="w-9 h-9 rounded-full object-cover shadow-xs border border-slate-200">
                                    @else
                                        <div class="w-9 h-9 rounded-full bg-gradient-to-tr from-slate-700 to-slate-800 text-white flex items-center justify-center font-bold text-xs shadow-xs">
                                            {{ strtoupper(substr($chat->siswa?->name ?? 'S', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>

                                <!-- Chat Content -->
                                <div class="flex-1 overflow-hidden">
                                    <div class="flex items-center justify-between gap-2">
                                        <h4 class="text-xs font-bold text-slate-900 truncate">
                                            {{ $chat->siswa?->name ?? 'Siswa Anonim' }}
                                        </h4>
                                        <span class="text-[10px] text-slate-400 whitespace-nowrap">
                                            {{ $chat->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if($chat->pertanyaan === '[LATIHAN_SOAL]')
                                        <p class="text-xs font-semibold text-purple-700 mt-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-clipboard-question text-purple-500"></i>
                                            <span>Mengerjakan Latihan Soal Interaktif AI</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-700 font-medium mt-0.5 line-clamp-2 leading-relaxed">
                                            "{{ $chat->pertanyaan }}"
                                        </p>
                                    @endif

                                    <!-- Modul Reference Badge -->
                                    <div class="mt-1.5 flex items-center gap-2">
                                        @if($chat->referensiChunk && $chat->referensiChunk->module)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-emerald-50 text-[#008546] border border-emerald-200">
                                                <i class="fa-solid fa-book-bookmark text-[9px]"></i>
                                                <span>{{ $chat->referensiChunk->module->kb_nomor }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-medium bg-slate-100 text-slate-500">
                                                <i class="fa-solid fa-circle-info text-[9px]"></i> Umum / Bebas
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-400 space-y-2">
                        <i class="fa-solid fa-comments text-3xl text-slate-300"></i>
                        <p class="text-xs font-medium">Belum ada aktivitas tanya jawab dari siswa.</p>
                    </div>
                @endif
            </div>

            <!-- Right: Ringkasan Modul Terkini Guru (col-span-5) -->
            <div class="lg:col-span-5 bg-white rounded-3xl border-2 border-slate-100 shadow-sm p-6 sm:p-7 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-emerald-50 text-[#008546] flex items-center justify-center font-bold text-sm border border-emerald-100">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-900">Modul Pembelajaran Guru</h3>
                            <p class="text-[11px] text-slate-400">Unit Kegiatan Belajar (KB) yang Anda ampu</p>
                        </div>
                    </div>
                    <a href="{{ route('guru.modules.index') }}" class="text-xs font-bold text-[#008546] hover:underline">
                        Semua Modul
                    </a>
                </div>

                @if($latestModules->count() > 0)
                    <div class="space-y-3">
                        @foreach($latestModules as $modul)
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 space-y-2 hover:border-emerald-300 transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-[#008546]">
                                                {{ $modul->kb_nomor }}
                                            </span>
                                            <span class="text-[10px] font-semibold text-slate-500">
                                                {{ $modul->mapel }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-900 line-clamp-1">
                                            {{ $modul->judul }}
                                        </h4>
                                    </div>
                                    @if($modul->status_indexing === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200 shrink-0">
                                            <i class="fa-solid fa-check"></i> Siap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 text-amber-600 border border-amber-200 shrink-0">
                                            <i class="fa-solid fa-spinner animate-spin"></i> Proses
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-200/60">
                                    <span>{{ $modul->chunks_count }} Potongan Teks AI</span>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('guru.modules.edit', $modul->id) }}" class="text-[#008546] hover:underline font-bold text-xs">
                                            Edit
                                        </a>
                                        <span>•</span>
                                        <a href="{{ route('guru.modules.show', $modul->id) }}" class="text-blue-600 hover:underline font-bold text-xs">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-400 space-y-2">
                        <i class="fa-solid fa-cloud-arrow-up text-3xl text-slate-300"></i>
                        <p class="text-xs font-medium">Belum ada modul yang diunggah.</p>
                        <a href="{{ route('guru.modules.create') }}" class="inline-block mt-2 text-xs font-bold text-[#008546] underline">
                            Upload Modul Pertama
                        </a>
                    </div>
                @endif
            </div>

        </div>

    </div>

    <!-- Chart.js Real-time Setup Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctxMain = document.getElementById('realtimeMainChart');
            if (!ctxMain) return;

            const chartChatsData = @json($chartChats);
            const chartModulesData = @json($chartModules);

            const ctx = ctxMain.getContext('2d');
            
            let gradientChats = ctx.createLinearGradient(0, 0, 0, 250);
            gradientChats.addColorStop(0, '#008546');
            gradientChats.addColorStop(1, '#052e16');

            let gradientModules = ctx.createLinearGradient(0, 0, 0, 250);
            gradientModules.addColorStop(0, '#38bdf8');
            gradientModules.addColorStop(1, '#0284c7');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        {
                            label: 'Pertanyaan Siswa (Chatbot)',
                            data: chartChatsData,
                            backgroundColor: gradientChats,
                            borderRadius: 8,
                            barPercentage: 0.6,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Modul Terbit',
                            data: chartModulesData,
                            backgroundColor: gradientModules,
                            borderRadius: 8,
                            barPercentage: 0.6,
                            categoryPercentage: 0.7
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { 
                            display: true,
                            position: 'top',
                            labels: {
                                font: { size: 11, family: 'Inter', weight: '600' },
                                color: '#475569',
                                usePointStyle: true,
                                boxWidth: 8
                            }
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            titleFont: { size: 12, family: 'Inter' },
                            bodyFont: { size: 11, family: 'Inter' },
                            padding: 10,
                            cornerRadius: 10,
                            callbacks: {
                                label: function(context) {
                                    return context.dataset.label + ': ' + context.raw + (context.datasetIndex === 0 ? ' pertanyaan' : ' modul');
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { 
                                color: '#64748b', 
                                font: { size: 10, family: 'Inter' },
                                stepSize: 5
                            },
                            grid: { color: '#f1f5f9' },
                            border: { display: false }
                        },
                        x: {
                            ticks: { 
                                color: '#334155', 
                                font: { size: 10, family: 'Inter', weight: '600' }
                            },
                            grid: { display: false },
                            border: { display: false }
                        }
                    }
                }
            });
        });
    </script>
</x-premium-layout>
