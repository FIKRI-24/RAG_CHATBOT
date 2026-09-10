<?php

namespace Tests\Feature;

use App\Jobs\ProcessModuleJob;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\User;
use App\Services\ChunkingService;
use App\Services\DocumentExtractorService;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class RagPipelineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gemini.api_key' => 'test-key', 'rag.retry_delay_ms' => 0]);
        Http::preventStrayRequests();
    }

    private function module(array $attributes = []): Module
    {
        return Module::create(array_merge([
            'guru_id' => User::factory()->create(['role' => 'guru'])->id,
            'judul' => 'Konfigurasi VLAN', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1',
            'file_path' => 'modules/test.pdf', 'status_indexing' => 'completed',
            'indexing_version' => (string) Str::uuid(),
        ], $attributes));
    }

    private function chunk(Module $module, string $text = 'VLAN memisahkan jaringan secara logis.', array $vector = [1, 0])
    {
        return $module->chunks()->create(['chunk_text' => $text, 'embedding_vector' => $vector,
            'embedding_model' => GeminiService::EMBEDDING_MODEL, 'embedding_dimensions' => count($vector), 'chunk_index' => 0]);
    }

    private function student(): User
    {
        $user = User::factory()->create(['role' => 'siswa']);
        $this->actingAs($user);

        return $user;
    }

    public function test_retrieval_filters_status_subject_expiry_dimensions_and_each_score(): void
    {
        $valid = $this->chunk($this->module(['berlaku_sampai' => today(config('app.display_timezone'))]));
        $this->chunk($this->module(['status_indexing' => 'failed']), 'failed');
        $this->chunk($this->module(['status_indexing' => 'processing']), 'processing');
        $this->chunk($this->module(['mapel' => 'Hardware']), 'hardware');
        $this->chunk($this->module(['berlaku_sampai' => today(config('app.display_timezone'))->subDay()]), 'expired');
        $this->chunk($valid->module, 'irrelevant', [0, 1]);
        $this->chunk($valid->module, 'wrong dimensions', [1, 0, 100]);
        $this->chunk($valid->module, $valid->chunk_text); // duplicate must not occupy another top-k slot
        $this->chunk($valid->module, 'wrong model')->update(['embedding_model' => 'other']);
        $matches = app(RetrievalService::class)->search([1, 0], 'Jaringan');
        $this->assertCount(1, $matches);
        $this->assertSame($valid->id, $matches[0]['chunk']->id);
    }

    public function test_empty_corpus_returns_deterministic_answer_without_api(): void
    {
        $this->student();
        $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Apa itu VLAN?'])
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('data.sources', [])
            ->assertJsonPath('data.jawaban', 'Materi untuk menjawab pertanyaan tersebut belum ditemukan dalam modul yang tersedia.');
        Http::assertNothingSent();
    }

    public function test_source_window_preserves_continuing_list_without_crossing_modules(): void
    {
        $module = $this->module();
        $anchor = $this->chunk($module, 'Keuntungan: Mobilitas. Kelemahan: Keamanan.');
        $next = $this->chunk($module, '2. Interferensi. 3. Jangkauan terbatas.', [0, 1]);
        $next->update(['chunk_index' => 1]);
        $this->chunk($this->module(['mapel' => 'Hardware']), 'Tidak boleh masuk')->update(['chunk_index' => 1]);
        $retrieval = app(RetrievalService::class);
        $sources = $retrieval->sources($retrieval->search([1, 0], 'Jaringan'), 'Jaringan');
        $this->assertCount(1, $sources);
        $this->assertSame([$anchor->id, $next->id], $sources[0]['chunk_ids']);
        $this->assertStringContainsString('Interferensi', $sources[0]['text']);
        $this->assertStringNotContainsString('Tidak boleh masuk', $sources[0]['text']);
    }

    public function test_below_threshold_does_not_call_generation(): void
    {
        $this->student();
        $this->chunk($this->module(), 'VLAN', [0, 1]);
        Http::fake(['*' => Http::response(['embedding' => ['values' => [1, 0]]])]);
        $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Topik di luar modul'])->assertOk()->assertJsonPath('data.sources', []);
        Http::assertSentCount(1);
        Http::assertSent(fn ($request) => str_contains($request->url(), ':embedContent'));
    }

    public function test_answer_uses_context_and_source_snapshot_survives_chunk_deletion(): void
    {
        $student = $this->student();
        $chunk = $this->chunk($this->module());
        Http::fake([
            '*:embedContent' => Http::response(['embedding' => ['values' => [1, 0]]]),
            '*:generateContent' => Http::response(['candidates' => [['finishReason' => 'STOP', 'content' => ['parts' => [['text' => 'VLAN memisahkan jaringan [1].']]]]]]),
        ]);
        $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Apa itu VLAN?', 'mapel' => 'Jaringan'])
            ->assertOk()->assertJsonPath('data.sources.0.chunk_id', $chunk->id);
        Http::assertSent(fn ($request) => str_contains($request->url(), ':generateContent')
            && str_contains($request['contents'][0]['parts'][0]['text'], $chunk->chunk_text));
        $chunk->delete();
        $chat = ChatHistory::where('siswa_id', $student->id)->first();
        $this->assertNull($chat->referensi_chunk_id);
        $this->assertSame('Konfigurasi VLAN', $chat->sources[0]['judul']);
        $this->get(route('siswa.dashboard'))->assertOk()->assertSee('Sumber materi');
    }

    public function test_followup_uses_only_same_student_and_subject_history(): void
    {
        $student = $this->student();
        $this->chunk($this->module());
        foreach ([[$student->id, 'Jaringan', 'Apa itu VLAN?'], [$student->id, 'Hardware', 'CPU?'],
            [User::factory()->create()->id, 'Jaringan', 'PRIVATE']] as [$id, $mapel, $question]) {
            ChatHistory::create(['siswa_id' => $id, 'mapel' => $mapel, 'kind' => 'answer', 'pertanyaan' => $question, 'jawaban' => 'jawaban']);
        }
        $mock = Mockery::mock(GeminiService::class);
        $mock->shouldReceive('rewriteQuestion')->once()->with('Contohnya?', Mockery::on(fn ($history) => count($history) === 1 && $history[0]['pertanyaan'] === 'Apa itu VLAN?'))->andReturn('Apa contoh VLAN?');
        $mock->shouldReceive('embedText')->with('Apa contoh VLAN?')->once()->andReturn([1, 0]);
        $mock->shouldReceive('generateAnswer')->once()->with('Apa contoh VLAN?', Mockery::type('string'))->andReturn('Contoh berdasarkan modul [1].');
        $this->app->instance(GeminiService::class, $mock);
        $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Contohnya?', 'mapel' => 'Jaringan'])->assertOk();
        $this->assertDatabaseHas('chat_histories', ['retrieval_query' => 'Apa contoh VLAN?']);
    }

    public function test_quiz_requires_explicit_answer_and_rejects_replay_other_users_and_wrong_subject(): void
    {
        $student = $this->student();
        $this->chunk($this->module());
        $mock = Mockery::mock(GeminiService::class);
        $mock->shouldReceive('generateQuiz')->once()->andReturn('Apa fungsi VLAN?');
        $mock->shouldReceive('gradeQuiz')->once()->andReturn('Benar.');
        $this->app->instance(GeminiService::class, $mock);
        $id = $this->postJson(route('siswa.chat.ask'), ['action' => 'quiz', 'mapel' => 'Jaringan'])->assertOk()->json('data.quiz_id');
        $payload = ['action' => 'quiz_answer', 'quiz_id' => $id, 'pertanyaan' => 'Memisahkan jaringan', 'mapel' => 'Jaringan'];
        $this->actingAs(User::factory()->create(['role' => 'siswa']));
        $this->postJson(route('siswa.chat.ask'), $payload)->assertStatus(409);
        $this->actingAs($student);
        $this->postJson(route('siswa.chat.ask'), array_replace($payload, ['mapel' => 'Hardware']))->assertStatus(409);
        $this->postJson(route('siswa.chat.ask'), $payload)->assertOk()->assertJsonPath('data.quiz_id', null);
        $this->postJson(route('siswa.chat.ask'), $payload)->assertStatus(409);
    }

    public function test_quiz_source_removed_prevents_grading_and_can_be_cancelled(): void
    {
        $student = $this->student();
        $chunk = $this->chunk($this->module());
        $quiz = ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => '[LATIHAN_SOAL]',
            'jawaban' => 'Soal', 'kind' => 'quiz', 'quiz_status' => 'pending', 'mapel' => 'Semua', 'referensi_chunk_id' => $chunk->id]);
        $chunk->delete();
        $this->postJson(route('siswa.chat.ask'), ['action' => 'quiz_answer', 'quiz_id' => $quiz->id, 'pertanyaan' => 'A'])->assertStatus(409);
        $this->assertSame('cancelled', $quiz->fresh()->quiz_status);
        $this->postJson(route('siswa.chat.ask'), ['action' => 'cancel_quiz'])->assertOk();
        Http::assertNothingSent();
    }

    public function test_normal_question_after_quiz_is_not_graded(): void
    {
        $student = $this->student();
        $quiz = ChatHistory::create(['siswa_id' => $student->id, 'pertanyaan' => '[LATIHAN_SOAL]', 'jawaban' => 'Soal', 'kind' => 'quiz', 'quiz_status' => 'pending']);
        $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Pertanyaan baru'])->assertOk();
        $this->assertSame('cancelled', $quiz->fresh()->quiz_status);
        Http::assertNothingSent();
    }

    public function test_failed_index_keeps_old_chunks_and_retry_publishes_once(): void
    {
        $module = $this->module(['status_indexing' => 'pending']);
        $old = $this->chunk($module, 'old');
        $legacyChat = ChatHistory::create(['siswa_id' => User::factory()->create()->id,
            'pertanyaan' => 'Materi lama?', 'jawaban' => 'old', 'referensi_chunk_id' => $old->id]);
        $extractor = Mockery::mock(DocumentExtractorService::class);
        $extractor->shouldReceive('extract')->andReturn(str_repeat('materi ', 150));
        $gemini = Mockery::mock(GeminiService::class);
        $gemini->shouldReceive('embedText')->once()->andReturn([1, 0]);
        $gemini->shouldReceive('embedText')->once()->andThrow(new RuntimeException('API unavailable'));
        $job = new ProcessModuleJob($module);
        try {
            $job->handle($extractor, new ChunkingService, $gemini);
            $this->fail('Expected failure');
        } catch (RuntimeException $e) {
            $this->assertSame('failed', $module->fresh()->status_indexing);
            $this->assertSame([$old->id], $module->chunks()->pluck('id')->all());
        }
        $healthy = Mockery::mock(GeminiService::class);
        $healthy->shouldReceive('embedText')->andReturn([1, 0]);
        $job->handle($extractor, new ChunkingService, $healthy);
        $ids = $module->chunks()->pluck('id')->all();
        $this->assertGreaterThan(1, count($ids));
        $this->assertSame('completed', $module->fresh()->status_indexing);
        $this->assertSame('old', $legacyChat->fresh()->sources[0]['text']);
        $job->handle($extractor, new ChunkingService, $healthy);
        $this->assertSame($ids, $module->chunks()->pluck('id')->all());
    }

    public function test_empty_extraction_is_failed_not_completed(): void
    {
        $module = $this->module(['status_indexing' => 'pending']);
        $extractor = Mockery::mock(DocumentExtractorService::class);
        $extractor->shouldReceive('extract')->once()->andReturn(' ');
        try {
            (new ProcessModuleJob($module))->handle($extractor, new ChunkingService, app(GeminiService::class));
            $this->fail('Expected failure');
        } catch (RuntimeException $e) {
            $this->assertStringContainsString('OCR', $module->fresh()->indexing_error);
            $this->assertSame('failed', $module->fresh()->status_indexing);
        }
        Http::assertNothingSent();
    }

    public function test_job_cannot_publish_after_newer_version_is_requested(): void
    {
        $module = $this->module(['status_indexing' => 'pending']);
        $old = $this->chunk($module, 'old');
        $job = new ProcessModuleJob($module);
        $extractor = Mockery::mock(DocumentExtractorService::class);
        $extractor->shouldReceive('extract')->andReturn('new content');
        $gemini = Mockery::mock(GeminiService::class);
        $gemini->shouldReceive('embedText')->once()->andReturnUsing(function () use ($module) {
            $module->refresh()->update(['indexing_version' => (string) Str::uuid(), 'status_indexing' => 'pending']);

            return [1, 0];
        });
        $job->handle($extractor, new ChunkingService, $gemini);
        $this->assertSame('pending', $module->fresh()->status_indexing);
        $this->assertSame([$old->id], $module->chunks()->pluck('id')->all());
        $job->failed(new RuntimeException('old job failed'));
        $this->assertSame('pending', $module->fresh()->status_indexing);
    }

    public function test_file_update_is_saved_before_async_dispatch_and_preserves_index(): void
    {
        Queue::fake();
        Storage::fake('local');
        $module = $this->module();
        $chunk = $this->chunk($module);
        $this->actingAs($module->guru);
        $this->put(route('guru.modules.update', $module), [
            'judul' => 'Baru', 'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1',
            'file' => UploadedFile::fake()->create('baru.pdf', 10, 'application/pdf'),
        ])->assertRedirect();
        Queue::assertPushed(ProcessModuleJob::class, function ($job) use ($module) {
            $current = $module->fresh();

            return $job->connection === 'rag' && $job->filePath === $current->file_path && $job->version === $current->indexing_version;
        });
        $this->assertSame([$chunk->id], $module->chunks()->pluck('id')->all());
    }

    public function test_evaluation_command_checks_live_pipeline_without_saving_chat(): void
    {
        Storage::fake('local');
        $this->chunk($this->module());
        Storage::disk('local')->put('cases.json', json_encode([
            ['question' => 'Apa itu VLAN?', 'mapel' => 'Jaringan', 'expected_terms' => ['VLAN']],
            ['question' => 'Apa itu VLAN?', 'mapel' => 'Mapel lain', 'expect_empty' => true],
        ]));
        Http::fake(['*' => Http::response(['embedding' => ['values' => [1, 0]]])]);
        $this->artisan('rag:evaluate', ['cases' => Storage::disk('local')->path('cases.json')])->assertSuccessful();
        $this->assertSame(0, ChatHistory::count());
        Http::assertSentCount(2);
    }

    public function test_api_failure_does_not_write_chat_or_expose_provider_body(): void
    {
        $this->student();
        $this->chunk($this->module());
        Http::fake(['*' => Http::response(['message' => 'provider-secret'], 503)]);
        $response = $this->postJson(route('siswa.chat.ask'), ['pertanyaan' => 'Apa itu VLAN?']);
        $response->assertStatus(503)->assertJsonPath('success', false)->assertDontSee('provider-secret');
        $this->assertSame(0, ChatHistory::count());
        Http::assertSentCount(3);
    }
}
