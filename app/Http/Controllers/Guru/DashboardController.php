<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Services\LearningMetricsService;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Tampilkan halaman Dashboard Guru dengan data analitik real-time.
     */
    public function index(): View
    {
        $guruId = auth()->id();
        if (! app()->runningUnitTests()) {
            Module::ensureKb4Module($guruId);
        }
        $year = (int) now(config('app.display_timezone'))->year;

        // 1. KPI Metrik Utama
        $totalModul = Module::where('guru_id', $guruId)->count();
        $totalModulAktif = Module::where('guru_id', $guruId)->available()->count();

        $totalChunk = ModuleChunk::whereHas('module', function ($q) use ($guruId) {
            $q->where('guru_id', $guruId);
        })->count();

        $metrics = app(LearningMetricsService::class)->summary();
        $totalSiswa = $metrics['students'];
        $totalChat = $metrics['interactions'];
        $totalKuis = $metrics['quizzes'];
        $totalPertanyaanBiasa = $metrics['questions'];

        // 2. Metrik Kesiapan & Partisipasi Nyata
        $modulCompleted = Module::where('guru_id', $guruId)->where('status_indexing', 'completed')->count();
        $modulSiapPersen = $totalModul > 0 ? (int) round(($modulCompleted / $totalModul) * 100) : 100;

        $siswaAktifCount = $metrics['active_students'];
        $siswaAktifPersen = $metrics['active_percent'];
        $avgChatPerSiswa = $metrics['questions_per_student'];

        $activeAiModel = config('gemini.model', 'gemini-2.5-flash');

        // 3. Data Grafik Batang Bulanan (Jan - Des Tahun Ini)
        $chartChats = $this->monthlyCounts(ChatHistory::questions(), $year);
        $chartModules = $this->monthlyCounts(Module::where('guru_id', $guruId), $year);

        // 4. Live Feed: Aktivitas Tanya Jawab Siswa Terkini
        $recentChats = ChatHistory::with(['siswa', 'referensiChunk:id,module_id', 'referensiChunk.module:id,judul,mapel,kb_nomor'])
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

    private function monthlyCounts(Builder $query, int $year): array
    {
        $start = CarbonImmutable::create($year, 1, 1, 0, 0, 0, config('app.display_timezone'));
        for ($month = 0; $month < 12; $month++) {
            $query->selectRaw('COALESCE(SUM(CASE WHEN created_at >= ? AND created_at < ? THEN 1 ELSE 0 END), 0) AS month_'.$month, [
                $start->addMonths($month)->utc()->format('Y-m-d H:i:s'),
                $start->addMonths($month + 1)->utc()->format('Y-m-d H:i:s'),
            ]);
        }
        $row = $query->where('created_at', '>=', $start->utc())
            ->where('created_at', '<', $start->addYear()->utc())->first();

        return array_map(fn ($month) => (int) $row->{'month_'.$month}, range(0, 11));
    }
}
