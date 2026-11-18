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

        if ($existingAttempt && ! $existingAttempt->hasApprovedRemedial()) {
            return redirect()->route('siswa.quiz-attempts.show', $existingAttempt)
                ->with('info', 'Anda telah menyelesaikan kuis ini. Kuis hanya dapat dikerjakan satu kali.');
        }

        $questions = $quiz->studentQuestions();
        $attempts = collect();
        $remedialAttempt = ($existingAttempt && $existingAttempt->hasApprovedRemedial()) ? $existingAttempt : null;

        return view('siswa.modules.quiz', compact('module', 'quiz', 'questions', 'attempts', 'remedialAttempt'));
    }

    public function submit(Request $request, Module $module)
    {
        $existing = ModuleQuizAttempt::where('module_id', $module->id)
            ->where('user_id', auth()->id())
            ->latest()
            ->first();

        if ($existing && ! $existing->hasApprovedRemedial()) {
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

            if ($doubleCheck && ! $doubleCheck->hasApprovedRemedial()) {
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
            $newScore = (int) round(100 * $correct / count($keys));

            if ($doubleCheck && $doubleCheck->hasApprovedRemedial()) {
                $history = $doubleCheck->historyEntries();
                $history[] = $doubleCheck->toHistoryEntry('retake', auth()->user(), $doubleCheck->remedial_note);

                $doubleCheck->update([
                    'module_quiz_id' => $quiz->id,
                    'module_title' => $current->judul,
                    'quiz_title' => $quiz->title,
                    'quiz_version' => $quiz->version,
                    'answers' => $answers,
                    'questions_snapshot' => $quiz->questions,
                    'correct_count' => $correct,
                    'question_count' => count($keys),
                    'score' => $newScore,
                    'attempt_number' => (int) $doubleCheck->attempt_number + 1,
                    'previous_attempts' => $history,
                    'remedial_approved_at' => null,
                    'remedial_approved_by' => null,
                    'adjusted_score_by' => null,
                    'adjusted_at' => null,
                ]);
                app(AuditService::class)->record('module.quiz.remedial_submitted', $doubleCheck, [
                    'attempt_number' => $doubleCheck->attempt_number,
                    'score' => $doubleCheck->score,
                ]);

                return $doubleCheck;
            }

            $attempt = ModuleQuizAttempt::create([
                'module_quiz_id' => $quiz->id, 'module_id' => $current->id, 'user_id' => auth()->id(),
                'module_title' => $current->judul, 'quiz_title' => $quiz->title, 'quiz_version' => $quiz->version,
                'answers' => $answers, 'questions_snapshot' => $quiz->questions, 'correct_count' => $correct,
                'question_count' => count($keys), 'score' => $newScore, 'attempt_number' => 1,
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
