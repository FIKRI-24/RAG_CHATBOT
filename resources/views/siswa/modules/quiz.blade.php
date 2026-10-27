@php
    /** @var \Illuminate\Support\ViewErrorBag $errors */
@endphp
<x-student-quiz-layout>
    <div class="max-w-4xl mx-auto p-4 sm:p-8 space-y-6">
        
        <!-- Breadcrumb Navigation -->
        <div>
            <a href="{{ route('siswa.modules.show', $module) }}" class="inline-flex items-center gap-2 text-xs font-bold text-[#008546] hover:text-emerald-800 transition-colors">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali ke {{ $module->kb_nomor }}</span>
            </a>
        </div>

        <!-- Cyber-Network Glassmorphism Hero Header -->
        <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-950 via-slate-900 to-emerald-950/80 text-white p-6 sm:p-8 shadow-sm">
            <!-- Subtle Cyber Grid Background -->
            <div class="absolute inset-0 opacity-[0.07] pointer-events-none" style="background-image: radial-gradient(rgba(255,255,255,0.4) 1px, transparent 1px); background-size: 20px 20px;"></div>
            <div class="absolute -top-16 -right-16 w-56 h-56 bg-emerald-500/15 rounded-full blur-3xl pointer-events-none"></div>

            <div class="relative z-10 space-y-3">
                <div class="flex items-center gap-2.5 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-emerald-300 border border-white/15 text-xs font-semibold backdrop-blur-md">
                        <i class="fa-solid fa-clipboard-question text-emerald-400"></i>
                        <span>Evaluasi Objektif</span>
                    </span>
                    <span class="text-xs text-slate-400 font-medium">
                        {{ $module->mapel }} • {{ $module->kb_nomor }}
                    </span>
                </div>

                <h1 class="text-xl sm:text-2xl font-bold tracking-tight text-white">
                    {{ $quiz->title }}
                </h1>
                <p class="text-xs sm:text-sm text-slate-300 max-w-2xl leading-relaxed">
                    {{ $module->judul }}
                </p>

                <!-- Warning Notice Pill -->
                <div class="pt-2">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2.5 rounded-2xl bg-amber-500/15 border border-amber-400/25 text-amber-200 text-xs leading-relaxed">
                        <i class="fa-solid fa-triangle-exclamation text-amber-400 shrink-0 text-sm"></i>
                        <span><strong>Peringatan Kejujuran:</strong> Kuis ini hanya dapat dikerjakan <strong>1 (satu) kali</strong>. Periksa kembali jawaban Anda dengan teliti sebelum menekan tombol Kirim Jawaban.</span>
                    </div>
                </div>
            </div>
        </div>

        @if($errors->any())
            <div role="alert" class="p-4 bg-rose-50 text-rose-800 rounded-2xl border border-rose-200 text-xs sm:text-sm">
                <div class="flex items-center gap-2 font-bold mb-1">
                    <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                    <span>Terdapat kendala pada pengiriman kuis:</span>
                </div>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('siswa.modules.quiz.submit', $module) }}" class="space-y-6">
            @csrf
            <input type="hidden" name="quiz_id" value="{{ $quiz->id }}">
            <input type="hidden" name="quiz_version" value="{{ $quiz->version }}">

            @foreach($questions as $index => $question)
                <fieldset class="bg-white p-6 sm:p-7 rounded-2xl border border-slate-200/90 shadow-xs space-y-4">
                    <legend class="sr-only">Soal {{ $index + 1 }}</legend>
                    
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs border border-emerald-100">
                            <i class="fa-solid fa-question text-[10px]"></i>
                            <span>Soal {{ sprintf('%02d', $index + 1) }}</span>
                        </span>
                        <span class="text-[11px] text-slate-400 font-medium">Pilihan Ganda</span>
                    </div>

                    <p class="whitespace-pre-line text-sm sm:text-base font-medium text-slate-800 leading-relaxed">
                        {{ $question['text'] }}
                    </p>

                    <div class="space-y-2.5 pt-1">
                        @foreach($question['options'] as $letter => $option)
                            <label class="flex items-start gap-3.5 rounded-xl border border-slate-200/90 p-4 cursor-pointer hover:border-emerald-300 hover:bg-emerald-50/40 transition-all group has-checked:border-[#008546] has-checked:bg-emerald-50/30">
                                <input 
                                    type="radio" 
                                    name="answers[{{ $index }}]" 
                                    value="{{ $letter }}" 
                                    required 
                                    @checked(! $errors->has('quiz_version') && old('answers.'.$index) === $letter) 
                                    class="mt-1 text-[#008546] focus:ring-emerald-500 accent-[#008546]"
                                >
                                <div class="flex items-start gap-2.5 flex-1">
                                    <span class="w-6 h-6 rounded-lg bg-slate-100 group-hover:bg-emerald-100 text-slate-700 group-hover:text-emerald-800 font-bold text-xs flex items-center justify-center shrink-0 transition-colors">
                                        {{ $letter }}
                                    </span>
                                    <span class="text-xs sm:text-sm text-slate-700 font-normal leading-relaxed pt-0.5">
                                        {{ $option }}
                                    </span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach

            <!-- Action Buttons Footer -->
            <div class="flex items-center justify-between gap-4 pt-2">
                <a href="{{ route('siswa.modules.show', $module) }}" class="px-5 py-3 rounded-xl border border-slate-200 hover:bg-slate-100 text-slate-600 font-bold text-xs transition-all">
                    Batal
                </a>
                <button type="submit" class="inline-flex items-center gap-2 px-7 py-3.5 bg-[#008546] hover:bg-emerald-800 text-white font-bold text-sm rounded-xl shadow-md hover:shadow-lg transition-all active:scale-95">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    <span>Kirim Jawaban Kuis</span>
                </button>
            </div>
        </form>
    </div>
</x-student-quiz-layout>
