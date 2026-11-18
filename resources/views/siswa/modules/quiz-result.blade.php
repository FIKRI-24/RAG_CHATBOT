<x-student-quiz-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-8 space-y-6">
        
        <!-- Breadcrumb Navigation -->
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <a href="{{ route('siswa.modules.index') }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#008546] hover:text-emerald-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Katalog Modul</span>
            </a>
            @if($attempt->module_id)
                <a href="{{ route('siswa.modules.show', $attempt->module_id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-600 hover:text-slate-900 transition-colors">
                    <span>Lihat Kembali Modul</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            @endif
        </div>

        @if(session('info'))
            <div role="status" class="p-4 bg-emerald-50 text-[#008546] rounded-2xl border border-emerald-200 text-xs sm:text-sm flex items-center gap-2.5">
                <i class="fa-solid fa-circle-info text-base shrink-0"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif

        @if(session('warning'))
            <div role="alert" class="p-4 bg-amber-50 text-amber-800 rounded-2xl border border-amber-200 text-xs sm:text-sm flex items-center gap-2.5">
                <i class="fa-solid fa-triangle-exclamation text-base shrink-0"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif

        @if($attempt->hasApprovedRemedial() && $attempt->module_id)
            <div role="status" class="p-4 bg-blue-50 text-blue-900 rounded-2xl border border-blue-200 text-xs sm:text-sm flex flex-wrap items-center justify-between gap-3">
                <div class="space-y-0.5">
                    <p class="font-bold">Izin Remedial Disetujui Guru</p>
                    <p class="text-xs text-blue-800">{{ $attempt->remedial_note ?: 'Anda dapat mengerjakan ulang kuis ini sekarang.' }}</p>
                </div>
                <a href="{{ route('siswa.modules.quiz.show', $attempt->module_id) }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition-colors">
                    Kerjakan Kuis Remedial &rarr;
                </a>
            </div>
        @endif

        <!-- Cyber-Network Glassmorphism Score Hero -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 shadow-sm">
            <!-- Subtle Cyber Grid Background -->
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-16 -right-16 w-60 h-60 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
                <div class="space-y-3">
                    <div class="flex items-center gap-2.5 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold backdrop-blur-md">
                            <i class="fa-solid fa-award text-emerald-400"></i>
                            <span>Hasil Evaluasi Kuis</span>
                        </span>
                        <span class="text-xs text-slate-400 font-medium">
                            Versi {{ $attempt->quiz_version }} &bull; Percobaan #{{ $attempt->attempt_number ?? 1 }}
                        </span>
                        @if($attempt->adjusted_at)
                            <span class="px-2.5 py-0.5 rounded-full bg-amber-500/20 text-amber-200 border border-amber-400/30 text-[11px] font-semibold">
                                Nilai Disesuaikan Guru
                            </span>
                        @endif
                    </div>

                    <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                        {{ $attempt->quiz_title }}
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl leading-relaxed">
                        {{ $attempt->module_title }}
                    </p>
                </div>

                <!-- Score Showcase Card -->
                <div class="bg-white/10 border border-white/15 backdrop-blur-md rounded-2xl p-5 text-center shrink-0 min-w-[200px] flex flex-col items-center justify-center shadow-lg">
                    <span class="text-[11px] font-semibold text-emerald-300 uppercase tracking-wider block mb-1">Nilai: {{ $attempt->score }}/100</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-4xl sm:text-5xl font-extrabold text-white tracking-tight">{{ $attempt->score }}</span>
                        <span class="text-sm font-semibold text-slate-400">/100</span>
                    </div>
                    <div class="mt-2.5 inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-xs font-bold text-emerald-200">
                        <i class="fa-solid fa-circle-check text-[10px]"></i>
                        <span>Benar {{ $attempt->correct_count }} dari {{ $attempt->question_count }} soal</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Question Reviews Breakdown -->
        <div class="space-y-4">
            <h2 class="text-sm font-bold text-slate-800 tracking-tight flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#008546]"></i>
                <span>Review Jawaban Soal</span>
            </h2>

            @foreach($attempt->questions_snapshot as $index => $question)
                @php 
                    $answer = $attempt->answers[$index] ?? null; 
                    $correct = $answer === $question['correct_answer']; 
                @endphp
                <div class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
                    
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 flex-wrap">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-slate-100 text-slate-700 font-bold text-xs border border-slate-200/80">
                            Soal {{ sprintf('%02d', $index + 1) }}
                        </span>

                        @if($correct)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-[#008546] border border-emerald-200 text-xs font-bold">
                                <i class="fa-solid fa-circle-check"></i>
                                <span>Jawaban Benar</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 border border-rose-200 text-xs font-bold">
                                <i class="fa-solid fa-circle-xmark"></i>
                                <span>Jawaban Kurang Tepat</span>
                            </span>
                        @endif
                    </div>

                    <p class="whitespace-pre-line text-sm sm:text-base font-medium text-slate-800 leading-relaxed">
                        {{ $question['text'] }}
                    </p>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                        <!-- User's Answer -->
                        <div class="p-3.5 rounded-xl border {{ $correct ? 'bg-emerald-50/50 border-emerald-200 text-emerald-950' : 'bg-rose-50/50 border-rose-200 text-rose-950' }}">
                            <span class="text-[10px] font-bold uppercase tracking-wider block mb-1 {{ $correct ? 'text-emerald-700' : 'text-rose-700' }}">
                                Jawaban Anda:
                            </span>
                            <p class="text-xs sm:text-sm font-semibold">
                                @if($answer && isset($question['options'][$answer]))
                                    <strong>{{ $answer }}.</strong> {{ $question['options'][$answer] }}
                                @else
                                    <span class="italic text-slate-400">Tidak dijawab</span>
                                @endif
                            </p>
                        </div>

                        <!-- Correct Answer -->
                        <div class="p-3.5 rounded-xl border bg-slate-50 border-slate-200 text-slate-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider block mb-1 text-slate-500">
                                Kunci Jawaban:
                            </span>
                            <p class="text-xs sm:text-sm font-semibold text-emerald-800">
                                <strong>{{ $question['correct_answer'] }}.</strong> {{ $question['options'][$question['correct_answer']] ?? '-' }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if($attempt->historyEntries())
            <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-xs space-y-3">
                <h2 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    <i class="fa-solid fa-clock-rotate-left text-[#008546]"></i>
                    <span>Riwayat Nilai Sebelumnya</span>
                </h2>
                <div class="space-y-2">
                    @foreach($attempt->historyEntries() as $entry)
                        <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                            <div>
                                <span class="font-bold text-slate-800">
                                    {{ ($entry['type'] ?? '') === 'manual_adjustment' ? 'Nilai Sebelum Penyesuaian Guru' : 'Percobaan #'.($entry['attempt_number'] ?? 1) }}:
                                    {{ $entry['score'] ?? '-' }}/100
                                </span>
                                @if(! empty($entry['note']))
                                    <p class="text-[11px] text-slate-500 mt-0.5">{{ $entry['note'] }}</p>
                                @endif
                            </div>
                            <span class="text-[11px] text-slate-400">
                                {{ ! empty($entry['recorded_at']) ? \Carbon\Carbon::parse($entry['recorded_at'])->timezone(config('app.display_timezone'))->translatedFormat('d M Y, H:i') : '-' }} WIB
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($attempt->module_id)
            <div class="pt-2">
                <a href="{{ route('siswa.modules.show', $attempt->module_id) }}" class="inline-flex items-center gap-2 px-6 py-3 bg-[#008546] hover:bg-emerald-800 text-white font-bold text-xs rounded-xl shadow-sm hover:shadow-md transition-all active:scale-95">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali ke Modul Pembelajaran</span>
                </a>
            </div>
        @endif
    </div>
</x-student-quiz-layout>
