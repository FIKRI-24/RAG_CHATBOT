<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use App\Services\ExportActivityService;
use Illuminate\Http\Request;

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
        ]);

        // Modul yang memiliki kuis aktif/diterbitkan
        $modules = Module::available()
            ->whereHas('quiz', fn ($q) => $q->where('is_published', true))
            ->orderBy('kb_nomor')
            ->orderBy('id')
            ->get(['id', 'judul', 'kb_nomor', 'mapel']);

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

        // Metrik Statistik
        $totalStudents = User::where('role', 'siswa')->count();
        $participatingStudents = ModuleQuizAttempt::whereIn('module_id', $moduleIds)->distinct('user_id')->count('user_id');
        $averageScore = $moduleIds->isNotEmpty()
            ? round((float) (ModuleQuizAttempt::whereIn('module_id', $moduleIds)->avg('score') ?? 0), 1)
            : 0;
        $totalAttempts = ModuleQuizAttempt::whereIn('module_id', $moduleIds)->count();

        $metrics = [
            'total_students' => $totalStudents,
            'participating_students' => $participatingStudents,
            'average_score' => $averageScore,
            'total_attempts' => $totalAttempts,
            'total_quizzes' => $modules->count(),
        ];

        return view('guru.quiz-recap.index', compact('modules', 'students', 'classList', 'metrics'));
    }

    /**
     * Tampilkan detail rincian jawaban satu attempt siswa kepada guru.
     */
    public function showAttempt(ModuleQuizAttempt $attempt)
    {
        $attempt->load(['user', 'module']);

        return view('guru.quiz-recap.attempt-detail', compact('attempt'));
    }

    /**
     * Unduh file Excel Rekapitulasi Nilai Kuis Siswa.
     */
    public function export(Request $request, ExportActivityService $exportService)
    {
        $request->validate([
            'search' => 'nullable|string|max:255',
            'class_name' => 'nullable|string|max:100',
        ]);

        return $exportService->exportQuizRecap($request->class_name, $request->search);
    }
}
