@php
    /** @var \App\Models\Module $module */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\ModuleQuizAttempt> $attempts */
@endphp
<x-premium-layout>
    <div class="max-w-5xl mx-auto space-y-6">
        <a href="{{ route('guru.modules.quiz.edit', $module) }}" class="text-emerald-700 font-semibold text-sm">← Kelola Kuis</a>
        <h1 class="font-bold text-2xl">Hasil Kuis Objektif</h1>
        <p class="text-sm text-slate-500">{{ $module->kb_nomor }} · {{ $module->judul }}. Hasil versi lama tetap ditampilkan.</p>
        <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-100 text-left"><tr><th class="p-4">Siswa</th><th class="p-4">Kuis / Versi</th><th class="p-4">Benar</th><th class="p-4">Nilai</th><th class="p-4">Waktu</th></tr></thead>
                <tbody>
                    @forelse($attempts as $attempt)
                        <tr class="border-t border-slate-100">
                            <td class="p-4">{{ $attempt->user?->name ?? 'Akun dihapus' }}</td>
                            <td class="p-4">{{ $attempt->quiz_title }} · v{{ $attempt->quiz_version }}</td>
                            <td class="p-4">{{ $attempt->correct_count }}/{{ $attempt->question_count }}</td>
                            <td class="p-4 font-bold text-emerald-700">{{ $attempt->score }}/100</td>
                            <td class="p-4">{{ $attempt->created_at->timezone(config('app.display_timezone'))->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="p-8 text-center text-slate-500">Belum ada siswa yang mengerjakan kuis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $attempts->links() }}
    </div>
</x-premium-layout>
