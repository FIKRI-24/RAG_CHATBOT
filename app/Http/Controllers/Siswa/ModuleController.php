<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Module;

class ModuleController extends Controller
{
    /**
     * Tampilkan katalog E-Modul dan Kegiatan Belajar (KB) untuk Siswa.
     */
    public function index(Request $request)
    {
        $query = Module::where('status_indexing', 'completed')
            ->where(function($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            });

        if ($request->filled('mapel') && $request->mapel !== 'Semua') {
            $query->where('mapel', $request->mapel);
        }

        if ($request->filled('kb_nomor') && $request->kb_nomor !== 'Semua') {
            $query->where('kb_nomor', $request->kb_nomor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhere('tp', 'like', "%{$search}%")
                  ->orWhere('mapel', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('mapel')->orderBy('kb_nomor')->paginate(9);

        $mapelList = Module::where('status_indexing', 'completed')
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
        if ($module->berlaku_sampai && \Carbon\Carbon::parse($module->berlaku_sampai)->isPast()) {
            return redirect()->route('siswa.modules.index')->with('error', 'Materi modul ini sudah melewati masa berlaku (diarsipkan).');
        }

        return view('siswa.modules.show', compact('module'));
    }
}
