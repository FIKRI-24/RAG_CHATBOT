@php
    /** @var array<string, mixed> $status */
    /** @var \Illuminate\Pagination\LengthAwarePaginator<int, \App\Models\AuditEvent> $events */
@endphp
<x-premium-layout>
    <h1 class="text-2xl font-bold">Status sistem lokal</h1>
    <p class="text-sm text-slate-600">Diperiksa {{ $status['checked_at'] }}. Status worker berdasarkan aktivitas yang teramati, bukan jaminan proses sedang berjalan.</p>
    <dl class="grid sm:grid-cols-2 gap-4 my-5">
        <div class="border bg-white rounded-xl p-4"><dt>Worker RAG</dt><dd>{{ $status['worker_observed'] ? 'Aktivitas teramati' : 'Belum teramati; jalankan worker RAG' }}</dd></div>
        <div class="border bg-white rounded-xl p-4"><dt>Antrean RAG / pekerjaan gagal</dt><dd>{{ $status['pending_jobs'] }} / {{ $status['failed_jobs'] }} · umur antrean tertua {{ $status['oldest_job_age_seconds'] }} detik</dd></div>
        <div class="border bg-white rounded-xl p-4"><dt>Konfigurasi AI / permintaan hari ini</dt><dd>{{ $status['ai_configured'] ? 'Kunci tersedia; koneksi belum diuji' : 'Belum dikonfigurasi' }} / {{ $status['api_attempts_today'] }}</dd></div>
        <div class="border bg-white rounded-xl p-4"><dt>Modul gagal / pendaftaran mandiri</dt><dd>{{ $status['failed_modules'] }} / {{ $status['registration_enabled'] ? 'Terbuka di lokal' : 'Ditutup' }}</dd></div>
    </dl>
    <h2 class="font-bold">Jejak audit</h2>
    <div class="overflow-x-auto"><table class="w-full text-sm"><thead><tr><th>Waktu</th><th>Pelaku</th><th>Tindakan</th><th>Objek</th></tr></thead><tbody>
        @forelse($events as $event)<tr class="border-b"><td>{{ $event->created_at->timezone(config('app.display_timezone'))->format('d M Y H:i') }}</td><td>{{ $event->actor_id ?? 'Sistem' }}</td><td>{{ $event->action }}</td><td>{{ $event->subject_type }} #{{ $event->subject_id }}</td></tr>@empty<tr><td colspan="4">Belum ada peristiwa audit.</td></tr>@endforelse
    </tbody></table></div>{{ $events->links() }}
</x-premium-layout>
