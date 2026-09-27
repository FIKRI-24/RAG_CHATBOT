@php
    /** @var \App\Models\Module|null $module */
    $media = old('additional_videos', isset($module) ? array_map(fn ($video) => ['title' => $video['title'], 'url' => $video['url']], $module->additionalVideos()) : []);
@endphp

<div class="mt-8 pt-6 border-t-2 border-slate-200" x-data="{ videos: {{ Illuminate\Support\Js::from($media) }} }">
    <input type="hidden" name="media_present" value="1">
    
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div>
            <h4 class="text-base font-bold text-slate-900 flex items-center gap-2">
                <i class="fa-solid fa-film text-blue-700"></i> Video Pembelajaran Tambahan
            </h4>
            <p class="text-xs sm:text-sm font-medium text-slate-700 mt-1">
                Bapak/Ibu dapat menambahkan link video YouTube lain jika materi ini memerlukan video tambahan (Maksimal 10 video).
            </p>
        </div>
        <button type="button" 
                @click="videos.push({title: '', url: ''})" 
                :disabled="videos.length >= 10" 
                class="self-start sm:self-auto px-4 py-2.5 bg-emerald-50 hover:bg-emerald-100 disabled:opacity-50 text-emerald-800 border-2 border-emerald-700 font-bold text-xs sm:text-sm rounded-xl inline-flex items-center gap-2 transition-colors cursor-pointer shadow-xs">
            <i class="fa-solid fa-plus text-sm"></i>
            <span>Tambah Video Lain</span>
        </button>
    </div>

    <!-- Daftar Video Tambahan -->
    <template x-for="(video, index) in videos" :key="index">
        <div class="p-4 bg-slate-50 border-2 border-slate-300 rounded-2xl my-3 space-y-3 shadow-xs">
            <div class="flex items-center justify-between border-b border-slate-200 pb-2">
                <span class="text-xs sm:text-sm font-extrabold text-slate-900 flex items-center gap-1.5" x-text="`Video Tambahan #${index + 1}`"></span>
                <button type="button" 
                        @click="videos.splice(index, 1)" 
                        class="text-rose-700 hover:text-rose-900 bg-rose-50 hover:bg-rose-100 border border-rose-300 font-bold text-xs px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                    <i class="fa-solid fa-trash-can text-rose-600"></i>
                    <span>Hapus Video Ini</span>
                </button>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-900 mb-1">
                        Judul / Keterangan Video <span class="text-rose-600">*</span>
                    </label>
                    <input x-model="video.title" 
                           :name="`additional_videos[${index}][title]`" 
                           maxlength="100" 
                           required 
                           placeholder="Contoh: Tutorial Praktik Konfigurasi VLAN" 
                           class="w-full bg-white border-2 border-slate-300 text-slate-900 font-semibold text-sm rounded-xl py-2.5 px-3.5 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20">
                </div>
                <div>
                    <label class="block text-xs sm:text-sm font-bold text-slate-900 mb-1">
                        Tautan / URL Video (YouTube) <span class="text-rose-600">*</span>
                    </label>
                    <input x-model="video.url" 
                           :name="`additional_videos[${index}][url]`" 
                           maxlength="255" 
                           type="url" 
                           required 
                           placeholder="https://www.youtube.com/watch?v=..." 
                           class="w-full bg-white border-2 border-slate-300 text-slate-900 font-semibold text-sm rounded-xl py-2.5 px-3.5 outline-none focus:border-emerald-700 focus:ring-2 focus:ring-emerald-700/20">
                </div>
            </div>
        </div>
    </template>

    <div x-show="videos.length === 0" class="text-xs sm:text-sm font-medium text-slate-700 bg-white border-2 border-dashed border-slate-300 rounded-xl p-3.5 text-center">
        Tidak ada video tambahan. Jika membutuhkan lebih dari 1 video, klik tombol <strong>"Tambah Video Lain"</strong> di atas.
    </div>
</div>
