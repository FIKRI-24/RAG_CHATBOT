@php
    /** @var \Illuminate\Database\Eloquent\Collection<\App\Models\Module> $modules */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<\App\Models\User> $students */
@endphp
<x-premium-layout>
    <div class="space-y-6 max-w-7xl mx-auto pb-12">

        <!-- Top Header & Action Banner -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-[#008546] border border-emerald-200/60 flex items-center justify-center text-xl shrink-0">
                    <i class="fa-solid fa-square-poll-vertical"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        Rekapitulasi Nilai Kuis Objektif Siswa
                    </h1>
                    <p class="text-xs text-slate-500 mt-0.5">Pantau hasil penilaian pilihan ganda siswa untuk seluruh Kegiatan Belajar (KB) secara terpadu.</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('guru.quiz-recap.export', request()->query()) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white rounded-xl text-xs font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                    <i class="fa-solid fa-file-excel"></i>
                    <span>Ekspor Rekap Excel (.xlsx)</span>
                </a>
            </div>
        </div>

        <!-- 4 KPI Summary Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Total Siswa</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $metrics['total_students'] }} <span class="text-xs font-medium text-slate-400">orang</span></p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-purple-50 text-purple-700 border border-purple-100 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-book-bookmark"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Kuis KB Aktif</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $metrics['total_quizzes'] }} <span class="text-xs font-medium text-slate-400">modul</span></p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-700 border border-blue-100 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Partisipasi Siswa</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ $metrics['participating_students'] }} <span class="text-xs font-medium text-slate-400">siswa</span></p>
                </div>
            </div>

            <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-[#008546] border border-emerald-100 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-award"></i>
                </div>
                <div>
                    <p class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Rata-Rata Kelas</p>
                    <p class="text-xl font-extrabold text-[#008546] mt-0.5">{{ $metrics['average_score'] }} <span class="text-xs font-medium text-slate-400">/ 100</span></p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-xs">
            <form method="GET" action="{{ route('guru.quiz-recap.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex-1 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-slate-400">
                            <i class="fa-solid fa-magnifying-glass text-xs"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama siswa, NISN, atau email..."
                               class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 pl-9 pr-3 outline-none focus:bg-white focus:border-[#008546] transition-all">
                    </div>

                    @if($classList->isNotEmpty())
                        <div class="sm:w-48">
                            <select name="class_name" onchange="this.form.submit()"
                                    class="w-full bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-xl py-2.5 px-3 outline-none focus:bg-white focus:border-[#008546] transition-all">
                                <option value="">Semua Kelas</option>
                                @foreach($classList as $c)
                                    <option value="{{ $c }}" @selected(request('class_name') === $c)>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-semibold transition-all">
                        Terapkan
                    </button>
                    @if(request()->hasAny(['search', 'class_name']))
                        <a href="{{ route('guru.quiz-recap.index') }}" class="px-3 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors" title="Reset Filter">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Matrix Table Card -->
        <div class="bg-white rounded-3xl border-2 border-slate-100 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#008546]"></span>
                    <h3 class="font-bold text-sm text-slate-800">Tabel Nilai Siswa per Kegiatan Belajar</h3>
                    <span class="text-xs text-slate-400">({{ $students->total() }} siswa ditemukan)</span>
                </div>
                <div class="flex items-center gap-3 text-[11px] text-slate-500 font-medium">
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-emerald-500"></span> $\ge$ 75 (Tuntas)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-amber-500"></span> &lt; 75 (Perlu Remedial)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-2 h-2 rounded-full bg-slate-300"></span> Belum Mengerjakan</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-xs text-left text-slate-700">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase text-[10px] tracking-wider border-b border-slate-200/80">
                        <tr>
                            <th class="py-3.5 px-4 font-bold text-center w-12">No</th>
                            <th class="py-3.5 px-4 font-bold">Identitas Siswa</th>
                            <th class="py-3.5 px-4 font-bold text-center">Kelas</th>
                            @foreach($modules as $m)
                                <th class="py-3.5 px-4 font-bold text-center" title="{{ $m->judul }}">
                                    <span class="text-slate-900 font-extrabold">{{ $m->kb_nomor ?? 'KB' }}</span>
                                    <span class="block text-[9px] font-normal text-slate-400 truncate max-w-[110px] mx-auto">{{ Str::limit($m->judul, 16) }}</span>
                                </th>
                            @endforeach
                            <th class="py-3.5 px-4 font-bold text-center bg-emerald-50/40 text-[#008546]">Rata-Rata</th>
                            <th class="py-3.5 px-4 font-bold text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $index => $student)
                            @php
                                $attemptsByModule = $student->quizAttempts->keyBy('module_id');
                                $totalScore = 0;
                                $completedCount = 0;
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3.5 px-4 text-center font-medium text-slate-400">
                                    {{ $students->firstItem() + $index }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $student->name }}</div>
                                    <div class="text-[10px] text-slate-400 flex items-center gap-1.5 mt-0.5">
                                        @if($student->student_number)
                                            <span class="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-slate-600">NIS: {{ $student->student_number }}</span>
                                        @endif
                                        <span>{{ $student->email }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 text-center">
                                    <span class="inline-block bg-slate-100 text-slate-600 text-[10px] font-semibold px-2 py-0.5 rounded-md">
                                        {{ $student->class_name ?: '-' }}
                                    </span>
                                </td>

                                @foreach($modules as $m)
                                    @php
                                        $att = $attemptsByModule->get($m->id);
                                    @endphp
                                    <td class="py-3.5 px-4 text-center">
                                        @if($att)
                                            @php
                                                $totalScore += $att->score;
                                                $completedCount++;
                                                $isPassed = $att->score >= 75;
                                            @endphp
                                            <a href="{{ route('guru.quiz-recap.show-attempt', $att) }}"
                                               class="inline-flex flex-col items-center justify-center px-2.5 py-1 rounded-xl text-xs font-bold transition-all hover:scale-105 {{ $isPassed ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-200 hover:bg-amber-100' }}"
                                               title="Klik untuk melihat rincian jawaban (Benar {{ $att->correct_count }}/{{ $att->question_count }})">
                                                <span>{{ $att->score }}</span>
                                                <span class="text-[9px] font-normal opacity-80">{{ $att->correct_count }}/{{ $att->question_count }}</span>
                                            </a>
                                        @else
                                            <span class="text-slate-300 font-bold" title="Belum mengerjakan kuis ini">—</span>
                                        @endif
                                    </td>
                                @endforeach

                                @php
                                    $studentAvg = $completedCount > 0 ? round($totalScore / $completedCount, 1) : null;
                                @endphp
                                <td class="py-3.5 px-4 text-center font-extrabold bg-emerald-50/20">
                                    @if($studentAvg !== null)
                                        <span class="text-sm {{ $studentAvg >= 75 ? 'text-[#008546]' : 'text-amber-600' }}">
                                            {{ $studentAvg }}
                                        </span>
                                    @else
                                        <span class="text-slate-300">—</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 text-center">
                                    @if($completedCount === 0)
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-400">
                                            Belum Ada
                                        </span>
                                    @elseif($completedCount === $modules->count() && $studentAvg >= 75)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Tuntas Penuh
                                        </span>
                                    @elseif($studentAvg >= 75)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800">
                                            <i class="fa-solid fa-circle-half-stroke text-[9px]"></i> Tuntas ({{ $completedCount }}/{{ $modules->count() }})
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800">
                                            <i class="fa-solid fa-clock-rotate-left text-[9px]"></i> Remedial
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 5 + $modules->count() }}" class="py-12 text-center text-slate-400">
                                    <i class="fa-solid fa-users-slash text-3xl mb-2 text-slate-300 block"></i>
                                    Tidak ada data siswa yang cocok dengan filter pencarian.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($students->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

    </div>
</x-premium-layout>
