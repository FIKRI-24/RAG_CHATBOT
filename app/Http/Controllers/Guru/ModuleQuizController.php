<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuleQuizController extends Controller
{
    private function authorizeModule(Module $module): void
    {
        abort_unless((int) $module->guru_id === (int) auth()->id(), 403);
    }

    public function edit(Module $module)
    {
        $this->authorizeModule($module);

        return view('guru.modules.quiz', ['module' => $module, 'quiz' => $module->quiz]);
    }

    public function update(Request $request, Module $module)
    {
        $this->authorizeModule($module);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'is_published' => 'sometimes|boolean',
            'questions' => 'required|array|min:1|max:50',
            'questions.*' => 'required|array:text,options,correct_answer',
            'questions.*.text' => 'required|string|max:2000',
            'questions.*.options' => 'required|array:A,B,C,D|size:4',
            'questions.*.options.A' => 'required|string|max:500',
            'questions.*.options.B' => 'required|string|max:500',
            'questions.*.options.C' => 'required|string|max:500',
            'questions.*.options.D' => 'required|string|max:500',
            'questions.*.correct_answer' => 'required|in:A,B,C,D',
        ]);
        $data['questions'] = array_values($data['questions']);
        $data['is_published'] = $request->boolean('is_published');

        DB::transaction(function () use ($module, $data) {
            $current = Module::whereKey($module->id)->lockForUpdate()->firstOrFail();
            $this->authorizeModule($current);
            $quiz = $current->quiz()->lockForUpdate()->first();
            $data['version'] = $quiz ? $quiz->version + 1 : 1;
            if ($quiz) {
                $quiz->update($data);
            } else {
                $quiz = $current->quiz()->create($data);
            }
            app(AuditService::class)->record('module.quiz.saved', $quiz, ['published' => $data['is_published'], 'question_count' => count($data['questions'])]);
        });

        return back()->with('success', $data['is_published'] ? 'Kuis diterbitkan. Siswa dapat mengerjakannya saat modul tersedia.' : 'Kuis disimpan sebagai draf.');
    }

    public function destroy(Module $module)
    {
        $this->authorizeModule($module);
        DB::transaction(function () use ($module) {
            $current = Module::whereKey($module->id)->lockForUpdate()->firstOrFail();
            $this->authorizeModule($current);
            if ($quiz = $current->quiz()->lockForUpdate()->first()) {
                $quiz->delete();
                app(AuditService::class)->record('module.quiz.deleted', $quiz);
            }
        });

        return back()->with('success', 'Kuis dihapus. Hasil pengerjaan sebelumnya tetap tersimpan.');
    }

    public function results(Module $module)
    {
        $this->authorizeModule($module);
        $attempts = ModuleQuizAttempt::where('module_id', $module->id)->with('user')->latest()->paginate(20);

        return view('guru.modules.quiz-results', compact('module', 'attempts'));
    }
}
