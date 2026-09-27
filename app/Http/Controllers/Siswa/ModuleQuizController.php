<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ModuleQuizController extends Controller
{
    public function show(Module $module)
    {
        abort_unless(Module::available()->whereKey($module->id)->exists(), 403, 'Modul belum tersedia atau sudah kedaluwarsa.');
        $quiz = $module->quiz;
        abort_unless($quiz && $quiz->is_published, 404, 'Kuis belum diterbitkan.');

        $existingAttempt = ModuleQuizAttempt::where('module_id', $module->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($existingAttempt) {
            return redirect()->route('siswa.quiz-attempts.show', $existingAttempt)
                ->with('info', 'Anda telah menyelesaikan kuis ini. Kuis hanya dapat dikerjakan satu kali.');
        }

        $questions = $quiz->studentQuestions();
        $attempts = collect();

        return view('siswa.modules.quiz', compact('module', 'quiz', 'questions', 'attempts'));
    }

    public function submit(Request $request, Module $module)
    {
        $existing = ModuleQuizAttempt::where('module_id', $module->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($existing) {
            return redirect()->route('siswa.quiz-attempts.show', $existing)
                ->with('warning', 'Anda sudah pernah mengerjakan kuis ini. Kuis hanya dapat dikerjakan satu kali.');
        }

        $identity = $request->validate(['quiz_id' => 'required|integer|min:1', 'quiz_version' => 'required|integer|min:1']);
        $attempt = DB::transaction(function () use ($request, $module, $identity) {
            $current = Module::whereKey($module->id)->lockForUpdate()->firstOrFail();
            abort_unless(Module::available()->whereKey($current->id)->exists(), 403, 'Modul belum tersedia atau sudah kedaluwarsa.');
            $quiz = $current->quiz()->lockForUpdate()->first();
            abort_unless($quiz && $quiz->is_published, 404, 'Kuis belum diterbitkan.');

            $doubleCheck = ModuleQuizAttempt::where('module_id', $current->id)
                ->where('user_id', auth()->id())
                ->lockForUpdate()
                ->first();

            if ($doubleCheck) {
                return $doubleCheck;
            }

            if ((int) $identity['quiz_id'] !== (int) $quiz->id || (int) $identity['quiz_version'] !== $quiz->version) {
                throw ValidationException::withMessages(['quiz_version' => 'Kuis telah diperbarui guru. Muat ulang halaman dan kerjakan versi terbaru.']);
            }
            $keys = array_keys($quiz->questions);
            $rules = ['answers' => ['required', 'array:'.implode(',', $keys), 'size:'.count($keys)]];
            foreach ($keys as $key) {
                $rules['answers.'.$key] = ['required', Rule::in(['A', 'B', 'C', 'D'])];
            }
            $answers = $request->validate($rules)['answers'];
            $correct = 0;
            foreach ($quiz->questions as $index => $question) {
                $correct += $answers[$index] === $question['correct_answer'] ? 1 : 0;
            }
            $attempt = ModuleQuizAttempt::create([
                'module_quiz_id' => $quiz->id, 'module_id' => $current->id, 'user_id' => auth()->id(),
                'module_title' => $current->judul, 'quiz_title' => $quiz->title, 'quiz_version' => $quiz->version,
                'answers' => $answers, 'questions_snapshot' => $quiz->questions, 'correct_count' => $correct,
                'question_count' => count($keys), 'score' => (int) round(100 * $correct / count($keys)),
            ]);
            app(AuditService::class)->record('module.quiz.submitted', $attempt, ['score' => $attempt->score]);

            return $attempt;
        });

        return redirect()->route('siswa.quiz-attempts.show', $attempt);
    }

    public function result(ModuleQuizAttempt $attempt)
    {
        abort_unless((int) $attempt->user_id === (int) auth()->id(), 403);

        return view('siswa.modules.quiz-result', compact('attempt'));
    }
}
