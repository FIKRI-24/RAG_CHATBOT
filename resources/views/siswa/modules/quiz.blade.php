@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<x-student-quiz-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-8 space-y-6">
        <a href="{{ route('siswa.modules.show', $module) }}" class="text-emerald-700 font-semibold">← Kembali ke Modul</a>
        <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-2">
            <h1 class="text-2xl font-bold">{{ $quiz->title }}</h1>
            <p class="text-slate-500">{{ $module->kb_nomor }} · {{ $module->judul }}</p>
            <p class="text-sm text-slate-500">Pilih satu jawaban per soal. Semua soal wajib dijawab. Nilai dihitung otomatis; latihan boleh diulang.</p>
        </div>
        @if($errors->any())
            <div role="alert" class="p-4 bg-rose-50 text-rose-800 rounded-2xl"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('siswa.modules.quiz.submit', $module) }}" class="space-y-5">
            @csrf
            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
            <input type="hidden" name="quiz_version" value="{{ $quiz->version }}">
            @foreach($questions as $index => $question)
                <fieldset class="bg-white p-6 rounded-3xl border border-slate-200 space-y-4">
                    <legend class="font-bold px-2">Soal {{ $index + 1 }}</legend>
                    <p class="whitespace-pre-line">{{ $question['text'] }}</p>
                    @foreach($question['options'] as $letter => $option)
                        <label class="flex items-start gap-3 rounded-xl border border-slate-200 p-4 cursor-pointer hover:bg-emerald-50">
                            <input type="radio" name="answers[{{ $index }}]" value="{{ $letter }}" required @checked(! $errors->has('quiz_version') && old('answers.'.$index) === $letter) class="mt-1 text-emerald-600">
                            <span><strong>{{ $letter }}.</strong> {{ $option }}</span>
                        </label>
                    @endforeach
                </fieldset>
            @endforeach
            <button type="submit" class="bg-[#008546] hover:bg-emerald-800 text-white px-6 py-3 rounded-xl font-semibold">Kirim Jawaban</button>
        </form>
        @if($attempts->isNotEmpty())
            <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-3">
                <h2 class="font-bold text-lg">Hasil Latihan Sebelumnya</h2>
                @foreach($attempts as $attempt)
                    <a href="{{ route('siswa.quiz-attempts.show', $attempt) }}" class="block text-emerald-700">{{ $attempt->quiz_title }} · v{{ $attempt->quiz_version }} · {{ $attempt->score }}/100 · {{ $attempt->created_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-student-quiz-layout>
