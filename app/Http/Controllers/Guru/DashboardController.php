<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Guru dengan data analitik real-time.
     */
    public function index(): View
    {
        $guruId = auth()->id();
        $year = (int) date('Y');

        // 1. KPI Metrik Utama
        $totalModul = Module::where('guru_id', $guruId)->count();
        $totalModulAktif = Module::where('guru_id', $guruId)
            ->where(function ($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })
            ->count();

        $totalChunk = ModuleChunk::whereHas('module', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $totalSiswa = User::where('role', 'siswa')->count();
        $totalChat = ChatHistory::count();
        $totalKuis = ChatHistory::where('pertanyaan', '[LATIHAN_SOAL]')->count();
        $totalPertanyaanBiasa = $totalChat - $totalKuis;

        // 2. Metrik Kesiapan & Partisipasi Nyata
        $modulCompleted = Module::where('guru_id', $guruId)->where('status_indexing', 'completed')->count();
        $modulSiapPersen = $totalModul > 0 ? (int) round(($modulCompleted / $totalModul) * 100) : 100;

        $siswaAktifCount = ChatHistory::distinct('siswa_id')->count('siswa_id');
        $siswaAktifPersen = $totalSiswa > 0 ? (int) round(($siswaAktifCount / $totalSiswa) * 100) : 0;
        $avgChatPerSiswa = $totalSiswa > 0 ? round($totalChat / $totalSiswa, 1) : 0;

        $activeAiModel = config('gemini.model', 'gemini-2.5-flash');

        // 3. Data Grafik Batang Bulanan (Jan - Des Tahun Ini)
        $chatCountsRaw = ChatHistory::selectRaw("EXTRACT(MONTH FROM created_at) as month, count(*) as count")
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $moduleCountsRaw = Module::where('guru_id', $guruId)
            ->selectRaw("EXTRACT(MONTH FROM created_at) as month, count(*) as count")
            ->whereYear('created_at', $year)
            ->groupBy('month')
            ->pluck('count', 'month')
            ->toArray();

        $chartChats = [];
        $chartModules = [];
        for ($m = 1; $m <= 12; $m++) {
            $chartChats[] = (int) ($chatCountsRaw[$m] ?? 0);
            $chartModules[] = (int) ($moduleCountsRaw[$m] ?? 0);
        }

        // 4. Live Feed: Aktivitas Tanya Jawab Siswa Terkini
        $recentChats = ChatHistory::with(['siswa', 'referensiChunk.module'])
            ->latest()
            ->take(6)
            ->get();

        // 5. Daftar Modul Terkini
        $latestModules = Module::where('guru_id', $guruId)
            ->withCount('chunks')
            ->latest()
            ->take(4)
            ->get();

        return view('guru.dashboard', compact(
            'totalModul',
            'totalModulAktif',
            'totalChunk',
            'totalSiswa',
            'totalChat',
            'totalKuis',
            'totalPertanyaanBiasa',
            'modulSiapPersen',
            'siswaAktifCount',
            'siswaAktifPersen',
            'avgChatPerSiswa',
            'activeAiModel',
            'year',
            'chartChats',
            'chartModules',
            'recentChats',
            'latestModules'
        ));
    }
}
