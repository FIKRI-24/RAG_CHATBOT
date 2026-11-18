@php
    /** @var \App\Models\Module $module */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\ModuleQuizAttempt> $attempts */
@endphp
<x-premium-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ route('guru.modules.quiz.edit', $module) }}" class="text-emerald-700 font-semibold text-sm">← Kelola Kuis</a>
        <h1 class="font-bold text-2xl">Hasil Kuis Objektif</h1>
        <p class="text-sm text-slate-500">{{ $module->kb_nomor }} · {{ $module->judul }}. Hasil versi lama tetap ditampilkan.</p>
        @if(session('success'))
            <div role="status" class="bg-emerald-50 border border-emerald-200 rounded-2xl p-4 text-xs font-semibold text-emerald-800">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div role="alert" class="bg-rose-50 border border-rose-200 rounded-2xl p-4 text-xs font-semibold text-rose-800">{{ session('error') }}</div>
        @endif
        <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left"><tr><th class="p-4">Siswa</th><th class="p-4">Kuis / Versi</th><th class="p-4">Benar</th><th class="p-4">Nilai</th><th class="p-4">Waktu</th><th class="p-4">Aksi & Remedial</th></tr></thead>
                <tbody>
                    @forelse($attempts as $attempt)
                        <tr class="border-t border-slate-100">
                            <td class="p-4">
                                <span class="font-semibold text-slate-900">{{ $attempt->user?->name ?? 'Akun dihapus' }}</span>
                                @if($attempt->hasRemedialHistory())
                                    <span class="block text-[11px] font-semibold text-blue-700">Remedial (#{{ $attempt->attempt_number }})</span>
                                @endif
                            </td>
                            <td class="p-4">{{ $attempt->quiz_title }} · v{{ $attempt->quiz_version }}</td>
                            <td class="p-4">{{ $attempt->correct_count }}/{{ $attempt->question_count }}</td>
                            <td class="p-4 font-bold {{ $attempt->score >= 75 ? 'text-emerald-700' : 'text-amber-700' }}">
                                {{ $attempt->score }}/100
                                @if($attempt->hasApprovedRemedial())
                                    <span class="block text-[10px] font-semibold text-amber-700">Izin Ulang Aktif</span>
                                @endif
                            </td>
                            <td class="p-4">{{ ($attempt->adjusted_at ?? $attempt->updated_at ?? $attempt->created_at)->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }}</td>
                            <td class="p-4">
                                <div class="flex flex-wrap items-center gap-2">
                                    <a href="{{ route('guru.quiz-recap.show-attempt', $attempt) }}" class="text-xs font-semibold text-emerald-700 hover:underline">Detail & Kelola Remedial &rarr;</a>
                                    @if($attempt->score < 75 && ! $attempt->hasApprovedRemedial() && $module->quiz)
                                        <form method="POST" action="{{ route('guru.quiz-recap.approve-remedial', $attempt) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 rounded-lg bg-amber-50 hover:bg-amber-100 border border-amber-300 text-amber-800 text-[11px] font-bold transition-colors">
                                                Approve Remedial
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="p-8 text-center text-slate-500">Belum ada siswa yang mengerjakan kuis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $attempts->links() }}
    </div>
</x-premium-layout>
