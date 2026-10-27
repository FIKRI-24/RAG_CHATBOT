<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200/90 shadow-2xs">
            <div>
                <div class="inline-flex items-center gap-2 px-2.5 py-1 rounded-md bg-emerald-50 text-[#008546] border border-emerald-200 text-[11px] font-mono mb-2">
                    <i class="fa-solid fa-calendar-week text-[10px]"></i>
                    <span>PERIODE 7 HARI TERAKHIR: {{ $metrics['start_date']->translatedFormat('d M Y') }} – {{ $metrics['end_date']->translatedFormat('d M Y') }}</span>
                </div>
                <h1 class="text-xl font-bold text-slate-800 flex items-center gap-2.5">
                    <i class="fa-solid fa-comments text-[#008546]"></i>
                    <span>Aktivitas Tanya Jawab Siswa</span>
                </h1>
                <p class="text-xs text-slate-500 mt-1">
                    Log riwayat pertanyaan materi yang diajukan siswa ke asisten AI RAG dengan fitur unduh rekapan mingguan resmi.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 flex-wrap">
                <a href="{{ route('guru.chat-activities.export-weekly', ['search' => request('search')]) }}" 
                   class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white px-4 py-2.5 rounded-xl font-semibold text-xs transition-colors shadow-sm border border-emerald-600"
                   title="Unduh Rekapitulasi 7 Hari Terakhir (.xlsx) dengan Format Resmi Times New Roman 12pt">
                    <i class="fa-solid fa-file-excel text-sm text-emerald-200"></i>
                    <span>Unduh Rekap Mingguan (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 3 KPI Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <!-- Metric 1: Pertanyaan 7 Hari Terakhir -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Pertanyaan Minggu Ini</span>
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-[#008546] flex items-center justify-center text-xs border border-emerald-200">
                        <i class="fa-solid fa-calendar-day"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">
                    {{ number_format($metrics['weekly_questions']) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 pt-1.5 border-t border-slate-100 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 inline-block"></span>
                    <span>7 hari terakhir tercatat</span>
                </p>
            </div>

            <!-- Metric 2: Siswa Aktif Bertanya (Mingguan) -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Siswa Aktif Bertanya</span>
                    <span class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs border border-blue-200">
                        <i class="fa-solid fa-users"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">
                    {{ number_format($metrics['weekly_students']) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 pt-1.5 border-t border-slate-100 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500 inline-block"></span>
                    <span>Siswa unik berinteraksi minggu ini</span>
                </p>
            </div>

            <!-- Metric 3: Total Sepanjang Waktu -->
            <div class="bg-white p-5 rounded-xl border border-slate-200/90 shadow-2xs">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Total Semua Riwayat</span>
                    <span class="w-8 h-8 rounded-lg bg-slate-100 text-slate-700 flex items-center justify-center text-xs border border-slate-200">
                        <i class="fa-solid fa-database"></i>
                    </span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold text-slate-900 font-mono tabular-nums">
                    {{ number_format($metrics['total_all_time']) }}
                </div>
                <p class="text-[11px] text-slate-500 mt-1.5 pt-1.5 border-t border-slate-100 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                    <span>Akumulasi log percakapan</span>
                </p>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-xl border border-slate-200/90 shadow-2xs">
            <form method="GET" action="{{ route('guru.chat-activities.index') }}" class="flex flex-col sm:flex-row items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-3 w-full sm:w-auto flex-1">
                    <!-- Search Input -->
                    <div class="relative w-full sm:w-80">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari siswa, pertanyaan, email, atau kelas..." 
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 pl-9 pr-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-3 text-slate-400 text-xs"></i>
                    </div>

                    <!-- Module Filter -->
                    <div class="w-full sm:w-60">
                        <select name="module_id" onchange="this.form.submit()" 
                                class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:border-[#008546] focus:ring-2 focus:ring-emerald-500/20">
                            <option value="">-- Semua Ruang Lingkup Modul --</option>
                            @foreach($modules as $m)
                                <option value="{{ $m->id }}" {{ request('module_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->kb_nomor }} - {{ $m->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" class="bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors">
                        Filter
                    </button>

                    @if(request('search') || request('module_id'))
                        <a href="{{ route('guru.chat-activities.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline px-2">
                            Reset
                        </a>
                    @endif
                </div>

                <div class="text-xs text-slate-500 shrink-0 w-full sm:w-auto text-right">
                    Menampilkan <strong>{{ $chats->total() }}</strong> aktivitas pertanyaan
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white rounded-xl border border-slate-200/90 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-700">
                    <thead class="bg-slate-50 text-[11px] font-semibold text-slate-600 uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 w-52">Siswa</th>
                            <th class="py-3 px-4 w-40">Waktu (WIB)</th>
                            <th class="py-3 px-4 w-44">Modul / KB</th>
                            <th class="py-3 px-4">Pertanyaan Siswa</th>
                            <th class="py-3 px-4">Jawaban Asisten AI</th>
                            <th class="py-3 px-4 w-40">Rujukan Sumber</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($chats as $index => $chat)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- No -->
                                <td class="py-3.5 px-4 text-center font-mono text-slate-400 align-top">
                                    {{ $chats->firstItem() + $index }}
                                </td>

                                <!-- Siswa -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex items-start gap-2.5">
                                        <div class="shrink-0 mt-0.5">
                                            @if($chat->siswa && $chat->siswa->avatar_url)
                                                <img src="{{ $chat->siswa->avatar_url }}" alt="{{ $chat->siswa->name }}" class="w-7 h-7 rounded-lg object-cover border border-slate-200">
                                            @else
                                                <div class="w-7 h-7 rounded-lg bg-slate-800 text-white flex items-center justify-center font-bold text-[10px]">
                                                    {{ strtoupper(substr($chat->siswa?->name ?? 'S', 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 leading-tight">
                                                {{ $chat->siswa?->name ?? 'Siswa Anonim' }}
                                            </p>
                                            <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                                                {{ $chat->siswa?->class_name ?: 'Kelas -' }}
                                                @if($chat->siswa?->student_number)
                                                    • {{ $chat->siswa->student_number }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Waktu -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="space-y-0.5">
                                        <p class="font-mono text-slate-900 font-medium">
                                            {{ $chat->created_at ? $chat->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y') : '-' }}
                                        </p>
                                        <p class="text-[11px] font-mono text-slate-400">
                                            {{ $chat->created_at ? $chat->created_at->timezone(config('app.display_timezone'))->format('H:i') : '' }} WIB
                                            <span class="text-slate-300">({{ $chat->created_at?->diffForHumans() }})</span>
                                        </p>
                                    </div>
                                </td>

                                <!-- Modul -->
                                <td class="py-3.5 px-4 align-top">
                                    @if($chat->sourceLabels())
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-emerald-50 text-[#008546] border border-emerald-200">
                                            <i class="fa-solid fa-book-bookmark text-[9px]"></i>
                                            <span>{{ implode(', ', $chat->sourceLabels()) }}</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                            Umum / Katalog
                                        </span>
                                    @endif
                                </td>

                                <!-- Pertanyaan Siswa -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-medium text-slate-900 leading-relaxed max-w-sm">
                                        "{{ $chat->pertanyaan }}"
                                    </div>
                                </td>

                                <!-- Jawaban AI -->
                                <td class="py-3.5 px-4 align-top">
                                    <div class="text-slate-600 line-clamp-3 leading-relaxed max-w-md text-[11.5px]">
                                        {{ $chat->jawaban }}
                                    </div>
                                </td>

                                <!-- Rujukan Sumber -->
                                <td class="py-3.5 px-4 align-top">
                                    @php
                                        $sources = $chat->sources ?? [];
                                    @endphp
                                    @if(!empty($sources) && is_array($sources))
                                        <div class="flex flex-col gap-1">
                                            @foreach($sources as $s)
                                                @php
                                                    $label = trim(($s['kb_nomor'] ?? '').' '.($s['judul'] ?? ''));
                                                @endphp
                                                @if($label)
                                                    <span class="inline-flex items-center gap-1 text-[10px] font-mono text-slate-600 bg-slate-100 px-2 py-0.5 rounded border border-slate-200 truncate" title="{{ $label }}">
                                                        <i class="fa-solid fa-file-lines text-[9px] text-[#008546]"></i>
                                                        <span class="truncate">{{ $label }}</span>
                                                    </span>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-[11px]">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 space-y-2">
                                    <i class="fa-solid fa-comments text-3xl text-slate-300"></i>
                                    <p class="text-sm font-medium text-slate-600">Tidak ada data aktivitas pertanyaan siswa.</p>
                                    <p class="text-xs text-slate-400">Pertanyaan siswa yang diajukan ke AI akan otomatis tercatat di halaman ini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($chats->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $chats->links() }}
                </div>
            @endif
        </div>

    </div>
</x-premium-layout>
