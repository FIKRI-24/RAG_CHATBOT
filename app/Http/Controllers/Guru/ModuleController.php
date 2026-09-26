<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessModuleJob;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Services\AuditService;
use App\Services\RetrievalService;
use App\Services\StoredFileService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'mapel' => 'nullable|string|max:255',
            'kb_nomor' => 'nullable|string|max:255',
        ]);

        $query = Module::where('guru_id', Auth::id());

        if ($request->filled('mapel')) {
            $query->where('mapel', $request->mapel);
        }

        if ($request->filled('kb_nomor')) {
            $query->where('kb_nomor', $request->kb_nomor);
        }

        $modules = $query->latest()->paginate(10)->withQueryString();

        $ownedModules = Module::where('guru_id', Auth::id());
        $mapelList = (clone $ownedModules)
            ->select('mapel')
            ->distinct()
            ->pluck('mapel');
        $kbList = $ownedModules->select('kb_nomor')->distinct()->orderBy('kb_nomor')->pluck('kb_nomor');

        return view('guru.modules.index', compact('modules', 'mapelList', 'kbList'));
    }

    public function create()
    {
        return view('guru.modules.create');
    }

    public function store(Request $request)
    {
        if ($request->boolean('media_present') && ! $request->has('additional_videos')) {
            $request->merge(['additional_videos' => []]);
        }
        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'kb_nomor' => 'required|string|max:255',
            'tp' => 'nullable|string|max:20000',
            'video_url' => 'nullable|url:http,https|max:255',
            'kuis_url' => 'nullable|url:http,https|max:255',
            'file' => 'required|file|mimes:pdf,docx|max:10240',
            'berlaku_sampai' => 'nullable|date',
            'additional_videos' => 'nullable|array|max:10',
            'additional_videos.*' => 'array:title,url',
            'additional_videos.*.title' => 'required|string|max:100',
            'additional_videos.*.url' => 'required|url:http,https|max:255',
        ], [
            'judul.required' => 'Judul modul / materi wajib diisi.',
            'mapel.required' => 'Mata pelajaran wajib diisi.',
            'kb_nomor.required' => 'Nama atau nomor Kegiatan Belajar (KB) wajib ditentukan.',
            'file.required' => 'File dokumen materi wajib diunggah.',
            'file.mimes' => 'Format file materi hanya boleh PDF atau DOCX.',
            'file.max' => 'Ukuran file materi maksimal 10 MB.',
            'video_url.url' => 'Link video harus berupa format URL yang valid (misal: https://youtube.com/...).',
            'kuis_url.url' => 'Link kuis harus berupa format URL yang valid.',
        ]);

        $files = app(StoredFileService::class);
        $filePath = $files->store($request->file('file'), 'modules', 'local', 'file');

        try {

            $module = DB::transaction(function () use ($request, $filePath) {
                $module = Module::create([
                    'guru_id' => Auth::id(),
                    'judul' => $request->judul,
                    'mapel' => $request->mapel,
                    'kb_nomor' => $request->kb_nomor,
                    'tp' => $request->tp,
                    'video_url' => $request->video_url,
                    'kuis_url' => $request->kuis_url,
                    'file_path' => $filePath,
                    'status_indexing' => 'pending',
                    'indexing_version' => (string) Str::uuid(),
                    'berlaku_sampai' => $request->berlaku_sampai,
                    'additional_videos' => $request->input('additional_videos'),
                ]);
                app(AuditService::class)->record('module.uploaded', $module);

                return $module;
            });

        } catch (\Throwable $e) {
            $files->delete($filePath, 'local');
            Log::warning('Module save failed', ['error_type' => get_class($e)]);

            return back()->withInput()->withErrors(['file' => 'Modul belum dapat disimpan. Silakan coba lagi.']);
        }
        if (! $this->schedule($module)) {
            return redirect()->route('guru.modules.index')->with('error', 'Modul tersimpan, tetapi antrean AI belum tersedia. Jalankan indexing ulang setelah antrean diperbaiki.');
        }

        return redirect()->route('guru.modules.index')->with('success', 'Modul '.$module->kb_nomor.' berhasil diupload dan sedang diproses AI.');
    }

    public function show(Module $module)
    {
        if ((int) $module->guru_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $chunks = $module->chunks()->select(['id', 'module_id', 'chunk_text', 'chunk_index', 'embedding_model', 'embedding_dimensions'])
            ->orderBy('chunk_index')->orderBy('id')->paginate(20);
        $chunkCount = $module->chunks()->count();

        return view('guru.modules.show', compact('module', 'chunks', 'chunkCount'));
    }

    public function edit(Module $module)
    {
        if ((int) $module->guru_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        return view('guru.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        if ($request->boolean('media_present') && ! $request->has('additional_videos')) {
            $request->merge(['additional_videos' => []]);
        }
        if ((int) $module->guru_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'kb_nomor' => 'required|string|max:255',
            'tp' => 'nullable|string|max:20000',
            'video_url' => 'nullable|url:http,https|max:255',
            'kuis_url' => 'nullable|url:http,https|max:255',
            'file' => 'nullable|file|mimes:pdf,docx|max:10240',
            'berlaku_sampai' => 'nullable|date',
            'additional_videos' => 'nullable|array|max:10',
            'additional_videos.*' => 'array:title,url',
            'additional_videos.*.title' => 'required|string|max:100',
            'additional_videos.*.url' => 'required|url:http,https|max:255',
        ]);

        $files = app(StoredFileService::class);
        $newPath = $request->hasFile('file')
            ? $files->store($request->file('file'), 'modules', 'local', 'file') : null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($request, &$module, $newPath, &$oldPath) {
                $module = Module::whereKey($module->id)->where('guru_id', Auth::id())->lockForUpdate()->firstOrFail();
                $module->fill($request->only(['judul', 'mapel', 'kb_nomor', 'tp', 'video_url', 'kuis_url', 'berlaku_sampai', 'additional_videos']));
                if ($newPath) {
                    $oldPath = $module->file_path;
                    $module->fill(['file_path' => $newPath, 'status_indexing' => 'pending',
                        'indexing_version' => (string) Str::uuid(), 'indexing_error' => null]);
                }
                $module->save();
                app(AuditService::class)->record('module.updated', $module, ['file_replaced' => (bool) $newPath]);
            });
        } catch (\Throwable $e) {
            $files->delete($newPath, 'local');
            Log::warning('Module update failed', ['error_type' => get_class($e)]);

            return back()->withInput()->withErrors(['file' => 'Perubahan belum dapat disimpan. Dokumen sebelumnya tetap tersedia.']);
        }
        if ($newPath) {
            $files->delete($oldPath, 'local');
            if (! $this->schedule($module)) {
                return back()->with('error', 'Dokumen tersimpan, tetapi antrean AI belum tersedia. Silakan jalankan indexing ulang.');
            }
        }

        return redirect()->route('guru.modules.index')->with('success', 'Data modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        abort_unless((int) $module->guru_id === (int) Auth::id(), 403, 'Akses ditolak.');
        $path = null;
        try {
            DB::transaction(function () use ($module, &$path) {
                $current = Module::whereKey($module->id)->where('guru_id', Auth::id())->lockForUpdate()->firstOrFail();
                $path = $current->file_path;
                foreach ($current->chunks()->with('module')->lazyById(100) as $chunk) {
                    ChatHistory::where('referensi_chunk_id', $chunk->id)->whereNull('sources')
                        ->update(['sources' => json_encode([app(RetrievalService::class)->source($chunk)], JSON_UNESCAPED_UNICODE)]);
                }
                $current->delete();
                app(AuditService::class)->record('module.deleted', $current);
            });
        } catch (\Throwable $e) {
            Log::warning('Module deletion failed', ['module_id' => $module->id, 'error_type' => get_class($e)]);

            return back()->with('error', 'Modul belum dapat dihapus. Silakan coba lagi.');
        }
        if (! app(StoredFileService::class)->delete($path, 'local')) {
            return redirect()->route('guru.modules.index')->with('error', 'Modul telah ditarik. Berkas lama perlu dibersihkan oleh pengelola penyimpanan.');
        }

        return redirect()->route('guru.modules.index')->with('success', 'Modul berhasil dihapus.');
    }

    public function reindex(Module $module)
    {
        if ((int) $module->guru_id !== (int) Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        DB::transaction(function () use (&$module) {
            $module = Module::whereKey($module->id)->where('guru_id', Auth::id())->lockForUpdate()->firstOrFail();
            $module->update([
                'status_indexing' => 'pending',
                'indexing_version' => (string) Str::uuid(),
                'indexing_error' => null,
            ]);
            app(AuditService::class)->record('module.reindex_requested', $module);
        });

        if (! $this->schedule($module)) {
            return back()->with('error', 'Antrean AI belum tersedia. Silakan coba indexing ulang beberapa saat lagi.');
        }

        return redirect()->back()->with('success', 'Proses indexing ulang AI telah dijadwalkan.');
    }

    public function download(Module $module)
    {
        // Izinkan guru pemilik atau siswa yang aktif
        $user = Auth::user();
        if (! $user) {
            abort(401);
        }

        if ($user->isGuru() && (int) $module->guru_id !== (int) $user->id) {
            abort(403, 'Akses ditolak.');
        }

        abort_unless($user->isGuru() || ($user->isSiswa() && Module::available()->whereKey($module->id)->exists()), 403, 'Modul belum tersedia atau sudah kedaluwarsa.');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        if (! $disk->exists($module->file_path)) {
            return redirect()->back()->with('error', 'File materi tidak ditemukan di penyimpanan server.');
        }

        $extension = pathinfo($module->file_path, PATHINFO_EXTENSION);
        $cleanName = Str::slug($module->mapel.'-'.$module->kb_nomor.'-'.$module->judul).'.'.$extension;

        return $disk->download($module->file_path, $cleanName);
    }

    private function schedule(Module $module): bool
    {
        try {
            ProcessModuleJob::dispatch($module);

            return true;
        } catch (\Throwable $e) {
            Module::whereKey($module->id)->where('indexing_version', $module->indexing_version)
                ->where('status_indexing', 'pending')->update(['status_indexing' => 'failed',
                    'indexing_error' => 'Antrean AI belum tersedia. Silakan jalankan indexing ulang.']);
            Log::warning('Module dispatch failed', ['module_id' => $module->id, 'error_type' => get_class($e)]);

            return false;
        }
    }
}
