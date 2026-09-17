@php
    /** @var \App\Models\Module|null $module */
    $media = old('additional_videos', isset($module) ? array_map(fn ($video) => ['title' => $video['title'], 'url' => $video['url']], $module->additionalVideos()) : []);
@endphp
<fieldset class="bg-white border rounded-2xl p-5 my-4" x-data="{ videos: {{ Illuminate\Support\Js::from($media) }} }">
    <legend class="font-semibold text-sm">Video tambahan untuk modul ini</legend>
    <input type="hidden" name="media_present" value="1">
    <p class="text-xs text-slate-500 mb-3">Maksimal 10 tautan HTTP/HTTPS. Hapus baris untuk melepas tautan.</p>
    <template x-for="(video, index) in videos" :key="index">
        <div class="flex flex-wrap gap-2 my-2">
            <label class="flex-1">Judul <input x-model="video.title" :name="`additional_videos[${index}][title]`" maxlength="100" required class="w-full rounded-xl"></label>
            <label class="flex-1">URL <input x-model="video.url" :name="`additional_videos[${index}][url]`" maxlength="255" type="url" required class="w-full rounded-xl"></label>
            <button type="button" @click="videos.splice(index, 1)" class="text-rose-700 px-2">Hapus tautan</button>
        </div>
    </template>
    <button type="button" @click="videos.push({title: '', url: ''})" :disabled="videos.length >= 10" class="px-3 py-2 bg-slate-100 rounded-xl text-sm">Tambah tautan video</button>
</fieldset>
