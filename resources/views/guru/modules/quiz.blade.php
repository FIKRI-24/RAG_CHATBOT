@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \App\Models\Module $module */
    /** @var \App\Models\ModuleQuiz|null $quiz */
@endphp
<x-premium-layout>
    @php
        $blankQuestion = ['text' => '', 'options' => ['A' => '', 'B' => '', 'C' => '', 'D' => ''], 'correct_answer' => 'A'];
        $rawQuestions = old('questions', $quiz?->questions ?? [$blankQuestion]);
        $rawQuestions = is_array($rawQuestions) && count($rawQuestions) > 0 ? array_values($rawQuestions) : [$blankQuestion];
        $initialQuestions = array_map(function ($q, $i) {
            $q = is_array($q) ? $q : [];
            $opts = is_array($q['options'] ?? null) ? $q['options'] : [];

            return [
                '_uid' => 'q-'.$i.'-'.uniqid(),
                'text' => (string) ($q['text'] ?? ''),
                'options' => [
                    'A' => (string) ($opts['A'] ?? ''),
                    'B' => (string) ($opts['B'] ?? ''),
                    'C' => (string) ($opts['C'] ?? ''),
                    'D' => (string) ($opts['D'] ?? ''),
                ],
                'correct_answer' => in_array($q['correct_answer'] ?? 'A', ['A', 'B', 'C', 'D'], true) ? $q['correct_answer'] : 'A',
            ];
        }, $rawQuestions, array_keys($rawQuestions));
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
        @php
            $initialTitle = (string) old('title', $quiz?->title ?? 'Kuis '.$module->kb_nomor);
            $initialPublished = (bool) old('is_published', $quiz?->is_published ?? false);
            $draftKey = 'quiz_draft_'.((int) auth()->id()).'_'.((int) $module->id);
        @endphp
        <form method="POST" action="{{ route('guru.modules.quiz.update', $module) }}" class="space-y-5"
              x-data="{
                  submitting: false,
                  draftKey: {{ Illuminate\Support\Js::from($draftKey) }},
                  hadServerSuccess: {{ Illuminate\Support\Js::from((bool) session('success')) }},
                  hadValidationErrors: {{ Illuminate\Support\Js::from($errors->any()) }},
                  draftBanner: false,
                  pendingDraft: null,
                  draftSavedText: '',
                  title: {{ Illuminate\Support\Js::from($initialTitle) }},
                  isPublished: {{ Illuminate\Support\Js::from($initialPublished) }},
                  nextUid: {{ count($initialQuestions) }},
                  questions: {{ Illuminate\Support\Js::from($initialQuestions) }},
                  normalizeCompare(t, pub, qs) {
                      return JSON.stringify({
                          title: t || '',
                          isPublished: Boolean(pub),
                          questions: (qs || []).map(q => ({ text: q.text || '', options: q.options || {}, correct_answer: q.correct_answer || 'A' }))
                      });
                  },
                  init() {
                      if (this.hadServerSuccess) {
                          localStorage.removeItem(this.draftKey);
                          return;
                      }
                      const raw = !this.hadValidationErrors ? localStorage.getItem(this.draftKey) : null;
                      if (raw) {
                          try {
                              const parsed = JSON.parse(raw);
                              if (parsed && Array.isArray(parsed.questions) && parsed.questions.length > 0) {
                                  const currentSig = this.normalizeCompare(this.title, this.isPublished, this.questions);
                                  const draftSig = this.normalizeCompare(parsed.title, parsed.isPublished, parsed.questions);
                                  if (currentSig !== draftSig) {
                                      this.pendingDraft = parsed;
                                      this.draftBanner = true;
                                  }
                              }
                          } catch {}
                      }
                      this.$watch('title', () => this.saveDraft());
                      this.$watch('isPublished', () => this.saveDraft());
                      this.$watch('questions', () => this.saveDraft(), { deep: true });
                  },
                  saveDraft() {
                      try {
                          localStorage.setItem(this.draftKey, JSON.stringify({
                              title: this.title,
                              isPublished: this.isPublished,
                              questions: this.questions,
                              savedAt: new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' })
                          }));
                          this.draftSavedText = 'Draf lokal tersimpan otomatis';
                      } catch {}
                  },
                  restoreDraft() {
                      if (!this.pendingDraft) return;
                      this.title = this.pendingDraft.title ?? this.title;
                      this.isPublished = Boolean(this.pendingDraft.isPublished);
                      this.questions = this.pendingDraft.questions.map((q, i) => ({
                          _uid: 'q-restored-' + (++this.nextUid) + '-' + i,
                          text: q.text || '',
                          options: Object.assign({ A: '', B: '', C: '', D: '' }, q.options || {}),
                          correct_answer: ['A', 'B', 'C', 'D'].includes(q.correct_answer) ? q.correct_answer : 'A'
                      }));
                      this.draftBanner = false;
                      this.pendingDraft = null;
                      this.draftSavedText = 'Draf lokal dipulihkan';
                  },
                  discardDraft() {
                      localStorage.removeItem(this.draftKey);
                      this.draftBanner = false;
                      this.pendingDraft = null;
                  },
                  addQuestion() { this.questions.push({ _uid: 'q-new-' + (++this.nextUid), text: '', options: { A: '', B: '', C: '', D: '' }, correct_answer: 'A' }); },
                  moveQuestion(index, delta) {
                      const target = index + delta;
                      if (target < 0 || target >= this.questions.length) return;
                      const item = this.questions.splice(index, 1)[0];
                      this.questions.splice(target, 0, item);
                  },
                  isBlank(q) { return !(q.text || '').trim() && Object.values(q.options || {}).every(v => !(v || '').trim()); }
              }"
              @submit="if (submitting) { $event.preventDefault(); return; } submitting = true;">
            @csrf
            @method('PUT')
            <div x-show="draftBanner" x-cloak class="bg-amber-50 border border-amber-300 rounded-2xl p-4 flex flex-wrap items-center justify-between gap-3 text-sm text-amber-900">
                <div>
                    <strong class="font-bold">Draf kuis lokal ditemukan</strong>
                    <span class="block text-xs text-amber-800" x-text="'Perubahan terakhir pukul ' + (pendingDraft?.savedAt || '-') + ' belum disimpan ke server.'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="restoreDraft()" class="px-3 py-1.5 rounded-lg bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold">Pulihkan Draf</button>
                    <button type="button" @click="discardDraft()" class="px-3 py-1.5 rounded-lg bg-white border border-amber-300 text-amber-800 text-xs font-semibold">Abaikan</button>
                </div>
            </div>
            <div class="bg-white p-6 rounded-3xl border border-slate-200 space-y-4">
                <div class="flex items-center justify-between gap-2">
                    <label class="block font-semibold text-sm" for="quiz-title">Judul kuis</label>
                    <span x-show="draftSavedText" x-text="draftSavedText" class="text-[11px] font-medium text-emerald-700"></span>
                </div>
                <input id="quiz-title" name="title" x-model="title" required maxlength="255" value="{{ $initialTitle }}" class="w-full rounded-xl border-slate-300">
                <p class="text-sm text-slate-500">Pilihan ganda A–D, satu jawaban benar per soal, maksimal 50 soal. Semua soal berbobot sama. Secara default siswa mengerjakan 1 kali; guru dapat menyetujui remedial dari menu Rekap Nilai Kuis.</p>
                <label class="flex gap-3 items-center text-sm font-semibold">
                    <input type="hidden" name="is_published" value="0">
                    <input type="checkbox" name="is_published" value="1" x-model="isPublished" @checked($initialPublished) class="rounded border-slate-300 text-emerald-600">
                    Terbitkan kuis untuk siswa
                </label>
            </div>
            <template x-for="(question, index) in questions" :key="question._uid">
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
                        <div class="flex items-center gap-3">
                            <button type="button" @click="moveQuestion(index, -1)" :disabled="index === 0" class="text-xs font-semibold text-slate-600 hover:text-slate-900 disabled:opacity-35" title="Pindah ke atas">&uarr; Naik</button>
                            <button type="button" @click="moveQuestion(index, 1)" :disabled="index >= questions.length - 1" class="text-xs font-semibold text-slate-600 hover:text-slate-900 disabled:opacity-35" title="Pindah ke bawah">&darr; Turun</button>
                            <button type="button" @click="if (isBlank(question) || confirm('Hapus soal nomor ' + (index + 1) + '?')) questions.splice(index, 1)" :disabled="questions.length <= 1" class="text-sm font-semibold text-rose-700 disabled:opacity-40">Hapus Soal</button>
                        </div>
                    </div>
                </fieldset>
            </template>
            <div class="flex flex-wrap gap-3">
                <button type="button" @click="addQuestion()" :disabled="questions.length >= 50" class="px-5 py-3 bg-white border border-emerald-600 rounded-xl font-semibold text-emerald-700 disabled:opacity-40">Tambah Soal</button>
                <button type="submit" :disabled="submitting" class="px-5 py-3 bg-[#008546] hover:bg-emerald-800 disabled:opacity-60 disabled:cursor-wait text-white rounded-xl font-semibold">Simpan Kuis</button>
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
