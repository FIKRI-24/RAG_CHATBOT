<x-premium-layout>
    <div class="h-full flex flex-col gap-6">
        
        <!-- Header Section -->
        <div class="flex justify-between items-center mb-2">
            <h2 class="font-bold text-2xl text-gray-800 tracking-tight">
                {{ __('Manajemen Modul') }}
            </h2>
            <a href="{{ route('guru.modules.create') }}" class="inline-flex items-center px-5 py-2.5 bg-[#008546] hover:bg-[#006e39] text-white text-sm font-semibold rounded-full shadow-md transition-colors gap-2">
                <i class="fa-solid fa-cloud-arrow-up"></i> Upload Modul Baru
            </a>
        </div>
        
        <!-- Main Content Section -->
        <div class="flex-1 flex flex-col min-h-0 bg-white rounded-3xl border-2 border-gray-100 shadow-sm p-6 overflow-hidden">
            
            @if(session('success'))
                <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-2xl flex items-center justify-between text-xs font-semibold" role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-2xl flex items-center justify-between text-xs font-semibold" role="alert">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-rose-600 text-base"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                </div>
            @endif

            <div class="flex flex-col h-full">
                
                <form method="GET" action="{{ route('guru.modules.index') }}" class="mb-6 flex flex-wrap gap-3">
                    <div class="w-full sm:w-64 relative">
                        <select name="mapel" id="mapel" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-2.5 px-4 pr-10 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#008546] focus:border-transparent w-full text-sm font-medium" onchange="this.form.submit()">
                            <option value="">Semua Mata Pelajaran</option>
                            @foreach($mapelList as $mapel)
                                <option value="{{ $mapel }}" {{ request('mapel') == $mapel ? 'selected' : '' }}>{{ $mapel }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    <div class="w-full sm:w-48 relative">
                        <select name="kb_nomor" id="kb_nomor" class="appearance-none bg-gray-50 border border-gray-200 text-gray-700 py-2.5 px-4 pr-10 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-[#008546] focus:border-transparent w-full text-sm font-medium" onchange="this.form.submit()">
                            <option value="">Semua Kegiatan Belajar</option>
                            @foreach($kbList as $kb)
                                <option value="{{ $kb }}" {{ request('kb_nomor') == $kb ? 'selected' : '' }}>{{ $kb }}</option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                            <i class="fa-solid fa-chevron-down text-xs"></i>
                        </div>
                    </div>

                    @if(request('mapel') || request('kb_nomor'))
                        <a href="{{ route('guru.modules.index') }}" class="inline-flex items-center px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold">
                            Reset Filter
                        </a>
                    @endif
                </form>

                <div class="overflow-y-auto flex-1 rounded-2xl border border-gray-100">
                    <table class="min-w-full divide-y divide-gray-100 relative">
                        <thead class="bg-gray-50 sticky top-0 z-10">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">No</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kegiatan Belajar</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul & Mapel</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Media & TP</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status AI</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            @forelse($modules as $index => $module)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $modules->firstItem() + $index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-bold bg-emerald-50 text-[#008546] border border-emerald-200">
                                        <i class="fa-solid fa-layer-group text-[10px]"></i> {{ $module->kb_nomor ?? 'KB 1' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-bold text-slate-900 text-sm">{{ $module->judul }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $module->mapel }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($module->tp)
                                            <span class="text-xs bg-slate-100 text-slate-700 px-2 py-0.5 rounded font-medium" title="{{ $module->tp }}">TP Ada</span>
                                        @endif
                                        @if($module->video_url)
                                            <a href="{{ $module->video_url }}" target="_blank" class="text-rose-500 hover:text-rose-600 text-sm" title="Buka Video Pembelajaran">
                                                <i class="fa-brands fa-youtube"></i>
                                            </a>
                                        @endif
                                        @if($module->kuis_url)
                                            <a href="{{ $module->kuis_url }}" target="_blank" class="text-emerald-600 hover:text-emerald-700 text-sm" title="Buka Link Kuis">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($module->isExpired())
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-gray-100 text-gray-800">Diarsipkan</span>
                                    @elseif($module->status_indexing === 'completed')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-emerald-100 text-emerald-800">Aktif</span>
                                    @elseif($module->status_indexing === 'processing')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-blue-100 text-blue-800 animate-pulse">Diproses</span>
                                    @elseif($module->status_indexing === 'failed')
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-red-100 text-red-800">Gagal</span>
                                    @else
                                        <span class="px-2.5 py-1 inline-flex text-xs leading-4 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center gap-1.5">
                                        <a href="{{ route('guru.modules.quiz.edit', $module) }}" class="inline-flex items-center px-3 h-8 rounded-xl bg-purple-50 text-purple-700 hover:bg-purple-100 text-xs font-semibold" title="Buat dan kelola kuis pilihan ganda">Kelola Kuis</a>
                                        <!-- Detail -->
                                        <a href="{{ route('guru.modules.show', $module->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Lihat Detail">
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </a>

                                        <!-- Edit -->
                                        <a href="{{ route('guru.modules.edit', $module->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit Modul & Media">
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </a>

                                        <!-- Download File Asli -->
                                        <a href="{{ route('modules.download', $module->id) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-emerald-50 text-[#008546] hover:bg-emerald-100 transition-colors" title="Download File Materi">
                                            <i class="fa-solid fa-download text-xs"></i>
                                        </a>

                                        <!-- Hapus -->
                                        <form action="{{ route('guru.modules.destroy', $module->id) }}" method="POST" onsubmit="if(!confirm('Apakah Anda yakin ingin menghapus modul ini?')) return false; this.querySelector('button[type=submit]').disabled = true; return true;" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-red-50 text-red-600 hover:bg-red-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed" title="Hapus Modul">
                                                <i class="fa-solid fa-trash-can text-xs"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 whitespace-nowrap text-sm text-center text-gray-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <i class="fa-solid fa-folder-open text-4xl text-gray-300"></i>
                                        <span>Belum ada modul yang diupload.</span>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-6">
                    {{ $modules->links() }}
                </div>
            </div>
        </div>
    </div>
</x-premium-layout>
