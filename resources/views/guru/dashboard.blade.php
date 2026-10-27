<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        
        <!-- Institutional Console Header -->
        <div class="relative bg-slate-900 text-white rounded-xl border border-slate-800 p-6 sm:p-7 shadow-2xs overflow-hidden">
            <div class="absolute top-0 inset-x-0 h-1 bg-[#008546]"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 border border-slate-700 text-[11px] font-mono mb-2.5">
                        <i class="fa-solid fa-server text-emerald-400 text-[10px]"></i>
                        <span>TELEMETRI PENGAJAR RAG • SMK NEGERI 1 KINALI</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                        Selamat Datang, {{ auth()->user()->name }}
                    </h1>
                    <p class="text-slate-300 text-xs sm:text-sm mt-1.5 max-w-2xl leading-relaxed">
                        Ringkasan operasional materi Kegiatan Belajar (KB), indeks vektor dokumen, dan aktivitas tanya jawab siswa pada sistem pembelajaran TKJ.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('guru.siswa.export') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg transition-colors border border-slate-700" title="Ekspor Rekapitulasi Pembelajaran ke Excel (Times New Roman 12pt)">
                        <i class="fa-solid fa-file-excel text-emerald-400"></i>
                        <span>Ekspor Excel</span>
                    </a>

                    <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold px-4 py-2.5 rounded-lg transition-colors">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Upload Modul Baru</span>
                    </a>

                    <a href="{{ route('guru.siswa.index') }}" class="inline-flex items-center gap-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold px-3.5 py-2.5 rounded-lg transition-colors border border-slate-700">
                        <i class="fa-solid fa-user-plus"></i>
                        <span>Kelola Akun Siswa</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- 4-Column Architectural Telemetry Strip -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- KPI 1: Total Modul -->
            <a href="{{ route('guru.modules.index') }}" class="bg-white p-5 rounded-xl border border-slate-200/90 hover:border-[#008546] transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Modul Pembelajaran</span>
                    <span class="text-[11px] text-slate-400 group-hover:text-[#008546] transition-colors font-semibold flex items-center gap-1">
                        Kelola <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">{{ number_format($totalModul) }}</div>
                <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#008546] inline-block"></span>
                    <span>{{ $totalModulAktif }} materi aktif terbit</span>
                </p>
            </a>

            <!-- KPI 2: Total Chunks (Vektor RAG) -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/90">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Potongan Teks (Chunk)</span>
                    <span class="text-[10px] font-mono bg-slate-100 text-slate-700 font-semibold px-2 py-0.5 rounded border border-slate-200">
                        VEKTOR AI
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">{{ number_format($totalChunk) }}</div>
                <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    Rata-rata {{ $totalModul > 0 ? round($totalChunk / $totalModul) : 0 }} chunk / modul
                </p>
            </div>

            <!-- KPI 3: Total Siswa Terdaftar -->
            <a href="{{ route('guru.siswa.index') }}" class="bg-white p-5 rounded-xl border border-slate-200/90 hover:border-[#008546] transition-colors group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Siswa Terdaftar</span>
                    <span class="text-[11px] text-slate-400 group-hover:text-[#008546] transition-colors font-semibold flex items-center gap-1">
                        Data <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">{{ number_format($totalSiswa) }}</div>
                <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                    <span>{{ $siswaAktifCount }} aktif bertanya ({{ $siswaAktifPersen }}%)</span>
                </p>
            </a>

            <!-- KPI 4: Total Tanya Jawab AI -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/90">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tanya Jawab Siswa</span>
                    <span class="text-[10px] font-mono bg-emerald-50 text-[#008546] font-semibold px-2 py-0.5 rounded border border-emerald-200">
                        TERCATAT
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">{{ number_format($totalChat) }}</div>
                <p class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    {{ $totalPertanyaanBiasa }} tanya materi • {{ $totalKuis }} kuis AI
                </p>
            </div>

        </div>

        <!-- Main Analytics Section (2-Column Grid: Chart + Performance) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Activity Bar Chart (col-span-8) -->
            <div class="lg:col-span-8 bg-white p-6 rounded-xl border border-slate-200/90 flex flex-col justify-between space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-4 border-b border-slate-100">
                    <div>
                        <h3 class="font-bold text-slate-900 text-sm sm:text-base">
                            Tren Aktivitas Tanya Jawab Siswa & Unggah Modul
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Data agregasi bulanan dari log percakapan chatbot dan publikasi modul
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-mono font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                            <i class="fa-regular fa-calendar text-slate-500"></i> {{ $year }}
                        </span>
                    </div>
                </div>

                <div class="w-full h-[300px] relative">
                    <canvas id="realtimeMainChart"></canvas>
                </div>

                <div class="pt-3 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500">
                    <div class="flex items-center gap-4">
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-xs bg-[#008546] inline-block"></span>
                            <span>Pertanyaan Siswa (Chatbot)</span>
                        </span>
                        <span class="flex items-center gap-1.5">
                            <span class="w-2.5 h-2.5 rounded-xs bg-slate-600 inline-block"></span>
                            <span>Modul Terbit</span>
                        </span>
                    </div>
                    <span class="text-[11px] text-slate-400">Diperbarui saat halaman dimuat</span>
                </div>
            </div>

            <!-- Right: System Health & Participation Metrics (col-span-4) -->
            <div class="lg:col-span-4 bg-white p-6 rounded-xl border border-slate-200/90 space-y-5">
                <div class="pb-3 border-b border-slate-100">
                    <h3 class="font-bold text-slate-900 text-sm">
                        Indikator Operasional Riil
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Kesiapan indeks RAG dan partisipasi kelas</p>
                </div>

                <div class="space-y-3.5">
                    <!-- Metric 1: Kesiapan Indeks Modul -->
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-700">Kesiapan Modul RAG</span>
                            <span class="text-[#008546] font-mono tabular-nums">{{ $modulSiapPersen }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-xs h-1.5 overflow-hidden">
                            <div class="bg-[#008546] h-1.5 transition-all duration-500" style="width: {{ $modulSiapPersen }}%"></div>
                        </div>
                        <span class="text-[11px] text-slate-500 block">
                            {{ $totalModul }} materi terindeks & siap dirujuk AI
                        </span>
                    </div>

                    <!-- Metric 2: Partisipasi Siswa Aktif -->
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-700">Partisipasi Siswa</span>
                            <span class="text-slate-900 font-mono tabular-nums">{{ $siswaAktifPersen }}%</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-xs h-1.5 overflow-hidden">
                            <div class="bg-slate-700 h-1.5 transition-all duration-500" style="width: {{ $siswaAktifPersen }}%"></div>
                        </div>
                        <span class="text-[11px] text-slate-500 block">
                            {{ $siswaAktifCount }} dari {{ $totalSiswa }} siswa telah berinteraksi
                        </span>
                    </div>

                    <!-- Metric 3: Rata-rata Pertanyaan per Siswa -->
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80 space-y-2">
                        <div class="flex items-center justify-between text-xs font-semibold">
                            <span class="text-slate-700">Intensitas Bertanya</span>
                            <span class="text-slate-900 font-mono tabular-nums">{{ $avgChatPerSiswa }}x</span>
                        </div>
                        <div class="w-full bg-slate-200 rounded-xs h-1.5 overflow-hidden">
                            <div class="bg-slate-700 h-1.5 transition-all duration-500" style="width: {{ min(100, (int)($avgChatPerSiswa * 10)) }}%"></div>
                        </div>
                        <span class="text-[11px] text-slate-500 block">
                            Rata-rata {{ $avgChatPerSiswa }} pertanyaan diajukan per siswa
                        </span>
                    </div>

                    <!-- Metric 4: AI Engine Info -->
                    <div class="p-3.5 rounded-lg bg-slate-900 text-white space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-slate-300">Model AI Aktif</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-[#008546] text-white">
                                AKTIF
                            </span>
                        </div>
                        <p class="text-xs text-emerald-300 font-mono truncate">
                            {{ $activeAiModel }}
                        </p>
                        <span class="text-[10px] text-slate-400 block">
                            Strict RAG Grounding aktif (berbasis dokumen guru)
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <!-- 2-Column Bottom Section: Activity Feed + Latest Modules -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Left: Pertanyaan Siswa Terkini (col-span-7) -->
            <div class="lg:col-span-7 bg-white rounded-xl border border-slate-200/90 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Aktivitas Tanya Jawab Siswa Terkini</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Pertanyaan riil siswa yang diajukan ke asisten AI</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a href="{{ route('guru.chat-activities.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-[#008546] hover:bg-emerald-100 border border-emerald-200 transition-colors" title="Buka Halaman Lengkap & Unduh Rekap Mingguan">
                            <span>Buka Rekap</span>
                            <i class="fa-solid fa-arrow-right text-[10px]"></i>
                        </a>
                    </div>
                </div>

                @if($recentChats->count() > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($recentChats as $chat)
                            <div class="py-3 flex items-start gap-3.5 hover:bg-slate-50/80 px-2 rounded-lg transition-colors">
                                <!-- Student Avatar -->
                                <div class="shrink-0 mt-0.5">
                                    @if($chat->siswa && $chat->siswa->avatar_url)
                                        <img src="{{ $chat->siswa->avatar_url }}" alt="{{ $chat->siswa->name }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200">
                                    @else
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-xs">
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
                                        <span class="text-[10px] font-mono text-slate-400 whitespace-nowrap">
                                            {{ $chat->created_at->diffForHumans() }}
                                        </span>
                                    </div>

                                    @if($chat->pertanyaan === '[LATIHAN_SOAL]')
                                        <p class="text-xs font-semibold text-[#008546] mt-1 flex items-center gap-1.5">
                                            <i class="fa-solid fa-clipboard-question text-xs"></i>
                                            <span>Mengerjakan Latihan Soal Interaktif AI</span>
                                        </p>
                                    @else
                                        <p class="text-xs text-slate-700 mt-0.5 line-clamp-2 leading-relaxed">
                                            "{{ $chat->pertanyaan }}"
                                        </p>
                                    @endif

                                    <!-- Modul Reference Badge -->
                                    <div class="mt-1.5 flex items-center gap-2">
                                        @if($chat->sourceLabels())
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono font-semibold bg-emerald-50 text-[#008546] border border-emerald-200">
                                                <span>{{ implode(', ', $chat->sourceLabels()) }}</span>
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-mono bg-slate-100 text-slate-600">
                                                Umum / Katalog
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-400 space-y-1.5">
                        <p class="text-xs font-medium text-slate-500">Belum ada aktivitas tanya jawab dari siswa.</p>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Memuat 5 pertanyaan terbaru</span>
                    <a href="{{ route('guru.chat-activities.index') }}" class="font-semibold text-[#008546] hover:text-emerald-700 inline-flex items-center gap-1.5 transition-colors">
                        <span>Lihat Seluruh Aktivitas & Unduh Rekap Mingguan (.xlsx)</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

            <!-- Right: Ringkasan Modul Terkini Guru (col-span-5) -->
            <div class="lg:col-span-5 bg-white rounded-xl border border-slate-200/90 p-6 space-y-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Modul Pembelajaran Guru</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Unit Kegiatan Belajar (KB) yang Anda ampu</p>
                    </div>
                    <a href="{{ route('guru.modules.index') }}" class="text-xs font-semibold text-[#008546] hover:underline">
                        Semua Modul →
                    </a>
                </div>

                @if($latestModules->count() > 0)
                    <div class="space-y-2.5">
                        @foreach($latestModules as $modul)
                            <div class="p-3.5 rounded-lg bg-slate-50 border border-slate-200/80 space-y-2 hover:border-slate-300 transition-colors">
                                <div class="flex items-start justify-between gap-2">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-mono font-bold bg-slate-900 text-white">
                                                {{ $modul->kb_nomor }}
                                            </span>
                                            <span class="text-[11px] font-medium text-slate-500">
                                                {{ $modul->mapel }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-900 line-clamp-1">
                                            {{ $modul->judul }}
                                        </h4>
                                    </div>
                                    @if($modul->status_indexing === 'completed')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-[#008546] border border-emerald-200 shrink-0">
                                            Siap
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200 shrink-0">
                                            Proses
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between text-[11px] text-slate-500 pt-2 border-t border-slate-200/60">
                                    <span class="font-mono">{{ $modul->chunks_count }} chunk</span>
                                    <div class="flex items-center gap-2.5">
                                        <a href="{{ route('guru.modules.edit', $modul->id) }}" class="text-slate-700 hover:text-[#008546] font-semibold text-xs">
                                            Edit
                                        </a>
                                        <span class="text-slate-300">|</span>
                                        <a href="{{ route('guru.modules.show', $modul->id) }}" class="text-[#008546] hover:underline font-semibold text-xs">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="p-8 text-center text-slate-500 space-y-2">
                        <p class="text-xs font-medium">Belum ada modul yang diunggah.</p>
                        <a href="{{ route('guru.modules.create') }}" class="inline-block mt-1 text-xs font-bold text-[#008546] underline">
                            Upload Modul Pertama
                        </a>
                    </div>
                @endif

                <div class="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
                    <span class="text-slate-400 text-[11px]">Daftar modul pembelajaran</span>
                    <a href="{{ route('guru.modules.index') }}" class="font-semibold text-[#008546] hover:text-emerald-700 inline-flex items-center gap-1.5 transition-colors">
                        <span>Kelola Seluruh Modul RAG</span>
                        <i class="fa-solid fa-arrow-right text-[10px]"></i>
                    </a>
                </div>
            </div>

        </div>

    </div>

    <!-- Chart.js Setup Script -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const ctxMain = document.getElementById('realtimeMainChart');
            if (!ctxMain) return;

            const chartChatsData = @json($chartChats);
            const chartModulesData = @json($chartModules);

            const ctx = ctxMain.getContext('2d');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                    datasets: [
                        {
                            label: 'Pertanyaan Siswa (Chatbot)',
                            data: chartChatsData,
                            backgroundColor: '#008546',
                            borderRadius: 4,
                            barPercentage: 0.65,
                            categoryPercentage: 0.7
                        },
                        {
                            label: 'Modul Terbit',
                            data: chartModulesData,
                            backgroundColor: '#475569',
                            borderRadius: 4,
                            barPercentage: 0.65,
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
                            cornerRadius: 6,
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
