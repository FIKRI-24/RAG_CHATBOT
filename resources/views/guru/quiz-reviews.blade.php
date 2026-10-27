@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\ChatHistory> $reviews */
@endphp
<x-premium-layout>
    <div class="space-y-6 max-w-6xl mx-auto pb-12">
        <div class="bg-white p-6 rounded-xl border border-slate-200/90 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Tinjauan nilai kuis</h1>
                <p class="text-xs text-slate-500 mt-1">Nilai AI bersifat sementara. Guru dapat meninjau soal, rubrik, jawaban, dan sumber sebelum menetapkan nilai.</p>
            </div>
            <form method="GET" class="flex items-center gap-2.5">
                <label class="text-xs font-semibold text-slate-600 flex items-center gap-2">
                    <span>Status</span>
                    <select name="status" class="bg-slate-50 border border-slate-200 text-slate-800 text-xs rounded-lg py-2 px-3 outline-none focus:bg-white focus:border-[#008546]">
                        <option value="pending" @selected(request('status', 'pending') === 'pending')>Belum ditinjau</option>
                        <option value="reviewed" @selected(request('status') === 'reviewed')>Sudah ditinjau</option>
                        <option value="all" @selected(request('status') === 'all')>Semua</option>
                    </select>
                </label>
                <button class="px-4 py-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold rounded-lg transition-colors">Tampilkan</button>
            </form>
        </div>

        @if(session('success'))
            <p role="status" class="p-3.5 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold">{{ session('success') }}</p>
        @endif
        @if($errors->any())
            <p role="alert" class="p-3.5 rounded-lg bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold">{{ $errors->first() }}</p>
        @endif

        <div class="space-y-4">
        @forelse($reviews as $review)
            <article class="bg-white rounded-xl border border-slate-200/90 p-5 sm:p-6 space-y-4">
                <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-100">
                    <h2 class="font-bold text-sm text-slate-900">{{ $review->siswa?->name }} · <span class="font-mono text-xs font-normal text-slate-500">{{ $review->siswa?->class_name ?: 'Kelas belum diisi' }}</span></h2>
                    @if($review->reviewed_at)
                        <p class="text-xs font-mono text-[#008546] bg-emerald-50 px-2.5 py-0.5 rounded border border-emerald-200">Ditinjau {{ $review->reviewer?->name }} · {{ $review->reviewed_at->timezone(config('app.display_timezone'))->format('d M Y H:i') }}</p>
                    @endif
                </div>

                <div class="space-y-2 text-xs sm:text-sm">
                    <p class="text-slate-700"><strong class="text-slate-900">Soal:</strong> {{ $review->quiz?->jawaban ?: 'Soal lama tidak tersambung' }}</p>
                    <p class="whitespace-pre-wrap p-3.5 bg-slate-50 rounded-lg border border-slate-200/80 text-slate-800"><strong class="text-slate-900">Jawaban siswa:</strong> {{ $review->pertanyaan }}</p>
                </div>

                <details class="text-xs text-slate-600 bg-slate-50/70 p-3.5 rounded-lg border border-slate-200/70">
                    <summary class="font-semibold text-slate-800 cursor-pointer">Kunci, rubrik, dan sumber</summary>
                    <div class="mt-2.5 space-y-2 pt-2 border-t border-slate-200/70">
                        <p class="whitespace-pre-wrap">{{ $review->quiz?->quiz_payload['answer_key'] ?? 'Belum tersedia' }}</p>
                        <ul class="list-disc pl-5 space-y-1">@foreach($review->quiz?->quiz_payload['rubric'] ?? [] as $criterion)<li>{{ $criterion }}</li>@endforeach</ul>
                        @foreach($review->sources ?? [] as $source)<blockquote class="whitespace-pre-wrap border-l-2 border-[#008546] pl-3 my-2 text-slate-600">{{ $source['judul'] }}: {{ $source['text'] }}</blockquote>@endforeach
                    </div>
                </details>

                <p class="text-xs sm:text-sm text-slate-700 font-medium">Saran AI: <span class="font-mono font-bold text-slate-900">{{ $review->assessment['score'] ?? '—' }}/100</span> · {{ $review->assessment['feedback'] ?? $review->jawaban }}</p>

                <form method="POST" action="{{ route('guru.quiz-reviews.update', $review) }}" class="pt-3 border-t border-slate-100 flex flex-wrap gap-3 items-end">@csrf @method('PATCH')
                    <label class="text-xs font-semibold text-slate-700">Nilai guru
                        <input type="number" name="score" min="0" max="100" required value="{{ $review->score }}" class="mt-1 block w-24 rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs font-mono focus:bg-white focus:border-[#008546] outline-none">
                    </label>
                    <label class="flex-1 text-xs font-semibold text-slate-700">Catatan
                        <textarea name="review_note" required maxlength="2000" rows="2" class="mt-1 block w-full rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-xs focus:bg-white focus:border-[#008546] outline-none">{{ $review->review_note }}</textarea>
                    </label>
                    <button class="px-4 py-2.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold rounded-lg transition-colors">Simpan penilaian</button>
                </form>
            </article>
        @empty
            <div class="bg-white rounded-xl border border-slate-200 p-10 text-center text-xs text-slate-500">
                <p>Belum ada jawaban kuis untuk ditinjau.</p>
            </div>
        @endforelse
        </div>
        {{ $reviews->links() }}
    </div>
</x-premium-layout>
