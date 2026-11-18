<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\AuditService;
use App\Services\ExportActivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizRecapController extends Controller
{
    /**
     * Tampilkan rekapitulasi nilai kuis objektif seluruh siswa per Kegiatan Belajar (KB).
     */
    public function index(Request $request)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'class_name' => 'nullable|string|max:100',
            'mapel' => 'nullable|string|max:255',
            'topik' => 'nullable|string|max:255',
        ]);

        $guruId = (int) auth()->id();

        // Seluruh modul milik guru yang memiliki kuis terbit atau riwayat pengerjaan (termasuk arsip/re-index)
        $allRecapModules = Module::quizRecapModules($guruId);
        $mapelList = $allRecapModules->pluck('mapel')->filter()->unique()->values();
        $topikList = $allRecapModules
            ->when($request->filled('mapel'), fn ($c) => $c->where('mapel', $request->mapel))
            ->pluck('topik')->filter()->unique()->values();

        $modules = $allRecapModules
            ->when($request->filled('mapel'), fn ($c) => $c->where('mapel', $request->mapel))
            ->when($request->filled('topik'), fn ($c) => $c->where('topik', $request->topik))
            ->values();

        $moduleIds = $modules->pluck('id');

        $query = User::where('role', 'siswa');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('student_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('class_name')) {
            $query->where('class_name', $request->class_name);
        }

        $filteredStudentIds = (clone $query)->select('id');
        $attemptsForFiltered = ModuleQuizAttempt::whereIn('module_id', $moduleIds)
            ->whereIn('user_id', $filteredStudentIds);

        // Metrik statistik mengikuti filter kelas/pencarian/kurikulum dan modul milik guru
        $totalStudents = (clone $query)->count();
        $participatingStudents = $moduleIds->isNotEmpty()
            ? (clone $attemptsForFiltered)->distinct('user_id')->count('user_id')
            : 0;
        $averageScore = $moduleIds->isNotEmpty()
            ? round((float) ((clone $attemptsForFiltered)->avg('score') ?? 0), 1)
            : 0;
        $totalAttempts = $moduleIds->isNotEmpty()
            ? (clone $attemptsForFiltered)->count()
            : 0;

        $students = $query->with(['quizAttempts' => function ($q) use ($moduleIds) {
            $q->whereIn('module_id', $moduleIds);
        }])->orderBy('name')->paginate(20)->withQueryString();

        // Kelas unik untuk filter
        $classList = User::where('role', 'siswa')
            ->whereNotNull('class_name')
            ->where('class_name', '!=', '')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $metrics = [
            'total_students' => $totalStudents,
            'participating_students' => $participatingStudents,
            'average_score' => $averageScore,
            'total_attempts' => $totalAttempts,
            'total_quizzes' => $modules->count(),
        ];

        return view('guru.quiz-recap.index', compact('modules', 'students', 'classList', 'mapelList', 'topikList', 'metrics'));
    }

    /**
     * Tampilkan detail rincian jawaban satu attempt siswa kepada guru.
     */
    public function showAttempt(ModuleQuizAttempt $attempt)
    {
        $attempt->load(['user', 'module.quiz', 'approvedBy:id,name', 'adjustedBy:id,name']);
        $this->authorizeAttempt($attempt);

        return view('guru.quiz-recap.attempt-detail', compact('attempt'));
    }

    /**
     * Setujui izin ujian ulang (remedial) di sistem bagi siswa untuk kuis modul terkait.
     */
    public function approveRemedial(Request $request, ModuleQuizAttempt $attempt)
    {
        $attempt->load('module.quiz');
        $this->authorizeAttempt($attempt);

        $data = $request->validate([
            'remedial_note' => 'nullable|string|max:500',
        ]);

        if (! $attempt->module_id || ! $attempt->module?->quiz) {
            return back()->with('error', 'Kuis atau modul sudah tidak tersedia sehingga ujian ulang tidak dapat dibuka.');
        }

        $note = isset($data['remedial_note']) && trim($data['remedial_note']) !== ''
            ? trim($data['remedial_note'])
            : 'Disetujui guru untuk mengikuti ujian ulang remedial.';

        DB::transaction(function () use ($attempt, $note) {
            $locked = ModuleQuizAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $locked->update([
                'remedial_approved_at' => now(),
                'remedial_approved_by' => auth()->id(),
                'remedial_note' => $note,
            ]);
            app(AuditService::class)->record('module.quiz.remedial_approved', $locked, ['note' => $note]);
        });

        return back()->with('success', 'Izin ujian ulang remedial telah disetujui. Siswa kini dapat mengerjakan ulang kuis ini.');
    }

    /**
     * Batalkan izin ujian ulang remedial sebelum dikerjakan siswa.
     */
    public function revokeRemedial(ModuleQuizAttempt $attempt)
    {
        $attempt->load('module');
        $this->authorizeAttempt($attempt);

        DB::transaction(function () use ($attempt) {
            $locked = ModuleQuizAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $locked->update([
                'remedial_approved_at' => null,
                'remedial_approved_by' => null,
            ]);
            app(AuditService::class)->record('module.quiz.remedial_revoked', $locked);
        });

        return back()->with('success', 'Izin ujian ulang remedial telah dibatalkan.');
    }

    /**
     * Perbarui nilai remedial siswa secara langsung oleh guru (misal dari remedial praktik/lisan),
     * sembari menyimpan nilai sebelumnya ke dalam riwayat attempt.
     */
    public function updateRemedialScore(Request $request, ModuleQuizAttempt $attempt)
    {
        $attempt->load('module');
        $this->authorizeAttempt($attempt);

        $data = $request->validate([
            'score' => 'required|integer|min:0|max:100',
            'remedial_note' => 'nullable|string|max:500',
        ], [
            'score.required' => 'Nilai remedial wajib diisi.',
            'score.integer' => 'Nilai remedial harus berupa angka bulat 0 sampai 100.',
            'score.min' => 'Nilai remedial minimal 0.',
            'score.max' => 'Nilai remedial maksimal 100.',
        ]);

        $newScore = (int) $data['score'];
        $note = isset($data['remedial_note']) && trim($data['remedial_note']) !== ''
            ? trim($data['remedial_note'])
            : 'Penyesuaian nilai remedial oleh guru.';

        DB::transaction(function () use ($attempt, $newScore, $note) {
            $locked = ModuleQuizAttempt::whereKey($attempt->id)->lockForUpdate()->firstOrFail();
            $oldScore = (int) $locked->score;
            $history = $locked->historyEntries();
            $history[] = $locked->toHistoryEntry('manual_adjustment', auth()->user(), $note);

            $locked->update([
                'score' => $newScore,
                'previous_attempts' => $history,
                'remedial_note' => $note,
                'adjusted_score_by' => auth()->id(),
                'adjusted_at' => now(),
                'remedial_approved_at' => null,
                'remedial_approved_by' => null,
            ]);

            app(AuditService::class)->record('module.quiz.remedial_score_updated', $locked, [
                'old_score' => $oldScore,
                'new_score' => $newScore,
                'note' => $note,
            ]);
        });

        return back()->with('success', 'Nilai remedial siswa berhasil diperbarui. Riwayat nilai sebelumnya tetap tersimpan.');
    }

    /**
     * Unduh file Excel Rekapitulasi Nilai Kuis Siswa.
     */
    public function export(Request $request, ExportActivityService $exportService)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'class_name' => 'nullable|string|max:100',
            'mapel' => 'nullable|string|max:255',
            'topik' => 'nullable|string|max:255',
        ]);

        return $exportService->exportQuizRecap(
            $request->class_name,
            $request->search,
            (int) auth()->id(),
            $request->mapel,
            $request->topik
        );
    }

    private function authorizeAttempt(ModuleQuizAttempt $attempt): void
    {
        if ($attempt->module && (int) $attempt->module->guru_id !== (int) auth()->id()) {
            abort(403, 'Akses ditolak.');
        }
    }
}
