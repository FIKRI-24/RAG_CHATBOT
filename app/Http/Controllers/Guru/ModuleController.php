<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ProcessModuleJob; 
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ModuleController extends Controller
{
    public function index(Request $request)
    {
        $query = Module::where('guru_id', Auth::id());
        
        if ($request->filled('mapel')) {
            $query->where('mapel', $request->mapel);
        }

        if ($request->filled('kb_nomor')) {
            $query->where('kb_nomor', $request->kb_nomor);
        }

        $modules = $query->latest()->paginate(10);
        
        $mapelList = Module::where('guru_id', Auth::id())
            ->select('mapel')
            ->distinct()
            ->pluck('mapel');

        return view('guru.modules.index', compact('modules', 'mapelList'));
    }

    public function create()
    {
        return view('guru.modules.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'kb_nomor' => 'required|string|in:KB 1,KB 2,KB 3',
            'tp' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'kuis_url' => 'nullable|url|max:500',
            'file' => 'required|file|mimes:pdf,docx|max:10240',
            'berlaku_sampai' => 'nullable|date',
        ], [
            'judul.required' => 'Judul modul / materi wajib diisi.',
            'mapel.required' => 'Mata pelajaran wajib diisi.',
            'kb_nomor.required' => 'Pilihan Kegiatan Belajar (KB 1, 2, atau 3) wajib ditentukan.',
            'file.required' => 'File dokumen materi wajib diunggah.',
            'file.mimes' => 'Format file materi hanya boleh PDF atau DOCX.',
            'file.max' => 'Ukuran file materi maksimal 10 MB.',
            'video_url.url' => 'Link video harus berupa format URL yang valid (misal: https://youtube.com/...).',
            'kuis_url.url' => 'Link kuis harus berupa format URL yang valid.',
        ]);

        $filePath = $request->file('file')->store('modules', 'local');

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
            'berlaku_sampai' => $request->berlaku_sampai,
        ]);

        if (class_exists(ProcessModuleJob::class)) {
            dispatch(new ProcessModuleJob($module));
        }

        return redirect()->route('guru.modules.index')->with('success', 'Modul ' . $module->kb_nomor . ' berhasil diupload dan sedang diproses AI.');
    }

    public function show(Module $module)
    {
        if ($module->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $module->load('chunks');

        return view('guru.modules.show', compact('module'));
    }

    public function edit(Module $module)
    {
        if ($module->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        return view('guru.modules.edit', compact('module'));
    }

    public function update(Request $request, Module $module)
    {
        if ($module->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'judul' => 'required|string|max:255',
            'mapel' => 'required|string|max:255',
            'kb_nomor' => 'required|string|in:KB 1,KB 2,KB 3',
            'tp' => 'nullable|string',
            'video_url' => 'nullable|url|max:500',
            'kuis_url' => 'nullable|url|max:500',
            'file' => 'nullable|file|mimes:pdf,docx|max:10240',
            'berlaku_sampai' => 'nullable|date',
        ]);

        $module->judul = $request->judul;
        $module->mapel = $request->mapel;
        $module->kb_nomor = $request->kb_nomor;
        $module->tp = $request->tp;
        $module->video_url = $request->video_url;
        $module->kuis_url = $request->kuis_url;
        $module->berlaku_sampai = $request->berlaku_sampai;

        if ($request->hasFile('file')) {
            // Hapus file lama jika ada
            if (Storage::disk('local')->exists($module->file_path)) {
                Storage::disk('local')->delete($module->file_path);
            }

            // Simpan file baru
            $newPath = $request->file('file')->store('modules', 'local');
            $module->file_path = $newPath;
            $module->status_indexing = 'pending';

            // Hapus chunk lama
            $module->chunks()->delete();

            // Index ulang di antrian
            if (class_exists(ProcessModuleJob::class)) {
                dispatch(new ProcessModuleJob($module));
            }
        }

        $module->save();

        return redirect()->route('guru.modules.index')->with('success', 'Data modul berhasil diperbarui.');
    }

    public function destroy(Module $module)
    {
        try {
            if ($module->guru_id !== Auth::id()) {
                abort(403, 'Akses ditolak.');
            }

            if (!empty($module->file_path) && Storage::disk('local')->exists($module->file_path)) {
                Storage::disk('local')->delete($module->file_path);
            }

            // Hapus chunk terlebih dahulu jika ada
            $module->chunks()->delete();

            $module->delete();

            return redirect()->route('guru.modules.index')->with('success', 'Modul berhasil dihapus.');
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Gagal menghapus modul ID {$module->id}: " . $e->getMessage());
            return redirect()->route('guru.modules.index')->with('error', 'Gagal menghapus modul: ' . $e->getMessage());
        }
    }

    public function reindex(Module $module)
    {
        if ($module->guru_id !== Auth::id()) {
            abort(403, 'Akses ditolak.');
        }

        $module->chunks()->delete();
        $module->update(['status_indexing' => 'pending']);

        if (class_exists(ProcessModuleJob::class)) {
            dispatch(new ProcessModuleJob($module));
        }

        return redirect()->back()->with('success', 'Proses indexing ulang AI telah dijadwalkan.');
    }

    public function download(Module $module)
    {
        // Izinkan guru pemilik atau siswa yang aktif
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->isGuru() && $module->guru_id !== $user->id) {
            abort(403, 'Akses ditolak.');
        }

        if (!Storage::disk('local')->exists($module->file_path)) {
            return redirect()->back()->with('error', 'File materi tidak ditemukan di penyimpanan server.');
        }

        $extension = pathinfo($module->file_path, PATHINFO_EXTENSION);
        $cleanName = Str::slug($module->mapel . '-' . $module->kb_nomor . '-' . $module->judul) . '.' . $extension;

        return Storage::disk('local')->download($module->file_path, $cleanName);
    }
}
