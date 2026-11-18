<x-premium-layout>
    <div class="space-y-6 max-w-4xl mx-auto pb-12">
        <div class="flex items-center justify-between">
            <a href="{{ route('guru.quiz-recap.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-slate-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke Rekap Nilai Kuis</span>
            </a>
            <span class="text-xs text-slate-400">
                Pengerjaan: {{ $attempt->created_at->timezone(config('app.display_timezone'))->translatedFormat('d F Y, H:i') }} WIB
            </span>
        </div>

        @if(session('success'))
            <div role="status" class="p-4 bg-emerald-50 border border-emerald-200 rounded-2xl text-xs font-semibold text-emerald-800">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div role="alert" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800">
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any())
            <div role="alert" class="p-4 bg-rose-50 border border-rose-200 rounded-2xl text-xs font-semibold text-rose-800">
                <ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <!-- Student & Score Header Card -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="space-y-1.5">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                        {{ $attempt->module?->kb_nomor ?? 'KB' }}
                    </span>
                    <span class="text-xs font-medium text-slate-400">Versi Kuis: v{{ $attempt->quiz_version }}</span>
                    <span class="px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-100 text-slate-700">
                        Percobaan #{{ $attempt->attempt_number ?? 1 }}
                    </span>
                    @if($attempt->hasApprovedRemedial())
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200">
                            Izin Ujian Ulang Aktif
                        </span>
                    @endif
                    @if($attempt->adjusted_at)
                        <span class="px-2.5 py-0.5 rounded-md text-[11px] font-bold bg-amber-50 text-amber-800 border border-amber-200">
                            Nilai Disesuaikan Guru
                        </span>
                    @endif
                </div>
                <h1 class="text-xl font-bold text-slate-900">{{ $attempt->quiz_title }}</h1>
                <p class="text-xs text-slate-500">Materi: {{ $attempt->module_title }}</p>

                <div class="pt-2 flex items-center gap-3 text-xs text-slate-600">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-user-graduate text-[#008546]"></i>
                        <span>{{ $attempt->user?->name ?? 'Akun Siswa Dihapus' }}</span>
                    </div>
                    @if($attempt->user?->class_name)
                        <span>&bull; Kelas: {{ $attempt->user->class_name }}</span>
                    @endif
                    @if($attempt->user?->student_number)
                        <span>&bull; NIS: {{ $attempt->user->student_number }}</span>
                    @endif
                </div>
            </div>

            <div class="sm:text-right bg-slate-50 p-4 rounded-2xl border border-slate-200/80 shrink-0">
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider">Nilai Akhir Siswa</p>
                <p class="text-3xl font-extrabold {{ $attempt->score >= 75 ? 'text-[#008546]' : 'text-amber-600' }} mt-0.5">
                    {{ $attempt->score }} <span class="text-xs font-medium text-slate-400">/ 100</span>
                </p>
                <p class="text-[11px] font-medium text-slate-500 mt-1">
                    Benar {{ $attempt->correct_count }} dari {{ $attempt->question_count }} Soal
                </p>
            </div>
        </div>

        <!-- Panel Remedial & Penyesuaian Nilai Guru -->
        <div class="bg-white p-6 sm:p-7 rounded-3xl border-2 border-slate-100 shadow-sm space-y-5">
            <div class="border-b border-slate-100 pb-3">
                <h2 class="text-sm font-bold text-slate-900 flex items-center gap-2">
                    <i class="fa-solid fa-sliders text-[#008546]"></i>
                    <span>Panel Remedial & Penyesuaian Nilai Guru</span>
                </h2>
                <p class="text-xs text-slate-500 mt-0.5">Guru dapat menyetujui izin ujian ulang di aplikasi atau langsung mengubah nilai remedial (misalnya hasil remedial praktik/lisan). Nilai baru menggantikan skor aktif dan nilai lama tetap tersimpan dalam riwayat.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- 1. Approve / Batalkan Ujian Ulang -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">1. Izin Ujian Ulang di Sistem</h3>
                    @if($attempt->hasApprovedRemedial())
                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-900 space-y-1">
                            <p class="font-bold">Ujian ulang remedial sedang dibuka untuk siswa.</p>
                            <p class="text-[11px]">Disetujui oleh: {{ $attempt->approvedBy?->name ?? 'Guru' }} ({{ $attempt->remedial_approved_at->timezone(config('app.display_timezone'))->translatedFormat('d M Y, H:i') }} WIB)</p>
                            @if($attempt->remedial_note)
                                <p class="text-[11px] italic">Catatan: "{{ $attempt->remedial_note }}"</p>
                            @endif
                        </div>
                        <form method="POST" action="{{ route('guru.quiz-recap.revoke-remedial', $attempt) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 rounded-xl bg-white hover:bg-rose-50 border border-rose-300 text-rose-700 text-xs font-bold transition-colors">
                                Batalkan Izin Ujian Ulang
                            </button>
                        </form>
                    @elseif($attempt->module_id && $attempt->module?->quiz)
                        <form method="POST" action="{{ route('guru.quiz-recap.approve-remedial', $attempt) }}" class="space-y-3">
                            @csrf
                            <div>
                                <label for="remedial_approve_note" class="block text-[11px] font-semibold text-slate-600 mb-1">Catatan Remedial (Opsional)</label>
                                <input id="remedial_approve_note" type="text" name="remedial_note" maxlength="500"
                                       placeholder="Misal: Kesempatan remedial KD Jaringan Nirkabel"
                                       class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-xs outline-none focus:border-[#008546]">
                            </div>
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold transition-colors">
                                Approve & Buka Ujian Ulang Remedial
                            </button>
                        </form>
                    @else
                        <p class="text-xs text-slate-500 italic">Kuis atau modul asal sudah dihapus sehingga ujian ulang tidak tersedia; gunakan penyesuaian nilai langsung jika diperlukan.</p>
                    @endif
                </div>

                <!-- 2. Ubah Nilai Remedial Langsung -->
                <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200 space-y-3">
                    <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">2. Ubah Nilai Remedial Langsung</h3>
                    <form method="POST" action="{{ route('guru.quiz-recap.update-remedial-score', $attempt) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <div>
                                <label for="remedial_score" class="block text-[11px] font-semibold text-slate-600 mb-1">Nilai Baru (0–100)</label>
                                <input id="remedial_score" type="number" name="score" min="0" max="100" required
                                       value="{{ old('score', $attempt->score < 75 ? 75 : $attempt->score) }}"
                                       class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-xs font-bold outline-none focus:border-[#008546]">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="remedial_score_note" class="block text-[11px] font-semibold text-slate-600 mb-1">Keterangan Perbaikan</label>
                                <input id="remedial_score_note" type="text" name="remedial_note" maxlength="500"
                                       placeholder="Misal: Nilai remedial praktik / lisan tuntas"
                                       value="{{ old('remedial_note') }}"
                                       class="w-full bg-white border border-slate-300 rounded-xl py-2 px-3 text-xs outline-none focus:border-[#008546]">
                            </div>
                        </div>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-colors">
                            Simpan Nilai Remedial
                        </button>
                    </form>
                </div>
            </div>

            @if($attempt->historyEntries())
                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                    <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wider">Riwayat Nilai Sebelumnya</h3>
                    <div class="space-y-2">
                        @foreach($attempt->historyEntries() as $entry)
                            <div class="flex flex-wrap items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs">
                                <div class="space-y-0.5">
                                    <span class="font-bold text-slate-800">
                                        {{ ($entry['type'] ?? '') === 'manual_adjustment' ? 'Sebelum Penyesuaian Guru' : 'Percobaan #'.($entry['attempt_number'] ?? 1) }}
                                        &bull; Nilai: {{ $entry['score'] ?? '-' }}/100
                                    </span>
                                    @if(! empty($entry['note']))
                                        <p class="text-[11px] text-slate-500">{{ $entry['note'] }}</p>
                                    @endif
                                </div>
                                <span class="text-[11px] text-slate-400">
                                    {{ ! empty($entry['recorded_at']) ? \Carbon\Carbon::parse($entry['recorded_at'])->timezone(config('app.display_timezone'))->translatedFormat('d M Y, H:i') : '-' }} WIB
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Question By Question Review -->
        <div class="space-y-4">
            <h3 class="font-bold text-sm text-slate-800 flex items-center gap-2">
                <i class="fa-solid fa-list-check text-[#008546]"></i>
                <span>Lembar Jawaban Siswa</span>
            </h3>

            @foreach($attempt->questions_snapshot as $index => $question)
                @php
                    $userAnswer = $attempt->answers[$index] ?? null;
                    $isCorrect = $userAnswer === $question['correct_answer'];
                @endphp
                <div class="bg-white p-6 rounded-3xl border-2 {{ $isCorrect ? 'border-emerald-100' : 'border-rose-100' }} shadow-xs space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                        <span class="font-bold text-xs text-slate-700">Soal Nomor {{ $index + 1 }}</span>
                        @if($isCorrect)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fa-solid fa-check text-[10px]"></i> Benar (+{{ round(100 / $attempt->question_count, 1) }})
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                <i class="fa-solid fa-xmark text-[10px]"></i> Salah (0)
                            </span>
                        @endif
                    </div>

                    <p class="text-xs sm:text-sm font-medium text-slate-800 whitespace-pre-line leading-relaxed">
                        {{ $question['text'] }}
                    </p>

                    <div class="grid grid-cols-1 gap-2 pt-1">
                        @foreach($question['options'] as $letter => $optionText)
                            @php
                                $isChosen = $userAnswer === $letter;
                                $isKey = $question['correct_answer'] === $letter;
                            @endphp
                            <div class="flex items-start gap-3 p-3 rounded-xl border text-xs {{ $isKey ? 'bg-emerald-50/70 border-emerald-300 text-emerald-950 font-semibold' : ($isChosen ? 'bg-rose-50/70 border-rose-300 text-rose-950 font-semibold' : 'bg-slate-50/50 border-slate-200 text-slate-600') }}">
                                <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 {{ $isKey ? 'bg-emerald-600 text-white' : ($isChosen ? 'bg-rose-600 text-white' : 'bg-slate-200 text-slate-700') }}">
                                    {{ $letter }}
                                </span>
                                <div class="flex-1 pt-0.5">
                                    <span>{{ $optionText }}</span>
                                    @if($isKey)
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-0.5">✓ Kunci Jawaban Benar</span>
                                    @endif
                                    @if($isChosen && ! $isKey)
                                        <span class="block text-[10px] text-rose-600 font-bold mt-0.5">✗ Pilihan Jawaban Siswa</span>
                                    @elseif($isChosen && $isKey)
                                        <span class="block text-[10px] text-emerald-700 font-bold mt-0.5">★ Dipilih Siswa (Tepat)</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-premium-layout>
