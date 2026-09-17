<?php

namespace Tests\Feature;

use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Models\User;
use App\Services\GeminiService;
use App\Services\StoredFileService;
use Carbon\Carbon;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Mockery;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AuditRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
        Storage::fake('local');
        Storage::fake('public');
        Queue::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function module(array $attributes = []): Module
    {
        return Module::create($attributes + [
            'guru_id' => User::factory()->create(['role' => 'guru'])->id,
            'judul' => 'Materi audit', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1',
            'file_path' => 'modules/audit.pdf', 'status_indexing' => 'completed',
        ]);
    }

    private function chunk(Module $module): ModuleChunk
    {
        return $module->chunks()->create(['chunk_text' => 'VLAN memisahkan jaringan.',
            'embedding_vector' => [1, 0], 'chunk_index' => 0]);
    }

    public function test_student_cannot_download_expired_or_unindexed_modules_but_owner_can(): void
    {
        Storage::disk('local')->put('modules/audit.pdf', 'document');
        $student = User::factory()->create(['role' => 'siswa']);
        foreach ([['berlaku_sampai' => '2000-01-01'], ['status_indexing' => 'pending'], ['status_indexing' => 'failed']] as $attributes) {
            $module = $this->module($attributes);
            $this->actingAs($student)->get(route('modules.download', $module))->assertForbidden();
            $this->get(route('siswa.modules.show', $module))->assertRedirect(route('siswa.modules.index'));
            $this->actingAs($module->guru)->get(route('modules.download', $module))->assertOk();
        }
    }

    public function test_school_expiry_is_consistent_at_the_wib_day_boundary(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-11 18:00:00', 'UTC'));
        $today = $this->module(['berlaku_sampai' => '2026-09-12']);
        $yesterday = $this->module(['berlaku_sampai' => '2026-09-11', 'judul' => 'Sudah kedaluwarsa']);
        $this->assertTrue(Module::available()->whereKey($today->id)->exists());
        $this->assertFalse(Module::available()->whereKey($yesterday->id)->exists());
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->get(route('siswa.modules.index'))->assertSee('Materi audit')->assertDontSee('Sudah kedaluwarsa');
        $this->get(route('siswa.modules.show', $today))->assertOk();
    }

    public function test_long_urls_are_rejected_before_writing_files_or_database(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'guru']))->post(route('guru.modules.store'), [
            'judul' => 'Test', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1',
            'video_url' => 'https://example.com/'.str_repeat('a', 260),
            'file' => UploadedFile::fake()->create('module.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('video_url');
        $this->assertDatabaseCount('modules', 0);
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_deleting_someone_elses_module_returns_403_and_preserves_file(): void
    {
        $module = $this->module();
        Storage::disk('local')->put($module->file_path, 'original');
        $this->actingAs(User::factory()->create(['role' => 'guru']))
            ->delete(route('guru.modules.destroy', $module))->assertForbidden();
        $this->assertDatabaseHas('modules', ['id' => $module->id]);
        Storage::disk('local')->assertExists($module->file_path);
    }

    public function test_failed_module_update_keeps_original_file_and_cleans_replacement(): void
    {
        $module = $this->module();
        Storage::disk('local')->put($module->file_path, 'original');
        Module::saving(function ($model) {
            if ($model->judul === 'Trigger failure') {
                throw new \RuntimeException('private database detail');
            }
        });
        $this->actingAs($module->guru)->put(route('guru.modules.update', $module), [
            'judul' => 'Trigger failure', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1',
            'file' => UploadedFile::fake()->create('replacement.pdf', 10, 'application/pdf'),
        ])->assertSessionHasErrors('file');
        $this->assertSame('original', Storage::disk('local')->get($module->file_path));
        $this->assertCount(1, Storage::disk('local')->allFiles());
        $this->assertSame('Materi audit', $module->fresh()->judul);
        Queue::assertNothingPushed();
    }

    public function test_storage_false_result_is_reported_as_a_validation_error(): void
    {
        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('store')->with('modules', 'local')->once()->andReturn(false);
        $this->expectException(ValidationException::class);
        app(StoredFileService::class)->store($file, 'modules', 'local', 'file');
    }

    public function test_failed_profile_update_preserves_old_avatar(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'avatar' => 'avatars/old.png']);
        Storage::disk('public')->put($student->avatar, 'old avatar');
        User::saving(function ($model) {
            if ($model->name === 'Trigger failure') {
                throw new \RuntimeException('database error');
            }
        });
        $this->actingAs($student)->patch(route('profile.update'), [
            'name' => 'Trigger failure', 'email' => $student->email,
            'avatar' => UploadedFile::fake()->image('new.png'), 'remove_avatar' => '1',
        ])->assertSessionHasErrors('avatar');
        Storage::disk('public')->assertExists('avatars/old.png');
        $this->assertCount(1, Storage::disk('public')->allFiles());
        $this->assertSame('avatars/old.png', $student->fresh()->avatar);
    }

    public function test_deletion_preserves_legacy_chat_source_snapshot(): void
    {
        $module = $this->module();
        $chunk = $this->chunk($module);
        $chat = ChatHistory::create(['siswa_id' => User::factory()->create()->id,
            'pertanyaan' => 'VLAN?', 'jawaban' => 'VLAN.', 'referensi_chunk_id' => $chunk->id]);
        $this->actingAs($module->guru)->delete(route('guru.modules.destroy', $module))->assertRedirect();
        $this->assertNull($chat->fresh()->referensi_chunk_id);
        $this->assertSame('Materi audit', $chat->fresh()->sources[0]['judul']);
    }

    public function test_dashboard_works_on_sqlite_and_counts_wib_months_and_question_types(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-15', 'UTC'));
        $module = $this->module();
        $student = User::factory()->create(['role' => 'siswa']);
        foreach (['answer', 'quiz', 'quiz_feedback'] as $kind) {
            ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => $kind === 'quiz' ? '[LATIHAN_SOAL]' : 'Teks',
                'jawaban' => 'Teks', 'kind' => $kind, 'created_at' => now()]);
        }
        $chat = ChatHistory::first();
        $chat->created_at = '2026-08-31 18:00:00';
        $chat->save();
        $this->actingAs($module->guru)->get(route('guru.dashboard'))->assertOk()
            ->assertViewHas('totalPertanyaanBiasa', 1)->assertViewHas('totalKuis', 1)
            ->assertViewHas('chartChats', fn ($counts) => $counts[7] === 0 && $counts[8] === 1);
    }

    public function test_export_keeps_formulas_as_text_and_uses_snapshots_and_wib(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-09-11 18:00:00', 'UTC'));
        $guru = User::factory()->create(['role' => 'guru']);
        $student = User::factory()->create(['role' => 'siswa', 'name' => '=1+1']);
        foreach (['quiz', 'quiz_feedback', 'answer'] as $kind) {
            ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => $kind === 'quiz' ? '[LATIHAN_SOAL]' : '=1+1',
                'jawaban' => '=2+2', 'kind' => $kind,
                'sources' => [['judul' => 'Sumber sebelum reindex', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1']]]);
        }
        $response = $this->actingAs($guru)->get(route('guru.siswa.export'))->assertOk();
        Storage::disk('local')->put('audit.xlsx', $response->streamedContent());
        $book = IOFactory::load(Storage::disk('local')->path('audit.xlsx'));
        $summary = $book->getSheet(0);
        $this->assertSame('s', $summary->getCell('C14')->getDataType());
        $this->assertSame('=1+1', $summary->getCell('C14')->getValue());
        $this->assertSame('1 kali', $summary->getCell('F14')->getValue());
        $this->assertSame('-', $summary->getCell('D14')->getValue());
        $log = $book->getSheet(1);
        $this->assertSame('s', $log->getCell('E4')->getDataType());
        $this->assertSame('=1+1', $log->getCell('E4')->getValue());
        $this->assertSame('s', $log->getCell('F4')->getDataType());
        $this->assertStringContainsString('Sumber sebelum reindex', $log->getCell('D4')->getValue());
        $this->assertSame('12/09/2026 01:00', $log->getCell('B4')->getValue());
        $this->assertSame('Jawaban Kuis', $log->getCell('G5')->getValue());
        $book->disconnectWorksheets();
    }

    public function test_chat_history_is_paginated_and_names_are_js_encoded(): void
    {
        $student = User::factory()->create(['role' => 'siswa', 'name' => 'Student ` ${globalThis.marker=123}']);
        for ($i = 1; $i <= 35; $i++) {
            ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => 'Question '.$i, 'jawaban' => 'Answer']);
        }
        $response = $this->actingAs($student)->get(route('siswa.dashboard'))->assertOk()
            ->assertViewHas('chats', fn ($chats) => $chats->count() === 30 && $chats->first()->pertanyaan === 'Question 6');
        $this->assertStringNotContainsString('alt="Student ` ${globalThis.marker=123}" class=',
            explode('function appendUserMessage', $response->getContent())[1]);
        $this->get($response->viewData('historyPages')->nextPageUrl())->assertOk()
            ->assertViewHas('chats', fn ($chats) => $chats->count() === 5);
    }

    public function test_quiz_creation_handles_source_deleted_during_api_call(): void
    {
        $chunk = $this->chunk($this->module());
        $mock = Mockery::mock(GeminiService::class);
        $mock->shouldReceive('startBudget')->andReturnNull();
        $mock->shouldReceive('structuredQuiz')->once()->andReturnUsing(function () use ($chunk) {
            $chunk->delete();

            return ['question' => 'Question', 'answer_key' => 'Key', 'rubric' => ['Criterion']];
        });
        $this->app->instance(GeminiService::class, $mock);
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->postJson(route('siswa.chat.ask'), ['action' => 'quiz'])->assertStatus(409);
        $this->assertDatabaseCount('chat_histories', 0);
    }

    public function test_quiz_grading_handles_source_withdrawn_during_api_call(): void
    {
        $module = $this->module();
        $chunk = $this->chunk($module);
        $student = User::factory()->create(['role' => 'siswa']);
        $quiz = ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => '[LATIHAN_SOAL]',
            'jawaban' => 'Question', 'kind' => 'quiz', 'quiz_status' => 'pending', 'mapel' => 'Semua',
            'referensi_chunk_id' => $chunk->id, 'quiz_payload' => ['question' => 'Question', 'answer_key' => 'Key', 'rubric' => ['Criterion']]]);
        $mock = Mockery::mock(GeminiService::class);
        $mock->shouldReceive('startBudget')->andReturnNull();
        $mock->shouldReceive('assessQuiz')->once()->andReturnUsing(function () use ($module) {
            $module->update(['status_indexing' => 'pending']);

            return ['score' => 100, 'feedback' => 'Correct', 'criteria' => ['Criterion']];
        });
        $this->app->instance(GeminiService::class, $mock);
        $this->actingAs($student)->postJson(route('siswa.chat.ask'), [
            'action' => 'quiz_answer', 'quiz_id' => $quiz->id, 'pertanyaan' => 'Answer',
        ])->assertStatus(409);
        $this->assertSame('cancelled', $quiz->fresh()->quiz_status);
        $this->assertDatabaseCount('chat_histories', 1);
    }

    public function test_demo_seeder_is_repeatable_and_preserves_existing_passwords(): void
    {
        $this->seed(UserSeeder::class);
        $guru = User::where('email', 'guru@tkj.com')->firstOrFail();
        $guru->update(['password' => 'a-new-password']);
        $hash = $guru->fresh()->password;
        $this->seed(UserSeeder::class);
        $this->assertDatabaseCount('users', 3);
        $this->assertSame($hash, $guru->fresh()->password);
    }

    public function test_unrelated_kb_module_does_not_show_seeded_videos(): void
    {
        $module = $this->module(['video_url' => 'https://example.com/video']);
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->get(route('siswa.modules.show', $module))->assertOk()->assertDontSee('Video Pembelajaran Tambahan');
    }

    public function test_filtered_catalog_pagination_preserves_filters(): void
    {
        $guru = User::factory()->create(['role' => 'guru']);
        for ($i = 0; $i < 12; $i++) {
            $this->module(['guru_id' => $guru->id]);
        }
        $this->actingAs(User::factory()->create(['role' => 'siswa']))
            ->get(route('siswa.modules.index', ['mapel' => 'Jaringan', 'kb_nomor' => 'KB 1']))->assertOk()
            ->assertViewHas('modules', fn ($modules) => str_contains($modules->nextPageUrl(), 'mapel=Jaringan'));
    }
}
