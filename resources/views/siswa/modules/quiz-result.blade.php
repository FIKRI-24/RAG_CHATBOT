<x-student-quiz-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-8 space-y-6">
        <a href="{{ route('siswa.modules.index') }}" class="text-emerald-700 font-semibold">← Katalog Modul</a>
        @if(session('info'))
            <div role="status" class="p-4 bg-blue-50 text-blue-800 rounded-2xl border border-blue-200 text-sm flex items-center gap-2">
                <i class="fa-solid fa-circle-info text-base"></i>
                <span>{{ session('info') }}</span>
            </div>
        @endif
        @if(session('warning'))
            <div role="alert" class="p-4 bg-amber-50 text-amber-800 rounded-2xl border border-amber-200 text-sm flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-base"></i>
                <span>{{ session('warning') }}</span>
            </div>
        @endif
        <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-2">
            <h1 class="text-2xl font-bold">Hasil Kuis: {{ $attempt->quiz_title }}</h1>
            <p class="text-slate-500">{{ $attempt->module_title }} · versi {{ $attempt->quiz_version }}</p>
            <p class="text-3xl font-bold text-emerald-700">Nilai: {{ $attempt->score }}/100</p>
            <p>Benar {{ $attempt->correct_count }} dari {{ $attempt->question_count }} soal.</p>
        </div>
        @foreach($attempt->questions_snapshot as $index => $question)
            @php $answer = $attempt->answers[$index]; $correct = $answer === $question['correct_answer']; @endphp
            <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-3">
                <h2 class="font-bold">Soal {{ $index + 1 }} · <span class="{{ $correct ? 'text-emerald-700' : 'text-rose-700' }}">{{ $correct ? 'Benar' : 'Salah' }}</span></h2>
                <p class="whitespace-pre-line">{{ $question['text'] }}</p>
                <p>Jawaban Anda: <strong>{{ $answer }}.</strong> {{ $question['options'][$answer] }}</p>
                <p class="text-emerald-800">Jawaban benar: <strong>{{ $question['correct_answer'] }}.</strong> {{ $question['options'][$question['correct_answer']] }}</p>
            </div>
        @endforeach
        @if($attempt->module_id)
            <div>
                <a href="{{ route('siswa.modules.show', $attempt->module_id) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#008546] hover:bg-emerald-800 text-white font-semibold text-xs rounded-xl shadow-sm transition-all">
                    <i class="fa-solid fa-arrow-left"></i> Kembali ke Modul Pembelajaran
                </a>
            </div>
        @endif
    </div>
</x-student-quiz-layout>
