<x-student-quiz-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-8 space-y-6">
        <a href="{{ route('siswa.modules.index') }}" class="text-emerald-700 font-semibold">← Katalog Modul</a>
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
            <a href="{{ route('siswa.modules.quiz.show', $attempt->module_id) }}" class="inline-block text-emerald-700 font-semibold">Kembali ke Latihan →</a>
        @endif
    </div>
</x-student-quiz-layout>
