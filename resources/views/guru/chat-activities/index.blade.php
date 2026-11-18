<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-10">
        
        <!-- Header Halaman Bersih & Tegas -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-5 border-b border-slate-200">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                    Aktivitas Tanya Jawab Siswa
                </h1>
                <p class="text-sm text-slate-500 mt-1">
                    Rekap riwayat interaksi pertanyaan siswa ke asisten AI, rujukan dokumen modul, dan evaluasi pemahaman materi.
                </p>
            </div>
            
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('guru.chat-activities.export-weekly', ['search' => request('search')]) }}" 
                   class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white px-4 py-2.5 rounded-lg text-sm font-semibold transition-colors shadow-2xs"
                   title="Unduh rekapitulasi data 7 hari terakhir dalam format Excel">
                    <i class="fa-solid fa-file-excel text-sm"></i>
                    <span>Unduh Rekap Mingguan (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- Ringkasan Metrik (Layout Rata & Minimalis) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 divide-y sm:divide-y-0 sm:divide-x divide-slate-200 bg-white rounded-xl border border-slate-200 shadow-2xs">
            <div class="p-5 space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Pertanyaan 7 Hari Terakhir</p>
                <p class="text-2xl font-bold font-mono text-slate-900">{{ number_format($metrics['weekly_questions']) }}</p>
                <p class="text-xs text-slate-400">Periode {{ $metrics['start_date']->translatedFormat('d M') }} – {{ $metrics['end_date']->translatedFormat('d M Y') }}</p>
            </div>

            <div class="p-5 space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Siswa Aktif Minggu Ini</p>
                <p class="text-2xl font-bold font-mono text-slate-900">{{ number_format($metrics['weekly_students']) }}</p>
                <p class="text-xs text-slate-400">Jumlah siswa berinteraksi aktif</p>
            </div>

            <div class="p-5 space-y-1">
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">Total Akumulasi Log</p>
                <p class="text-2xl font-bold font-mono text-slate-900">{{ number_format($metrics['total_all_time']) }}</p>
                <p class="text-xs text-slate-400">Semua riwayat tanya jawab tersimpan</p>
            </div>
        </div>

        <!-- Toolbar Pencarian & Filter -->
        <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-2xs">
            <form method="GET" action="{{ route('guru.chat-activities.index') }}" class="flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 flex-1">
                    <!-- Search Input -->
                    <div class="relative flex-1 min-w-[240px]">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari siswa, kelas, NIS, atau pertanyaan..." 
                               class="w-full bg-slate-50/70 border border-slate-300 text-slate-900 text-sm rounded-lg py-2 pl-9 pr-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-1 focus:ring-[#008546] transition-colors">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                    </div>

                    <!-- Module Select -->
                    <div class="w-full sm:w-64">
                        <select name="module_id" onchange="this.form.submit()" 
                                class="w-full bg-slate-50/70 border border-slate-300 text-slate-900 text-sm rounded-lg py-2 px-3 outline-none focus:bg-white focus:border-[#008546] focus:ring-1 focus:ring-[#008546] transition-colors">
                            <option value="">Semua Modul KB</option>
                            @foreach($modules as $m)
                                <option value="{{ $m->id }}" {{ request('module_id') == $m->id ? 'selected' : '' }}>
                                    {{ $m->kb_nomor }} - {{ $m->judul }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                            Saring
                        </button>

                        @if(request('search') || request('module_id'))
                            <a href="{{ route('guru.chat-activities.index') }}" class="text-xs text-slate-500 hover:text-slate-800 underline px-2">
                                Reset
                            </a>
                        @endif
                    </div>
                </div>

                <div class="text-xs text-slate-500 self-end md:self-center">
                    Total <strong class="text-slate-800 font-mono">{{ $chats->total() }}</strong> rekaman ditemukan
                </div>
            </form>
        </div>

        <!-- Tabel Data Utama (Standar Data Table Bersih, Tanpa Kotak-dalam-Kotak) -->
        <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm text-slate-700">
                    <thead class="bg-slate-50 text-slate-600 text-xs font-semibold uppercase tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="py-3.5 px-4 w-12 text-center">No</th>
                            <th class="py-3.5 px-4 w-56">Siswa</th>
                            <th class="py-3.5 px-4 w-36">Waktu</th>
                            <th class="py-3.5 px-4 w-44">Modul / KB</th>
                            <th class="py-3.5 px-5 min-w-[280px]">Pertanyaan Siswa</th>
                            <th class="py-3.5 px-5 min-w-[340px]">Jawaban Asisten AI</th>
                            <th class="py-3.5 px-4 w-48">Rujukan Sumber</th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @forelse($chats as $index => $chat)
                            <tr class="hover:bg-slate-50/60 transition-colors align-top">
                                
                                <!-- No -->
                                <td class="py-4 px-4 text-center text-xs font-mono text-slate-400">
                                    {{ $chats->firstItem() + $index }}
                                </td>

                                <!-- Identitas Siswa -->
                                <td class="py-4 px-4">
                                    <div class="flex items-center gap-3">
                                        @if($chat->siswa && $chat->siswa->avatar_url)
                                            <img src="{{ $chat->siswa->avatar_url }}" alt="{{ $chat->siswa->name }}" class="w-8 h-8 rounded-full object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-8 h-8 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-xs shrink-0">
                                                {{ strtoupper(substr($chat->siswa?->name ?? 'S', 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-semibold text-slate-900 text-sm truncate leading-snug">
                                                {{ $chat->siswa?->name ?? 'Siswa Anonim' }}
                                            </p>
                                            <p class="text-xs text-slate-500 truncate mt-0.5">
                                                {{ $chat->siswa?->class_name ?: 'Kelas -' }}
                                                @if($chat->siswa?->student_number)
                                                    • {{ $chat->siswa->student_number }}
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Waktu -->
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <div class="text-xs font-medium text-slate-800">
                                        {{ $chat->created_at ? $chat->created_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y') : '-' }}
                                    </div>
                                    <div class="text-xs text-slate-400 font-mono mt-0.5">
                                        {{ $chat->created_at ? $chat->created_at->timezone(config('app.display_timezone'))->format('H:i') : '' }} WIB
                                    </div>
                                </td>

                                <!-- Modul / KB -->
                                <td class="py-4 px-4">
                                    @php $labels = $chat->sourceLabels(); @endphp
                                    @if(!empty($labels))
                                        <span class="inline-block px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                                            {{ implode(', ', $labels) }}
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400">
                                            Umum / Bebas
                                        </span>
                                    @endif
                                </td>

                                <!-- Pertanyaan Siswa (Langsung Dibaca, Font Jelas) -->
                                <td class="py-4 px-5">
                                    <p class="text-sm font-medium text-slate-900 leading-relaxed whitespace-pre-wrap">
                                        {{ $chat->pertanyaan }}
                                    </p>
                                </td>

                                <!-- Jawaban Asisten AI (Dengan Toggle Baca Selengkapnya yang Bersih) -->
                                <td class="py-4 px-5" x-data="{ expanded: false }">
                                    @php $isLong = mb_strlen($chat->jawaban) > 180; @endphp
                                    <div class="text-sm text-slate-700 leading-relaxed whitespace-pre-wrap"
                                         :class="expanded ? '' : '{{ $isLong ? 'line-clamp-3' : '' }}'">
                                        {{ $chat->jawaban }}
                                    </div>

                                    @if($isLong)
                                        <button type="button" 
                                                @click="expanded = !expanded" 
                                                class="text-xs font-semibold text-[#008546] hover:text-[#00703c] hover:underline mt-1.5 focus:outline-none">
                                            <span x-text="expanded ? 'Tampilkan lebih sedikit' : 'Lihat selengkapnya'"></span>
                                        </button>
                                    @endif
                                </td>

                                <!-- Rujukan Sumber -->
                                <td class="py-4 px-4">
                                    @php $sources = $chat->sources ?? []; @endphp
                                    @if(!empty($sources) && is_array($sources))
                                        <div class="space-y-1">
                                            @foreach($sources as $s)
                                                @php $title = trim(($s['kb_nomor'] ?? '').' '.($s['judul'] ?? '')); @endphp
                                                @if($title)
                                                    <div class="text-xs text-slate-600 truncate max-w-[200px]" title="{{ $title }}">
                                                        • {{ $title }}
                                                    </div>
                                                @endif
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400">—</span>
                                    @endif
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 bg-slate-50/40">
                                    <p class="text-sm font-medium text-slate-700">Belum ada riwayat aktivitas tanya jawab.</p>
                                    <p class="text-xs text-slate-400 mt-1">Pertanyaan yang diajukan siswa ke chatbot akan otomatis dicatat di sini.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($chats->hasPages())
                <div class="p-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        Menampilkan {{ $chats->firstItem() }} – {{ $chats->lastItem() }} dari {{ $chats->total() }} pertanyaan
                    </div>
                    <div>
                        {{ $chats->links() }}
                    </div>
                </div>
            @endif
        </div>

    </div>
</x-premium-layout>
