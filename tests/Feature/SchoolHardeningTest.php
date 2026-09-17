<?php

namespace Tests\Feature;

use App\Exceptions\RagException;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\User;
use App\Services\AiUsageService;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class SchoolHardeningTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function module(): Module
    {
        $module = Module::create(['guru_id' => User::factory()->create(['role' => 'guru'])->id,
            'judul' => 'VLAN', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1', 'file_path' => 'modules/test.pdf', 'status_indexing' => 'completed']);
        $module->chunks()->create(['chunk_text' => 'VLAN memisahkan jaringan secara logis.', 'embedding_vector' => [1, 0], 'chunk_index' => 0]);

        return $module;
    }

    public function test_registration_policy_blocks_both_get_and_post(): void
    {
        config(['security.registration_enabled' => false]);
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'Unwanted', 'email' => 'new@example.test', 'password' => 'password', 'password_confirmation' => 'password'])->assertNotFound();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_reset_notification_uses_canonical_app_url(): void
    {
        config(['app.url' => 'https://school.example.test']);
        $user = User::factory()->create();
        $mail = (new ResetPassword('reset-token'))->toMail($user);
        $this->assertStringStartsWith('https://school.example.test/reset-password/reset-token?', $mail->actionUrl);
    }

    public function test_forgot_password_has_same_public_response_for_unknown_email(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $existing = $this->post('/forgot-password', ['email' => $user->email])->assertSessionHasNoErrors()->assertRedirect();
        $status = session('status');
        $this->post('/forgot-password', ['email' => 'unknown@example.test'])->assertSessionHasNoErrors()->assertRedirect()->assertSessionHas('status', $status);
        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_revokes_existing_sessions_and_stale_session_version(): void
    {
        config(['session.driver' => 'database']);
        $user = User::factory()->create(['role' => 'siswa']);
        DB::table('sessions')->insert(['id' => 'stolen-session', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
        $token = Password::createToken($user);
        $this->post('/reset-password', ['token' => $token, 'email' => $user->email, 'password' => 'new-password-123', 'password_confirmation' => 'new-password-123'])->assertRedirect(route('login'));
        $this->assertDatabaseMissing('sessions', ['id' => 'stolen-session']);
        $this->assertSame(2, $user->fresh()->auth_version);
        $this->actingAs($user->fresh())->withSession(['auth_version' => 1])->getJson('/siswa/dashboard')->assertUnauthorized();
        $this->assertDatabaseHas('audit_events', ['action' => 'account.password_reset', 'subject_id' => $user->id]);
    }

    public function test_inactive_user_cannot_login_or_use_existing_session_and_history_survives(): void
    {
        $user = User::factory()->create(['role' => 'siswa']);
        ChatHistory::create(['siswa_id' => $user->id, 'pertanyaan' => 'Hello', 'jawaban' => 'Hello']);
        $teacher = User::factory()->create(['role' => 'guru']);
        $this->actingAs($teacher)->patch(route('guru.siswa.status', $user), ['is_active' => false])->assertRedirect();
        $this->assertDatabaseCount('chat_histories', 1);
        $this->actingAs($user->fresh())->getJson('/siswa/dashboard')->assertUnauthorized();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
    }

    public function test_all_teachers_can_manage_all_students(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($teacher)->put(route('guru.siswa.update', $student), ['name' => 'New name', 'email' => strtoupper($student->email), 'class_name' => 'XI TKJ', 'student_number' => '123'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $student->id, 'class_name' => 'XI TKJ', 'student_number' => '123', 'email' => $student->email]);
        $this->patch('/profile', ['name' => $teacher->name, 'email' => $teacher->email, 'teacher_number' => 'NIP-123'])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $teacher->id, 'teacher_number' => 'NIP-123']);
    }

    public function test_teacher_deletion_requires_transfer_and_preserves_modules(): void
    {
        $module = $this->module();
        $teacher = $module->guru;
        $this->actingAs($teacher)->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('transfer_to', null, 'userDeletion');
        $this->assertDatabaseHas('modules', ['id' => $module->id, 'guru_id' => $teacher->id]);
        $replacement = User::factory()->create(['role' => 'guru']);
        $this->delete('/profile', ['password' => 'password', 'transfer_to' => $replacement->id])->assertRedirect(route('login'));
        $this->assertDatabaseHas('modules', ['id' => $module->id, 'guru_id' => $replacement->id]);
        $this->assertDatabaseCount('module_chunks', 1);
    }

    public function test_student_deletion_removes_avatar_and_records_audit(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'avatar' => 'avatars/test.jpg']);
        Storage::disk('public')->put($student->avatar, 'image');
        $this->actingAs(User::factory()->create(['role' => 'guru']))->delete(route('guru.siswa.destroy', $student))->assertRedirect();
        Storage::disk('public')->assertMissing('avatars/test.jpg');
        $this->assertDatabaseHas('audit_events', ['action' => 'account.deleted', 'subject_id' => $student->id]);
    }

    public function test_final_teacher_account_cannot_be_removed(): void
    {
        $teacher = User::factory()->create(['role' => 'guru']);
        $this->actingAs($teacher)->delete('/profile', ['password' => 'password'])->assertSessionHasErrors('transfer_to', null, 'userDeletion');
        $this->assertDatabaseHas('users', ['id' => $teacher->id]);
    }

    public function test_normal_answer_is_not_published_when_source_is_withdrawn_during_generation(): void
    {
        $module = $this->module();
        $mock = Mockery::mock(GeminiService::class);
        $mock->shouldReceive('startBudget')->once();
        $mock->shouldReceive('rewriteQuestion')->andReturn('Apa itu VLAN?');
        $mock->shouldReceive('embedText')->andReturn([1, 0]);
        $mock->shouldReceive('generateAnswer')->once()->andReturnUsing(function () use ($module) {
            $module->update(['status_indexing' => 'pending']);

            return 'Stale answer [1]';
        });
        $this->app->instance(GeminiService::class, $mock);
        $this->actingAs(User::factory()->create(['role' => 'siswa']))->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Apa itu VLAN?'])->assertStatus(409);
        $this->assertDatabaseCount('chat_histories', 0);
    }

    public function test_module_scope_excludes_other_modules_and_unavailable_selection(): void
    {
        $first = $this->module();
        $second = $this->module();
        $retrieval = app(RetrievalService::class);
        $retrieval->scope($second->id);
        $matches = $retrieval->search([1, 0]);
        $this->assertCount(1, $matches);
        $this->assertSame($second->id, $matches[0]['chunk']->module_id);
        $first->update(['status_indexing' => 'failed']);
        $this->actingAs(User::factory()->create(['role' => 'siswa']))->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Q', 'module_id' => $first->id])->assertUnprocessable();
        Http::assertNothingSent();
    }

    public function test_quota_rolls_back_global_increment_when_user_limit_is_exhausted(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'siswa']));
        config(['rag.daily_user_limit' => 1, 'rag.daily_global_limit' => 10]);
        $service = app(AiUsageService::class);
        $service->reserve();
        try {
            $service->reserve();
            $this->fail('Expected quota rejection');
        } catch (RagException $e) {
        }
        $this->assertSame(1, DB::table('ai_usage')->where('bucket', 'global')->value('requests'));
    }

    public function test_structured_quiz_rejects_invalid_keys_and_grade_range(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{"question":"Q","rubric":["R"]}']]]]]])]);
        $this->expectException(RagException::class);
        app(GeminiService::class)->structuredQuiz('VLAN');
    }

    public function test_teacher_review_requires_valid_score_and_keeps_ai_assessment(): void
    {
        $student = User::factory()->create(['role' => 'siswa']);
        $quiz = ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => '[LATIHAN_SOAL]', 'jawaban' => 'Q', 'kind' => 'quiz', 'quiz_payload' => ['answer_key' => 'Secret answer key', 'rubric' => ['R']]]);
        $feedback = ChatHistory::create(['siswa_id' => $student->id, 'quiz_id' => $quiz->id, 'pertanyaan' => 'A', 'jawaban' => 'F', 'kind' => 'quiz_feedback', 'assessment' => ['score' => 60, 'feedback' => 'F'], 'score' => 60]);
        $this->assertArrayNotHasKey('quiz_payload', $quiz->toArray());
        $this->actingAs($student)->patchJson(route('guru.quiz-reviews.update', $feedback), ['score' => 90, 'review_note' => 'R'])->assertForbidden();
        $teacher = User::factory()->create(['role' => 'guru']);
        $this->actingAs($teacher)->patchJson(route('guru.quiz-reviews.update', $feedback), ['score' => 101, 'review_note' => 'R'])->assertUnprocessable();
        $this->patch(route('guru.quiz-reviews.update', $feedback), ['score' => 90, 'review_note' => 'Reviewed'])->assertRedirect();
        $this->assertSame(90, $feedback->fresh()->score);
        $this->assertSame(60, $feedback->fresh()->assessment['score']);
        $this->get('/guru/quiz-reviews?status=reviewed')->assertOk()->assertSee('Reviewed');
        $export = $this->get(route('guru.siswa.export'))->assertOk();
        Storage::disk('local')->put('review.xlsx', $export->streamedContent());
        $book = \PhpOffice\PhpSpreadsheet\IOFactory::load(Storage::disk('local')->path('review.xlsx'));
        $this->assertStringContainsString('Penilaian guru: 90/100', $book->getSheet(1)->getCell('F4')->getValue());
        $book->disconnectWorksheets();
    }

    public function test_system_status_is_teacher_only_without_external_calls(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'siswa']))->get('/guru/system')->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'guru']))->get('/guru/system')->assertOk()->assertSee('Status sistem lokal');
        $this->artisan('system:check')->assertSuccessful();
        Http::assertNothingSent();
    }
}
