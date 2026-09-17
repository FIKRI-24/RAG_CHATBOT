@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\ChatHistory> $reviews */
@endphp
<x-premium-layout>
    <h1 class="text-2xl font-bold">Tinjauan nilai kuis</h1>
    <p class="text-sm text-slate-600">Nilai AI bersifat sementara. Guru dapat meninjau soal, rubrik, jawaban, dan sumber sebelum menetapkan nilai.</p>
    @if(session('success')) <p role="status">{{ session('success') }}</p> @endif
    @if($errors->any()) <p role="alert">{{ $errors->first() }}</p> @endif
    <form method="GET" class="my-4"><label>Status <select name="status"><option value="pending" @selected(request('status', 'pending') === 'pending')>Belum ditinjau</option><option value="reviewed" @selected(request('status') === 'reviewed')>Sudah ditinjau</option><option value="all" @selected(request('status') === 'all')>Semua</option></select></label><button class="px-4 py-2 bg-emerald-700 text-white rounded-lg">Tampilkan</button></form>
    <div class="space-y-4">
    @forelse($reviews as $review)
        <article class="bg-white rounded-xl border p-5 space-y-3">
            <h2 class="font-bold">{{ $review->siswa?->name }} · {{ $review->siswa?->class_name ?: 'Kelas belum diisi' }}</h2>
            <p>Soal: {{ $review->quiz?->jawaban ?: 'Soal lama tidak tersambung' }}</p>
            <p class="whitespace-pre-wrap">Jawaban siswa: {{ $review->pertanyaan }}</p>
            <details><summary>Kunci, rubrik, dan sumber</summary>
                <p class="whitespace-pre-wrap">{{ $review->quiz?->quiz_payload['answer_key'] ?? 'Belum tersedia' }}</p>
                <ul class="list-disc pl-5">@foreach($review->quiz?->quiz_payload['rubric'] ?? [] as $criterion)<li>{{ $criterion }}</li>@endforeach</ul>
                @foreach($review->sources ?? [] as $source)<blockquote class="whitespace-pre-wrap border-l-4 pl-3 my-2">{{ $source['judul'] }}: {{ $source['text'] }}</blockquote>@endforeach
            </details>
            <p>Saran AI: {{ $review->assessment['score'] ?? '—' }}/100 · {{ $review->assessment['feedback'] ?? $review->jawaban }}</p>
            @if($review->reviewed_at)<p>Ditinjau {{ $review->reviewer?->name }} · {{ $review->reviewed_at->timezone(config('app.display_timezone'))->format('d M Y H:i') }}</p>@endif
            <form method="POST" action="{{ route('guru.quiz-reviews.update', $review) }}" class="flex flex-wrap gap-3 items-end">@csrf @method('PATCH')
                <label>Nilai guru <input type="number" name="score" min="0" max="100" required value="{{ $review->score }}" class="block w-24 rounded-lg"></label>
                <label class="flex-1">Catatan <textarea name="review_note" required maxlength="2000" class="block w-full rounded-lg">{{ $review->review_note }}</textarea></label>
                <button class="px-4 py-2 bg-emerald-700 text-white rounded-lg">Simpan penilaian</button>
            </form>
        </article>
    @empty <p>Belum ada jawaban kuis untuk ditinjau.</p> @endforelse
    </div>
    {{ $reviews->links() }}
</x-premium-layout>
