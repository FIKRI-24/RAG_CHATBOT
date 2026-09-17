<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Module;
use Illuminate\Http\Request;

class ModuleController extends Controller
{
    /**
     * Tampilkan katalog E-Modul dan Kegiatan Belajar (KB) untuk Siswa.
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'mapel' => 'nullable|string|max:255',
            'kb_nomor' => 'nullable|string|max:255',
        ]);

        $query = Module::available();

        if ($request->filled('mapel') && $request->mapel !== 'Semua') {
            $query->where('mapel', $request->mapel);
        }

        if ($request->filled('kb_nomor') && $request->kb_nomor !== 'Semua') {
            $query->where('kb_nomor', $request->kb_nomor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('tp', 'like', "%{$search}%")
                    ->orWhere('mapel', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('mapel')->orderBy('kb_nomor')->paginate(9)->withQueryString();

        $mapelList = Module::available()
            ->select('mapel')
            ->distinct()
            ->pluck('mapel');

        return view('siswa.modules.index', compact('modules', 'mapelList'));
    }

    /**
     * Tampilkan detail Kegiatan Belajar (TP, Video, Kuis, Download).
     */
    public function show(Module $module)
    {
        if (! Module::available()->whereKey($module->id)->exists()) {
            return redirect()->route('siswa.modules.index')->with('error', 'Materi modul ini belum tersedia atau sudah melewati masa berlaku.');
        }

        $module->load(['quiz' => fn ($query) => $query->select('id', 'module_id', 'title', 'is_published')]);

        return view('siswa.modules.show', compact('module'));
    }
}
