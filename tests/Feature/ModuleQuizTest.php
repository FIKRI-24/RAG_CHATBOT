<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleQuizAttempt;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleQuizTest extends TestCase
{
    use RefreshDatabase;

    private function module(User $guru, array $attributes = []): Module
    {
        return Module::create($attributes + ['guru_id' => $guru->id, 'judul' => 'Dasar Jaringan', 'mapel' => 'TKJ',
            'kb_nomor' => 'KB 1', 'file_path' => 'modules/test.pdf', 'status_indexing' => 'completed']);
    }

    private function data(array $attributes = []): array
    {
        return $attributes + ['title' => 'Kuis Pilihan Ganda', 'is_published' => true, 'questions' => [
            ['text' => 'Perangkat pusat koneksi nirkabel?', 'options' => ['A' => 'Access Point', 'B' => 'Monitor', 'C' => 'Keyboard', 'D' => 'Printer'], 'correct_answer' => 'A'],
            ['text' => 'Contoh jaringan personal?', 'options' => ['A' => 'Internet', 'B' => 'Bluetooth', 'C' => 'WAN', 'D' => 'Satelit'], 'correct_answer' => 'B'],
        ]];
    }

    public function test_teacher_can_manage_publish_and_delete_quiz(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $this->actingAs($guru)->get(route('guru.modules.index'))->assertSee('Kelola Kuis');
        $this->get(route('guru.modules.quiz.edit', $module))->assertOk()->assertSee('Kelola Kuis Objektif');
        $this->put(route('guru.modules.quiz.update', $module), $this->data())->assertSessionHasNoErrors();
        $quiz = $module->quiz()->firstOrFail();
        $this->assertTrue($quiz->is_published);
        $this->assertSame(1, $quiz->version);
        $this->put(route('guru.modules.quiz.update', $module), $this->data(['is_published' => false, 'title' => 'Draf Baru']))->assertSessionHasNoErrors();
        $this->assertFalse($quiz->fresh()->is_published);
        $this->assertSame(2, $quiz->fresh()->version);
        $this->delete(route('guru.modules.quiz.destroy', $module))->assertSessionHas('success');
        $this->assertDatabaseMissing('module_quizzes', ['id' => $quiz->id]);
        $this->assertDatabaseHas('audit_events', ['action' => 'module.quiz.deleted']);
    }

    public function test_student_and_other_teacher_cannot_manage_quiz_or_view_teacher_results(): void
    {
        $owner = User::factory()->create(['role' => 'guru']);
        $module = $this->module($owner);
        foreach (['siswa', 'guru'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $this->get(route('guru.modules.quiz.edit', $module))->assertForbidden();
            $this->put(route('guru.modules.quiz.update', $module), $this->data())->assertForbidden();
            $this->delete(route('guru.modules.quiz.destroy', $module))->assertForbidden();
            $this->get(route('guru.modules.quiz.results', $module))->assertForbidden();
        }
    }

    public function test_invalid_question_key_missing_option_and_excess_questions_are_rejected(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $data = $this->data();
        $data['questions'][0]['correct_answer'] = 'E';
        unset($data['questions'][1]['options']['D']);
        $this->actingAs($guru)->put(route('guru.modules.quiz.update', $module), $data)
            ->assertSessionHasErrors(['questions.0.correct_answer', 'questions.1.options.D']);
        $data = $this->data();
        $data['questions'] = array_fill(0, 51, $data['questions'][0]);
        $this->put(route('guru.modules.quiz.update', $module), $data)->assertSessionHasErrors('questions');
        $this->assertDatabaseCount('module_quizzes', 0);
    }

    public function test_student_form_does_not_expose_answer_keys_and_module_links_to_quiz(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $quiz = $module->quiz()->create($this->data());
        $this->actingAs(User::factory()->create(['role' => 'siswa']));
        $this->get(route('siswa.modules.show', $module))->assertOk()->assertSee('Kerjakan Kuis Objektif');
        $response = $this->get(route('siswa.modules.quiz.show', $module))->assertOk()->assertDontSee('correct_answer');
        $this->assertArrayNotHasKey('correct_answer', $response->viewData('questions')[0]);
        $this->assertArrayNotHasKey('questions', $quiz->toArray());
    }

    public function test_score_is_computed_server_side_and_saved_with_original_questions(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $quiz = $module->quiz()->create($this->data());
        $siswa = User::factory()->create(['role' => 'siswa']);
        $response = $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), [
            'quiz_id' => $quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'D'], 'score' => 100, 'user_id' => $guru->id,
        ]);
        $attempt = ModuleQuizAttempt::firstOrFail();
        $response->assertRedirect(route('siswa.quiz-attempts.show', $attempt));
        $this->assertSame(50, $attempt->score);
        $this->assertSame($siswa->id, $attempt->user_id);
        $this->assertSame($quiz->questions, $attempt->questions_snapshot);
        $this->get($response->headers->get('Location'))->assertOk()->assertSee('Nilai: 50/100')->assertSee('Bluetooth');
        $this->actingAs($guru)->get(route('guru.modules.quiz.results', $module))->assertOk()->assertSee($siswa->name)->assertSee('50/100');
        $this->assertDatabaseCount('chat_histories', 0);
    }

    public function test_missing_extra_and_invalid_answers_do_not_create_attempts(): void
    {
        $module = $this->module(User::factory()->create(['role' => 'guru']));
        $quiz = $module->quiz()->create($this->data());
        $this->actingAs(User::factory()->create(['role' => 'siswa']));
        foreach ([['A'], ['A', 'B', 'C'], ['A', 'E']] as $answers) {
            $this->post(route('siswa.modules.quiz.submit', $module), ['quiz_id' => $quiz->id, 'quiz_version' => 1, 'answers' => $answers])->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('module_quiz_attempts', 0);
    }

    public function test_draft_expired_and_unavailable_modules_cannot_be_taken(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $siswa = User::factory()->create(['role' => 'siswa']);
        foreach ([['status_indexing' => 'pending'], ['berlaku_sampai' => today()->subDays(2)], []] as $attributes) {
            $module = $this->module($guru, $attributes);
            $quiz = $module->quiz()->create($this->data(['is_published' => (bool) $attributes]));
            $this->actingAs($siswa)->get(route('siswa.modules.quiz.show', $module))->assertStatus($attributes ? 403 : 404);
            $this->post(route('siswa.modules.quiz.submit', $module), ['quiz_id' => $quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'B']])->assertStatus($attributes ? 403 : 404);
        }
        $this->assertDatabaseCount('module_quiz_attempts', 0);
    }

    public function test_editing_or_recreating_quiz_rejects_stale_student_form(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $quiz = $module->quiz()->create($this->data());
        $this->actingAs($guru)->put(route('guru.modules.quiz.update', $module), $this->data())->assertSessionHasNoErrors();
        $siswa = User::factory()->create(['role' => 'siswa']);
        $payload = ['quiz_id' => $quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'B']];
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), $payload)->assertSessionHasErrors('quiz_version');
        $quiz->delete();
        $module->quiz()->create($this->data());
        $this->post(route('siswa.modules.quiz.submit', $module), $payload)->assertSessionHasErrors('quiz_version');
        $this->assertDatabaseCount('module_quiz_attempts', 0);
    }

    public function test_results_survive_quiz_edits_and_deletion_and_are_private(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        $module = $this->module($guru);
        $quiz = $module->quiz()->create($this->data());
        $siswa = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($siswa)->post(route('siswa.modules.quiz.submit', $module), ['quiz_id' => $quiz->id, 'quiz_version' => 1, 'answers' => ['A', 'B']]);
        $attempt = ModuleQuizAttempt::firstOrFail();
        $data = $this->data(['title' => 'Kuis Berubah']);
        $data['questions'][0]['text'] = 'Pertanyaan berbeda';
        $this->actingAs($guru)->put(route('guru.modules.quiz.update', $module), $data)->assertSessionHasNoErrors();
        $this->delete(route('guru.modules.quiz.destroy', $module));
        $this->assertNull($attempt->fresh()->module_quiz_id);
        $this->get(route('guru.modules.quiz.results', $module))->assertOk()->assertSee('Kuis Pilihan Ganda');
        $this->actingAs($siswa)->get(route('siswa.quiz-attempts.show', $attempt))->assertOk()->assertSee('Perangkat pusat koneksi nirkabel?')->assertDontSee('Pertanyaan berbeda');
        $this->actingAs(User::factory()->create(['role' => 'siswa']))->get(route('siswa.quiz-attempts.show', $attempt))->assertForbidden();
        $module->delete();
        $this->assertNull($attempt->fresh()->module_id);
        $this->actingAs($siswa)->get(route('siswa.quiz-attempts.show', $attempt))->assertOk();
    }
}
