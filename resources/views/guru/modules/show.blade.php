<x-premium-layout>
    @if(session('error'))
        <div role="alert" class="rounded-lg border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 mb-4">{{ session('error') }}</div>
    @endif

    <div class="h-full flex flex-col gap-6 max-w-6xl mx-auto">
        
        <!-- Header Section -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-xl border border-slate-200/90">
            <div>
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-mono font-bold bg-slate-900 text-white">
                        {{ $module->kb_nomor ?? 'KB 1' }}
                    </span>
                    <span class="text-xs font-medium text-slate-500">• {{ $module->mapel }}</span>
                </div>
                <h2 class="font-bold text-xl text-slate-900 tracking-tight">
                    {{ $module->judul }}
                </h2>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('guru.modules.edit', $module->id) }}" class="inline-flex items-center px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold rounded-lg transition-colors gap-1.5 border border-slate-200">
                    <i class="fa-solid fa-pen-to-square"></i> Edit Modul
                </a>
                @if(in_array($module->status_indexing, ['pending', 'failed', 'completed']))
                <form action="{{ route('guru.modules.reindex', $module->id) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg transition-colors gap-1.5">
                        <i class="fa-solid fa-rotate-right"></i> Coba Index Ulang
                    </button>
                </form>
                @endif
                <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center px-3.5 py-2 bg-white hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded-lg border border-slate-200 transition-colors gap-1.5">
                    <i class="fa-solid fa-arrow-left"></i> Kembali
                </a>
                <form action="{{ route('guru.modules.destroy', $module->id) }}" method="POST" onsubmit="if(!confirm('Apakah Anda yakin ingin menghapus modul ini?')) return false; this.querySelector('button[type=submit]').disabled = true; return true;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center px-3.5 py-2 bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 text-xs font-semibold rounded-lg transition-colors gap-1.5 disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="fa-solid fa-trash-can"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <!-- Main Content Section -->
        <div class="flex-1 space-y-6">
            
            <!-- Alert Notifications -->
            @if($module->indexing_error)
                <div class="bg-amber-50 border border-amber-200 text-amber-900 p-4 rounded-lg text-xs font-medium" role="alert">{{ $module->indexing_error }}</div>
            @endif
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg flex items-center justify-between text-xs font-semibold">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            <div class="bg-white rounded-xl border border-slate-200/90 p-6 space-y-6">
                <div class="border-b border-slate-100 pb-3 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900">Informasi Kegiatan Pembelajaran</h3>
                        <p class="text-xs text-slate-500">Rincian Tujuan Pembelajaran, media pendukung, dan status sistem RAG AI.</p>
                    </div>
                    <a href="{{ route('guru.modules.quiz.edit', $module) }}" class="inline-flex items-center px-3.5 py-2 rounded-lg bg-[#008546] hover:bg-[#00703c] text-white font-semibold text-xs transition-colors">
                        Buat / Kelola Kuis Objektif →
                    </a>
                </div>

                <!-- TP (Tujuan Pembelajaran) Section -->
                <div class="p-4 bg-slate-50 rounded-lg border-l-2 border-[#008546]">
                    <h4 class="text-xs font-bold text-slate-900 uppercase tracking-wider mb-1.5">
                        Tujuan Pembelajaran (TP)
                    </h4>
                    @if($module->tp)
                        <p class="text-sm text-slate-700 leading-relaxed whitespace-pre-line">{{ $module->tp }}</p>
                    @else
                        <p class="text-xs text-slate-400 italic">Tujuan Pembelajaran (TP) belum diisi untuk modul ini.</p>
                    @endif
                </div>

                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80">
                        <dt class="text-[11px] font-medium text-slate-500">Kegiatan Belajar</dt>
                        <dd class="mt-1 text-sm font-mono font-bold text-slate-900">{{ $module->kb_nomor ?? 'KB 1' }}</dd>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80">
                        <dt class="text-[11px] font-medium text-slate-500">Mata Pelajaran</dt>
                        <dd class="mt-1 text-sm font-bold text-slate-900">{{ $module->mapel }}</dd>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80">
                        <dt class="text-[11px] font-medium text-slate-500">Status Indexing AI</dt>
                        <dd class="mt-1 text-xs">
                            @if($module->status_indexing === 'completed')
                                <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-emerald-50 text-[#008546] border border-emerald-200">Selesai (Siap Tanya AI)</span>
                            @elseif($module->status_indexing === 'processing')
                                <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-amber-50 text-amber-800 border border-amber-200">Sedang Diekstrak</span>
                            @elseif($module->status_indexing === 'failed')
                                <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-rose-50 text-rose-800 border border-rose-200">Gagal Index</span>
                            @else
                                <span class="px-2 py-0.5 inline-flex font-semibold rounded bg-slate-200 text-slate-800">Menunggu Antrian</span>
                            @endif
                        </dd>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-lg border border-slate-200/80">
                        <dt class="text-[11px] font-medium text-slate-500">File Dokumen Asli</dt>
                        <dd class="mt-1">
                            <a href="{{ route('modules.download', $module->id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-800 hover:text-[#008546] bg-white px-2.5 py-1 rounded border border-slate-300">
                                <i class="fa-solid fa-download text-[10px]"></i> Unduh File
                            </a>
                        </dd>
                    </div>
                </dl>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    @php
                        $isDriveUrl = $module->video_url && str_contains($module->video_url, 'drive.google.com');
                    @endphp
                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-900">Video Pembelajaran</h5>
                            <p class="text-[11px] text-slate-500">{{ $module->video_url ? ($isDriveUrl ? 'Tautan video Google Drive' : 'Tautan video YouTube') : 'Belum ditambahkan' }}</p>
                        </div>
                        @if($module->video_url)
                            <a href="{{ $module->video_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold rounded-lg">
                                Tonton Video <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                            </a>
                        @endif
                    </div>

                    <div class="p-4 rounded-lg border border-slate-200 bg-slate-50 flex items-center justify-between">
                        <div>
                            <h5 class="text-xs font-bold text-slate-900">Link Kuis Eksternal</h5>
                            <p class="text-[11px] text-slate-500">{{ $module->kuis_url ? 'Tautan kuis aktif' : 'Belum ditambahkan' }}</p>
                        </div>
                        @if($module->kuis_url)
                            <a href="{{ $module->kuis_url }}" target="_blank" rel="noopener noreferrer" class="px-3 py-1.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-semibold rounded-lg">
                                Buka Kuis <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>

                @php $additionalVideos = $module->additionalVideos(); @endphp

                @if(!empty($additionalVideos))
                    <div class="p-4 bg-slate-50 rounded-lg border border-slate-200 space-y-2">
                        <span class="text-xs font-bold text-slate-700 block">Tautan Media & Video Tambahan:</span>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($additionalVideos as $extra)
                                <a href="{{ $extra['url'] }}" target="_blank" rel="noopener noreferrer" class="flex items-center justify-between p-2.5 bg-white hover:bg-slate-100 rounded-lg border border-slate-200 text-xs font-semibold text-slate-700 transition-colors">
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

            <div class="bg-white rounded-xl border border-slate-200/90 p-6 mb-8">
                <h3 class="text-sm font-bold text-slate-900 mb-1">Pratinjau teks hasil ekstraksi ({{ $chunkCount }} chunk)</h3>
                <p class="text-xs text-slate-500 mb-4">Periksa teks, urutan, dan kelengkapan materi sebelum digunakan siswa. PDF scan memerlukan OCR terlebih dahulu.</p>
                @forelse($chunks as $chunk)
                    <details class="bg-slate-50 p-3.5 rounded-lg border border-slate-200 mb-2.5 text-xs">
                        <summary class="font-mono font-semibold text-slate-800 cursor-pointer">Chunk #{{ ($chunk->chunk_index ?? 0) + 1 }} • {{ $chunk->embedding_model ?: 'Indeks lama' }} • {{ $chunk->embedding_dimensions ?? '?' }} dimensi</summary>
                        <pre class="whitespace-pre-wrap text-xs text-slate-700 mt-3 pt-3 border-t border-slate-200 font-sans leading-relaxed">{{ $chunk->chunk_text }}</pre>
                    </details>
                @empty
                    <p class="text-xs text-slate-500 py-4">Tidak ada chunk yang ditemukan.</p>
                @endforelse
                {{ $chunks->links() }}
                @if(in_array($module->status_indexing, ['pending', 'processing']))
                    <p class="text-xs text-amber-700 mt-2">Ekstraksi masih menunggu atau sedang diproses worker RAG.</p>
                @elseif($module->status_indexing === 'failed')
                    <p class="text-xs text-rose-600 mt-2">Pemrosesan gagal. Periksa pesan kesalahan lalu coba indeks ulang.</p>
                @endif
            </div>

        </div>
    </div>
</x-premium-layout>
