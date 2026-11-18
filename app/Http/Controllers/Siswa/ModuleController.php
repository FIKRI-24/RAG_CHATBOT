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
            'topik' => 'nullable|string|max:255',
            'kb_nomor' => 'nullable|string|max:255',
        ]);

        $query = Module::available();

        if ($request->filled('mapel') && $request->mapel !== 'Semua') {
            $query->where('mapel', $request->mapel);
        }

        if ($request->filled('topik') && $request->topik !== 'Semua') {
            $query->where('topik', $request->topik);
        }

        if ($request->filled('kb_nomor') && $request->kb_nomor !== 'Semua') {
            $query->where('kb_nomor', $request->kb_nomor);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                    ->orWhere('tp', 'like', "%{$search}%")
                    ->orWhere('topik', 'like', "%{$search}%")
                    ->orWhere('mapel', 'like', "%{$search}%");
            });
        }

        $modules = $query->orderBy('mapel')->orderBy('topik')->orderBy('kb_nomor')->paginate(9)->withQueryString();

        $mapelList = Module::mergeWithCurriculumElements(
            Module::available()->select('mapel')->distinct()->pluck('mapel')
        );

        $defaultTopics = ($request->filled('mapel') && $request->mapel !== 'Semua')
            ? (Module::defaultTopics()[$request->mapel] ?? [])
            : collect(Module::defaultTopics())->flatten()->all();

        $dbTopics = Module::available()
            ->when($request->filled('mapel') && $request->mapel !== 'Semua', fn ($q) => $q->where('mapel', $request->mapel))
            ->whereNotNull('topik')
            ->where('topik', '!=', '')
            ->select('topik')
            ->distinct()
            ->orderBy('topik')
            ->pluck('topik');

        $topikList = collect($defaultTopics)->merge($dbTopics)->unique()->values();

        $kbList = Module::mergeWithDefaultKbOptions(
            Module::available()->select('kb_nomor')->distinct()->pluck('kb_nomor')
        );

        return view('siswa.modules.index', compact('modules', 'mapelList', 'topikList', 'kbList'));
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
        $userAttempt = \App\Models\ModuleQuizAttempt::where('module_id', $module->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        return view('siswa.modules.show', compact('module', 'userAttempt'));
    }
}
