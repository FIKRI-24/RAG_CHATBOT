<x-siswa-layout>
    <div
        class="max-w-5xl mx-auto pb-12 space-y-8"
        x-data="{
            step: 1,
            openTip: 1
        }"
    >
        <!-- Editorial Header -->
        <header class="bg-white rounded-2xl border border-slate-200/90 p-6 sm:p-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
                <div class="max-w-2xl space-y-2.5">
                    <div class="flex items-center gap-2 text-xs font-semibold text-[#008546]">
                        <span class="w-5 h-0.5 bg-[#008546] rounded-full"></span>
                        <span>Panduan Belajar Digital • SMK Negeri 1 Kinali</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-snug">
                        Petunjuk Penggunaan E-Modul &amp; Asisten AI
                    </h1>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Ikuti alur belajar terstruktur pada setiap Kegiatan Belajar (KB) agar kamu memahami teori jaringan, demonstrasi praktikum, hingga siap mengerjakan evaluasi kuis.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                    <a href="{{ route('siswa.modules.index') }}" class="inline-flex items-center gap-2 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold px-4 py-2.5 rounded-xl transition-colors shadow-xs">
                        <i class="fa-solid fa-book-bookmark"></i>
                        <span>Buka Katalog E-Modul</span>
                    </a>
                    <a href="{{ route('siswa.dashboard') }}" class="inline-flex items-center gap-2 bg-slate-50 hover:bg-slate-100 text-slate-700 text-xs font-semibold px-3.5 py-2.5 rounded-xl border border-slate-200 transition-colors">
                        <i class="fa-solid fa-robot text-[#008546]"></i>
                        <span>Mulai Tanya AI</span>
                    </a>
                </div>
            </div>
        </header>

        <!-- 4 Tahap Belajar Interaktif -->
        <section class="space-y-4">
            <div class="flex flex-col sm:flex-row sm:items-baseline justify-between gap-1 border-b border-slate-200 pb-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">4 Tahap Belajar Tiap Kegiatan Belajar (KB)</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Klik setiap langkah untuk melihat panduan ringkas sebelum membuka modul.</p>
                </div>
                <span class="text-xs font-medium text-slate-500">Langkah <strong class="text-slate-900" x-text="'0' + step">01</strong> dari 04</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                <!-- Tahap 1: Pahami TP -->
                <button
                    type="button"
                    @click="step = 1"
                    :aria-pressed="step === 1"
                    :class="step === 1 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                    class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                >
                    <div class="flex items-center justify-between w-auto sm:w-full">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                            <!-- Custom Icon: Competency Target Card -->
                            <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="7" y="7" width="18" height="22" rx="3" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65"/>
                                <path d="M11 13H19M11 17.5H17M11 22H15" stroke="#0F172A" stroke-width="1.65" stroke-linecap="round"/>
                                <circle cx="24.5" cy="22.5" r="5.5" fill="#A7F3D0" stroke="#008546" stroke-width="1.65"/>
                                <path d="M22.2 22.6L23.8 24.2L27 21" stroke="#008546" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 1 ? 'text-[#008546]' : 'text-slate-400'">01</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Pahami TP</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Target kompetensi KB</div>
                    </div>
                </button>

                <!-- Tahap 2: Pelajari Materi -->
                <button
                    type="button"
                    @click="step = 2"
                    :aria-pressed="step === 2"
                    :class="step === 2 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                    class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                >
                    <div class="flex items-center justify-between w-auto sm:w-full">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                            <!-- Custom Icon: Module Document Study -->
                            <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M8.5 9.5C8.5 7.84315 9.84315 6.5 11.5 6.5H20L26.5 13V26.5C26.5 28.1569 25.1569 29.5 23.5 29.5H11.5C9.84315 29.5 8.5 28.1569 8.5 26.5V9.5Z" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65" stroke-linejoin="round"/>
                                <path d="M20 6.5V11.5C20 12.3284 20.6716 13 21.5 13H26.5" fill="#A7F3D0"/>
                                <path d="M20 6.5V11.5C20 12.3284 20.6716 13 21.5 13H26.5" stroke="#0F172A" stroke-width="1.65" stroke-linejoin="round"/>
                                <path d="M12.5 17.5H22.5M12.5 21.5H22.5M12.5 25.5H18" stroke="#008546" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 2 ? 'text-[#008546]' : 'text-slate-400'">02</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Pelajari Materi</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Unduh &amp; baca modul PDF/DOCX</div>
                    </div>
                </button>

                <!-- Tahap 3: Simak Video -->
                <button
                    type="button"
                    @click="step = 3"
                    :aria-pressed="step === 3"
                    :class="step === 3 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                    class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                >
                    <div class="flex items-center justify-between w-auto sm:w-full">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center shrink-0" aria-hidden="true">
                            <!-- Custom Icon: Video Demo Frame -->
                            <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="6" y="8.5" width="24" height="17" rx="3.5" fill="#ECFDF5" stroke="#0F172A" stroke-width="1.65"/>
                                <path d="M15.25 13.5L22 17L15.25 20.5V13.5Z" fill="#008546" stroke="#008546" stroke-width="1.5" stroke-linejoin="round"/>
                                <path d="M11 28.5H25" stroke="#0F172A" stroke-width="1.65" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 3 ? 'text-[#008546]' : 'text-slate-400'">03</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Simak Video</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Visualisasi &amp; praktik jaringan</div>
                    </div>
                </button>

                <!-- Tahap 4: Kerjakan Kuis -->
                <button
                    type="button"
                    @click="step = 4"
                    :aria-pressed="step === 4"
                    :class="step === 4 ? 'bg-white border-[#008546] ring-2 ring-emerald-500/15 shadow-xs' : 'bg-white/70 border-slate-200 hover:bg-white hover:border-slate-300'"
                    class="text-left p-4 rounded-xl border transition-all flex sm:flex-col items-center sm:items-start gap-3.5 sm:gap-3"
                >
                    <div class="flex items-center justify-between w-auto sm:w-full">
                        <div class="w-10 h-10 rounded-xl bg-[#008546] border border-emerald-700 flex items-center justify-center shrink-0" aria-hidden="true">
                            <!-- Custom Icon: Quiz Evaluation Matrix -->
                            <svg viewBox="0 0 36 36" class="w-6 h-6" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect x="7.5" y="7.5" width="21" height="21" rx="4" stroke="#A7F3D0" stroke-width="1.65"/>
                                <path d="M13 15.5L15.2 17.7L19.2 13.5" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/>
                                <path d="M21.5 15.5H24M13 22H24" stroke="#A7F3D0" stroke-width="1.75" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <span class="hidden sm:inline-block text-[11px] font-semibold" :class="step === 4 ? 'text-[#008546]' : 'text-slate-400'">04</span>
                    </div>
                    <div>
                        <div class="text-sm font-bold text-slate-900">Kerjakan Kuis</div>
                        <div class="text-[11px] text-slate-500 mt-0.5">Evaluasi pemahaman KB</div>
                    </div>
                </button>
            </div>

            <!-- Panel Detail Tiap Tahap -->
            <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6">
                <div x-show="step === 1" class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900">1. Pahami Tujuan Pembelajaran (TP)</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Setiap modul di katalog dilengkapi daftar <strong>Tujuan Pembelajaran (TP)</strong>. Bacalah terlebih dahulu agar kamu tahu kompetensi inti apa yang perlu dikuasai setelah menyelesaikan modul tersebut.
                    </p>
                </div>

                <div x-show="step === 2" x-cloak class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900">2. Pelajari Materi PDF / DOCX dari Guru</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Unduh berkas modul resmi pada halaman detail KB. Pelajari konsep dasar, tabel referensi, serta langkah-langkah praktikum jaringan sebelum beralih ke diskusi AI atau kuis.
                    </p>
                </div>

                <div x-show="step === 3" x-cloak class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900">3. Simak Video Pembelajaran Pendukung</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Putar video pembelajaran utama maupun video tambahan (YouTube / Google Drive) untuk melihat simulasi topologi, pemasangan kabel, atau konfigurasi perangkat jaringan secara langsung.
                    </p>
                </div>

                <div x-show="step === 4" x-cloak class="space-y-2">
                    <h3 class="text-base font-bold text-slate-900">4. Kerjakan Kuis Objektif &amp; Latihan Soal AI</h3>
                    <p class="text-xs sm:text-sm text-slate-600 leading-relaxed">
                        Setelah memahami materi, kerjakan <strong>Kuis Objektif</strong> pada halaman detail modul (perlu diperhatikan: kuis objektif hanya dapat dikerjakan <strong>1 kali</strong> dan hasilnya langsung tercatat di rekap nilai guru). Kamu juga bisa berlatih soal esai kapan saja melalui tombol <strong>Latihan Soal AI</strong> di ruang tanya jawab.
                    </p>
                </div>
            </div>
        </section>

        <!-- Panduan Tanya Jawab Asisten AI -->
        <section class="space-y-4 pt-2 border-t border-slate-200">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-lg font-bold text-slate-900">Cara Efektif Bertanya ke Asisten AI</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Asisten AI menjawab hanya berdasarkan isi modul TKJ resmi yang diunggah guru.</p>
                </div>
                <a href="{{ route('siswa.dashboard') }}" class="text-xs font-semibold text-[#008546] hover:underline">
                    Buka Ruang Tanya AI →
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Langkah Bertanya -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 space-y-4">
                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 border border-emerald-200/80 flex items-center justify-center">1</span>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Pilih Elemen &amp; Cakupan KB</h3>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Gunakan filter <strong>Elemen / Mapel</strong> dan <strong>Cakupan Modul</strong> di atas kotak chat agar AI memfokuskan pencarian ke materi yang sedang kamu pelajari.
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 border border-emerald-200/80 flex items-center justify-center">2</span>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Ajukan Pertanyaan Spesifik</h3>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Sebutkan istilah atau topik dengan jelas, misalnya: <em>"Apa perbedaan standar Wi-Fi IEEE 802.11n dan 802.11ac?"</em> atau <em>"Bagaimana cara kerja chain input di firewall?"</em>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 text-[#008546] font-bold text-xs shrink-0 border border-emerald-200/80 flex items-center justify-center">3</span>
                        <div>
                            <h3 class="text-xs sm:text-sm font-bold text-slate-900">Periksa Sumber Rujukan Modul</h3>
                            <p class="text-xs text-slate-600 mt-0.5 leading-relaxed">
                                Setiap jawaban AI menampilkan label rujukan modul (<span class="font-mono font-semibold text-slate-800">[1]</span>, <span class="font-mono font-semibold text-slate-800">[2]</span>) agar kamu bisa membuka kembali bagian modul aslinya untuk belajar lebih dalam.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Do & Don't -->
                <div class="bg-white rounded-2xl border border-slate-200/90 p-5 sm:p-6 space-y-4">
                    <div class="space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-[#008546]">
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Direkomendasikan</span>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 leading-relaxed">
                            <li>• Bertanya seputar konsep, istilah, dan langkah praktikum di modul TKJ.</li>
                            <li>• Meminta penjelasan ulang dengan bahasa lebih sederhana jika masih bingung.</li>
                            <li>• Mencoba menjawab <strong>Latihan Soal AI</strong> sendiri sebelum melihat umpan balik.</li>
                        </ul>
                    </div>

                    <div class="pt-4 border-t border-slate-100 space-y-2">
                        <div class="flex items-center gap-2 text-xs font-bold text-rose-700">
                            <i class="fa-solid fa-circle-xmark"></i>
                            <span>Dihindari</span>
                        </div>
                        <ul class="text-xs text-slate-600 space-y-1.5 leading-relaxed">
                            <li>• Menanyakan topik di luar materi kurikulum pembelajaran TKJ.</li>
                            <li>• Mengirim pesan spam atau kalimat yang tidak relevan dengan pelajaran.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    </div>
</x-siswa-layout>
