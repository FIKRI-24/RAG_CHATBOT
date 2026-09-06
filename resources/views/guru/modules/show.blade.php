<x-premium-layout>
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
                @if($module->status_indexing === 'failed')
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
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                    <div class="p-4 rounded-2xl border border-slate-200 flex items-center justify-between {{ $module->video_url ? 'bg-rose-50/50' : 'bg-slate-50' }}">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl {{ $module->video_url ? 'bg-rose-500 text-white' : 'bg-slate-200 text-slate-400' }} flex items-center justify-center text-lg">
                                <i class="fa-brands fa-youtube"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-slate-800">Video Pembelajaran</h5>
                                <p class="text-[11px] text-slate-500">{{ $module->video_url ? 'Tautan video tersedia' : 'Belum ditambahkan' }}</p>
                            </div>
                        </div>
                        @if($module->video_url)
                            <a href="{{ $module->video_url }}" target="_blank" class="px-3 py-1.5 bg-rose-600 hover:bg-rose-700 text-white text-xs font-bold rounded-xl shadow-xs">
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
                                <h5 class="text-xs font-bold text-slate-800">Kuis Evaluasi KB</h5>
                                <p class="text-[11px] text-slate-500">{{ $module->kuis_url ? 'Tautan kuis aktif' : 'Belum ditambahkan' }}</p>
                            </div>
                        </div>
                        @if($module->kuis_url)
                            <a href="{{ $module->kuis_url }}" target="_blank" class="px-3 py-1.5 bg-[#008546] hover:bg-[#00703c] text-white text-xs font-bold rounded-xl shadow-xs">
                                Buka Kuis <i class="fa-solid fa-arrow-up-right-from-square text-[10px] ml-1"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-3xl border-2 border-gray-100 shadow-sm p-8 mb-8">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Chunks Terindeks ({{ $module->chunks->count() }})</h3>
                
                @if($module->chunks->count() > 0)
                    <div class="space-y-4">
                        @foreach($module->chunks as $index => $chunk)
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                                <div class="text-xs text-gray-500 mb-2">Chunk #{{ $index + 1 }}</div>
                                <div class="text-sm text-gray-800">{{ Str::limit($chunk->chunk_text, 200) }}</div>
                            </div>
                        @endforeach
                    </div>
                    @if($module->status_indexing === 'processing' || $module->status_indexing === 'pending')
                        <div class="flex items-center gap-3 text-sm text-blue-600 bg-blue-50 p-4 rounded-xl">
                            <i class="fa-solid fa-circle-notch fa-spin"></i> Sedang memproses mengekstrak teks...
                        </div>
                    @elseif($module->status_indexing === 'failed')
                        <div class="flex items-center gap-3 text-sm text-red-600 bg-red-50 p-4 rounded-xl">
                            <i class="fa-solid fa-triangle-exclamation"></i> Terjadi kesalahan saat memproses modul ini.
                        </div>
                    @else
                        <div class="text-sm text-gray-500 text-center py-8">Tidak ada chunk yang ditemukan.</div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-premium-layout>
