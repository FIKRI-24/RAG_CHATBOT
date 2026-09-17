@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \App\Models\Module $module */
    /** @var \App\Models\ModuleQuiz|null $quiz */
@endphp
<x-premium-layout>
    @php
        $blankQuestion = ['text' => '', 'options' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''], 'correct_answer' => 'A'];
        $initialQuestions = old('questions', $quiz?->questions ?? [$blankQuestion]);
        $initialQuestions = is_array($initialQuestions) ? $initialQuestions : [$blankQuestion];
    @endphp
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        <div class="bg-white rounded-3xl border border-slate-200 p-6 space-y-3">
            <a href="{{ route('guru.modules.index') }}" class="text-sm text-emerald-700 font-semibold">← Manajemen Modul</a>
            <h1 class="text-2xl font-bold">Kelola Kuis Objektif</h1>
            <p class="text-sm text-slate-500">{{ $module->kb_nomor }} · {{ $module->judul }}</p>
            <div class="flex flex-wrap gap-3 text-sm">
                <span class="rounded-full px-3 py-1 {{ $quiz?->is_published ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $quiz?->is_published ? 'Diterbitkan' : 'Draf / belum dibuat' }}</span>
                <a href="{{ route('guru.modules.quiz.results', $module) }}" class="font-semibold text-emerald-700 py-1">Lihat Hasil Siswa →</a>
            </div>
        </div>
        @if(session('success'))
            <div role="status" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-emerald-800">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div role="alert" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-rose-800">
                <p class="font-semibold">Periksa isian kuis:</p>
                <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('guru.modules.quiz.update', $module) }}" class="space-y-5"
              x-data="{ questions: {{ Illuminate\Support\Js::from(array_values($initialQuestions)) }}, addQuestion() { this.questions.push({ text: '', options: { A: '', B: '', C: '', D: '' }, correct_answer: 'A' }); } }">
            @csrf
            @method('PUT')
            <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-4">
                <label class="block font-semibold text-sm" for="quiz-title">Judul kuis</label>
                <input id="quiz-title" name="title" required maxlength="255" value="{{ old('title', $quiz?->title ?? 'Kuis '.$module->kb_nomor) }}" class="w-full rounded-xl border-slate-300">
                <p class="text-sm text-slate-500">Pilihan ganda A–D, satu jawaban benar per soal, maksimal 50 soal. Semua soal berbobot sama. Siswa dapat mengulang latihan; setiap hasil disimpan.</p>
                <label class="flex gap-3 items-center text-sm font-semibold">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $quiz?->is_published ?? false)) class="rounded border-slate-300 text-emerald-600">
                    Terbitkan kuis untuk siswa
                </label>
            </div>
            <template x-for="(question, index) in questions" :key="index">
                <fieldset class="bg-white p-6 rounded-3xl border border-slate-200 space-y-4">
                    <legend class="font-bold px-2" x-text="'Soal ' + (index + 1)"></legend>
                    <label class="block text-sm font-semibold" :for="'question-' + index">Pertanyaan</label>
                    <textarea :id="'question-' + index" :name="'questions[' + index + '][text]'" x-model="question.text" required maxlength="2000" rows="3" class="w-full rounded-xl border-slate-300"></textarea>
                    <template x-for="letter in ['A', 'B', 'C', 'D']" :key="letter">
                        <div class="flex items-center gap-3">
                            <label :for="'option-' + index + '-' + letter" class="font-bold w-5" x-text="letter"></label>
                            <input :id="'option-' + index + '-' + letter" :name="'questions[' + index + '][options][' + letter + ']'" x-model="question.options[letter]" required maxlength="500" class="flex-1 min-w-0 rounded-xl border-slate-300">
                        </div>
                    </template>
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <label class="text-sm font-semibold flex items-center gap-3">Jawaban benar
                            <select :name="'questions[' + index + '][correct_answer]'" x-model="question.correct_answer" class="rounded-xl border-slate-300">
                                <option>A</option><option>B</option><option>C</option><option>D</option>
                            </select>
                        </label>
                        <button type="button" @click="questions.splice(index, 1)" :disabled="questions.length <= 1" class="text-sm font-semibold text-rose-700 disabled:opacity-40">Hapus Soal</button>
                    </div>
                </fieldset>
            </template>
            <div class="flex flex-wrap gap-3">
                <button type="button" @click="addQuestion()" :disabled="questions.length >= 50" class="px-5 py-3 bg-white border border-emerald-600 rounded-xl font-semibold text-emerald-700 disabled:opacity-40">Tambah Soal</button>
                <button type="submit" class="px-5 py-3 bg-[#008546] hover:bg-emerald-800 text-white rounded-xl font-semibold">Simpan Kuis</button>
            </div>
            <noscript><p class="text-rose-700">Aktifkan JavaScript untuk mengelola soal kuis.</p></noscript>
        </form>
        @if($quiz)
            <form method="POST" action="{{ route('guru.modules.quiz.destroy', $module) }}" onsubmit="return confirm('Hapus kuis ini? Hasil pengerjaan sebelumnya tetap tersimpan.');">
                @csrf @method('DELETE')
                <button type="submit" class="text-sm font-semibold text-rose-700 border border-rose-200 rounded-xl px-5 py-3">Hapus Kuis</button>
            </form>
        @endif
    </div>
</x-premium-layout>
