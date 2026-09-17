<x-premium-layout>
            @if(session('error'))
                <div role="alert" class="rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">{{ session('error') }}</div>
            @endif

    <div class="h-full flex flex-col gap-6">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                        <i class="fa-solid fa-layer-group text-[10px]"></i> {{ $module->kb_nomor ?? 'KB 1' }}
                    </span>
                    <span class="text-xs text-slate-400">• {{ $module->mapel }}</span>
                </div>
                <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                    {{ $module->judul }}
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.modules.edit', $module->id) }}" class="inline-flex items-center px-4 py-2.5 bg-amber-50 hover:bg-amber-100 text-amber-700 text-sm font-semibold rounded-full shadow-sm transition-colors gap-2 border border-amber-200">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Modul
                </a>
                @if(in_array($module->status_indexing, ['failed', 'completed']))
                <form action="{{ route('guru.modules.reindex', $module->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-sm font-semibold rounded-full shadow-sm transition-colors gap-2 border border-blue-200">
                        <i class="fa-solid fa-rotate-right"></i> Coba Index Ulang
                    </button>
                </form>
                @endif
                <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-full shadow-sm transition-colors gap-2">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <form action="{{ route('guru.modules.destroy', $module->id) }}" method="POST" onsubmit="if(!confirm('Apakah Anda yakin ingin menghapus modul ini?')) return false; this.querySelector('button[type=submit]').disabled = true; return true;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-semibold rounded-full shadow-sm transition-colors gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="flex-1 overflow-y-auto space-y-6">
            
            <!-- Alert Notifications -->
            @if($module->indexing_error)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-xl" role="alert">{{ $module->indexing_error }}</div>
            @endif
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-3xl border-2 border-gray-100 shadow-sm p-8 space-y-6">
                <div>
                    <h3 class="text-base font-bold text-gray-900 mb-1">Informasi Kegiatan Pembelajaran</h3>
                    <p class="text-xs text-slate-400">Rincian Tujuan Pembelajaran, media pendukung, dan status sistem RAG AI.</p>
                </div>

                <!-- TP (Tujuan Pembelajaran) Section -->
                <div class="p-5 bg-slate-50 rounded-2xl border border-slate-200">
                    <h4 class="text-xs font-bold text-[#008546] uppercase tracking-wider flex items-center gap-2 mb-2">
                        <i class="fa-solid fa-bullseye"></i> Tujuan Pembelajaran (TP)
                    </h4>
                    @if($module->tp)
                        <p class="text-sm text-slate-800 leading-relaxed whitespace-pre-line">{{ $module->tp }}</p>
                    @else
                        <p class="text-xs text-slate-400 italic">Tujuan Pembelajaran (TP) belum diisi untuk modul ini.</p>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <dt class="text-xs font-medium text-gray-500">Kegiatan Belajar</dt>
                        <dd class="mt-1 text-sm font-bold text-gray-900">{{ $module->kb_nomor ?? 'KB 1' }}</dd>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <dt class="text-xs font-medium text-gray-500">Mata Pelajaran</dt>
                        <dd class="mt-1 text-sm font-bold text-gray-900">{{ $module->mapel }}</dd>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <dt class="text-xs font-medium text-gray-500">Status Indexing AI</dt>
                        <dd class="mt-1 text-sm">
                            @if($module->status_indexing === 'completed')
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-emerald-100 text-emerald-800">Selesai (Siap Tanya AI)</span>
                            @elseif($module->status_indexing === 'processing')
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-blue-100 text-blue-800 animate-pulse">Sedang Diekstrak</span>
                            @elseif($module->status_indexing === 'failed')
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-red-100 text-red-800">Gagal Index</span>
                            @else
                                <span class="px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Menunggu Antrian</span>
                            @endif
                        </dd>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <dt class="text-xs font-medium text-gray-500">File Dokumen Asli</dt>
                        <dd class="mt-1">
                            <a href="{{ route('modules.download', $module->id) }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#008546] hover:text-[#00703c] bg-emerald-50 px-3 py-1 rounded-lg border border-emerald-200">
                                <i class="fa-solid fa-download"></i> Unduh File
                            </a>
                        </dd>
                    </div>
                </dl>

                <!-- Video & Kuis Quick Access -->
                <a href="{{ route('guru.modules.quiz.edit', $module) }}" class="inline-flex px-4 py-3 rounded-xl bg-purple-50 text-purple-700 font-semibold text-sm">Buat / Kelola Kuis Objektif →</a>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    @php
                        $isDriveUrl = $module->video_url && str_contains($module->video_url, 'drive.google.com');
                    @endphp
                    <div class="p-4 rounded-2xl border border-slate-200 flex items-center justify-between {{ $module->video_url ? ($isDriveUrl ? 'bg-amber-50/50' : 'bg-rose-50/50') : 'bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $module->video_url ? ($isDriveUrl ? 'bg-amber-500 text-white' : 'bg-rose-500 text-white') : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-lg">
                                <i class="{{ $isDriveUrl ? 'fa-brands fa-google-drive' : 'fa-brands fa-youtube' }}"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">Video Pembelajaran</h5>
                                <p class="text-[11px] text-slate-500">{{ $module->video_url ? ($isDriveUrl ? 'Tautan video Google Drive' : 'Tautan video YouTube') : 'Belum ditambahkan' }}</p>
                            </div>
                        </div>
                        @if($module->video_url)
                            <a href="{{ $module->video_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 {{ $isDriveUrl ? 'bg-amber-600 hover:bg-amber-700' : 'bg-rose-600 hover:bg-rose-700' }} text-white text-xs font-bold rounded-xl shadow-xs">
                                Tonton Video <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                            </a>
                        @endif
                    </div>

                    <div class="p-4 rounded-2xl border border-slate-200 flex items-center justify-between {{ $module->kuis_url ? 'bg-emerald-50/50' : 'bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $module->kuis_url ? 'bg-[#008546] text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-lg">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">Link Kuis Eksternal</h5>
                                <p class="text-[11px] text-slate-500">{{ $module->kuis_url ? 'Tautan kuis aktif' : 'Belum ditambahkan' }}</p>
                            </div>
                        </div>
                        @if($module->kuis_url)
                            <a href="{{ $module->kuis_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl shadow-xs">
                                Buka Kuis <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>

                @php $additionalVideos = $module->additionalVideos(); @endphp

                @if(!empty($additionalVideos))
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-2">
                        <span class="text-xs font-bold text-slate-700 block">Tautan Media & Video Tambahan:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($additionalVideos as $extra)
                                <a href="{{ $extra['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-2.5 bg-white hover:bg-slate-100 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 transition-colors">
                                    <span class="flex items-center gap-2">
                                        <i class="{{ $extra['icon'] }} {{ $extra['color'] }}"></i>
                                        <span>{{ $extra['title'] }}</span>
                                    </span>
                                    <i class="fa-solid fa-arrow-up-right-from-square text-[10px] text-slate-400"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="bg-white rounded-3xl border-2 border-gray-100 shadow-sm p-8 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Pratinjau teks hasil ekstraksi ({{ $chunkCount }} chunk)</h3>
                <p class="text-sm text-slate-500 mb-3">Periksa teks, urutan, dan kelengkapan materi sebelum digunakan siswa. PDF scan memerlukan OCR terlebih dahulu.</p>
                @forelse($chunks as $chunk)
                    <details class="bg-gray-50 p-4 rounded-lg border mb-3">
                        <summary>Chunk #{{ ($chunk->chunk_index ?? 0) + 1 }} ? {{ $chunk->embedding_model ?: 'Indeks lama' }} ? {{ $chunk->embedding_dimensions ?? '?' }} dimensi</summary>
                        <pre class="whitespace-pre-wrap text-sm mt-3 font-sans">{{ $chunk->chunk_text }}</pre>
                    </details>
                @empty
                    <p class="text-sm text-gray-500 py-4">Tidak ada chunk yang ditemukan.</p>
                @endforelse
                {{ $chunks->links() }}
                @if(in_array($module->status_indexing, ['pending', 'processing']))
                    <p class="text-sm text-blue-600">Ekstraksi masih menunggu atau sedang diproses worker RAG.</p>
                @elseif($module->status_indexing === 'failed')
                    <p class="text-sm text-red-600">Pemrosesan gagal. Periksa pesan kesalahan lalu coba indeks ulang.</p>
                @endif
            </div>

        </div>
    </div>
</x-premium-layout>
