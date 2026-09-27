<x-premium-layout>
    <div class="space-y-6 max-w-4xl mx-auto pb-12">
        <div class="flex items-center justify-between">
            <a href="{{ route('guru.quiz-recap.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Rekap Nilai Kuis</span>
            </a>
            <span class="text-xs text-slate-400">
                Pengerjaan: {{ $attempt->created_at->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB
            </span>
        </div>

        <!-- Student & Score Header Card -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                        {{ $attempt->module?->kb_nomor ?? 'KB' }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">Versi Kuis: v{{ $attempt->quiz_version }}</span>
                </div>
                <h1 class="text-xl font-bold text-slate-900">{{ $attempt->quiz_title }}</h1>
                <p class="text-xs text-slate-500">Materi: {{ $attempt->module_title }}</p>

                <div class="pt-2 flex items-center gap-3 text-xs text-slate-600">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-graduate text-[#008546]"></i>
                        <span>{{ $attempt->user?->name ?? 'Akun Siswa Dihapus' }}</span>
                    </div>
                    @if($attempt->user?->class_name)
                        <span>&bull; Kelas: {{ $attempt->user->class_name }}</span>
                    @endif
                    @if($attempt->user?->student_number)
                        <span>&bull; NIS: {{ $attempt->user->student_number }}</span>
                    @endif
                </div>
            </div>

            <div class="sm:text-right bg-slate-50 p-4 rounded-2xl border border-slate-200/80 shrink-0">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Nilai Akhir Siswa</p>
                <p class="text-3xl font-extrabold {{ $attempt->score >= 75 ? 'text-[#008546]' : 'text-amber-600' }} mt-0.5">
                    {{ $attempt->score }} <span class="text-xs font-medium text-slate-400">/ 100</span>
                </p>
                <p class="text-[11px] font-medium text-slate-500 mt-1">
                    Benar {{ $attempt->correct_count }} dari {{ $attempt->question_count }} Soal
                </p>
            </div>
        </div>

        <!-- Question By Question Review -->
        <div class="space-y-4">
            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#008546]"></i>
                <span>Lembar Jawaban Siswa</span>
            </h3>

            @foreach($attempt->questions_snapshot as $index => $question)
                @php
                    $userAnswer = $attempt->answers[$index] ?? null;
                    $isCorrect = $userAnswer === $question['correct_answer'];
                @endphp
                <div class="bg-white p-6 rounded-3xl border-2 {{ $isCorrect ? 'border-emerald-100' : 'border-rose-100' }} shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="font-bold text-xs text-slate-700">Soal Nomor {{ $index + 1 }}</span>
                        @if($isCorrect)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Benar (+{{ round(100 / $attempt->question_count, 1) }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <i class="fa-solid fa-xmark text-[10px]"></i> Salah (0)
                            </span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm font-medium text-slate-800 whitespace-pre-line leading-relaxed">
                        {{ $question['text'] }}
                    </p>

                    <div class="grid grid-cols-1 gap-2 pt-1">
                        @foreach($question['options'] as $letter => $optionText)
                            @php
                                $isChosen = $userAnswer === $letter;
                                $isKey = $question['correct_answer'] === $letter;
                            @endphp
                            <div class="flex items-start gap-3 p-3 rounded-xl border text-xs {{ $isKey ? 'bg-emerald-50/70 border-emerald-300 text-emerald-950 font-semibold' : ($isChosen ? 'bg-rose-50/70 border-rose-300 text-rose-950 font-semibold' : 'bg-slate-50/50 border-slate-200 text-slate-600') }}">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 {{ $isKey ? 'bg-emerald-600 text-white' : ($isChosen ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $letter }}
                                </span>
                                <div class="flex-1 pt-0.5">
                                    <span>{{ $optionText }}</span>
                                    @if($isKey)
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-0.5">✓ Kunci Jawaban Benar</span>
                                    @endif
                                    @if($isChosen && ! $isKey)
                                        <span class="block text-[10px] text-rose-600 font-bold mt-0.5">✗ Pilihan Jawaban Siswa</span>
                                    @elseif($isChosen && $isKey)
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-0.5">★ Dipilih Siswa (Tepat)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-premium-layout>
