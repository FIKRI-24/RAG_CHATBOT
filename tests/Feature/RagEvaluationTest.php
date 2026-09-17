<?php

namespace Tests\Feature;

use App\Exceptions\RagException;
use App\Models\Module;
use App\Models\User;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class RagEvaluationTest extends TestCase
{
    use RefreshDatabase;

    public function test_evaluator_reports_modules_latency_and_empty_scope_without_writing_chats(): void
    {
        Storage::fake('local');
        config(['gemini.api_key' => 'test-key']);
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response(['embedding' => ['values' => [1, 0]]])]);
        $module = Module::create(['guru_id' => User::factory()->create(['role' => 'guru'])->id, 'judul' => 'VLAN',
            'mapel' => 'Jaringan', 'kb_nomor' => 'KB 1', 'file_path' => 'modules/test.pdf', 'status_indexing' => 'completed']);
        $module->chunks()->create(['chunk_text' => 'VLAN memisahkan jaringan secara logis.', 'embedding_vector' => [1, 0], 'chunk_index' => 0]);
        $cases = [
            ['question' => 'Apa itu VLAN?', 'mapel' => 'Jaringan', 'expected_terms' => ['logis'], 'expected_modules' => ['VLAN'], 'module_id' => $module->id],
            ['question' => 'Apa itu VLAN?', 'mapel' => 'Mapel lainnya', 'expect_empty' => true],
        ];
        Storage::disk('local')->put('evaluation.json', json_encode($cases));
        $path = Storage::disk('local')->path('evaluation.json');
        $output = Storage::disk('local')->path('results.jsonl');
        $this->artisan('rag:evaluate', ['cases' => $path, '--output' => $output])->assertSuccessful();
        $rows = array_map(fn ($line) => json_decode($line, true), file($output, FILE_IGNORE_NEW_LINES));
        $this->assertSame(1, $rows[0]['module_precision']);
        $this->assertSame(1, $rows[0]['module_recall']);
        $this->assertSame([], $rows[1]['sources']);
        $this->assertSame(2, $rows[2]['passed']);
        $this->assertArrayHasKey('p95_ms', $rows[2]);
        $this->assertDatabaseCount('chat_histories', 0);
    }

    public function test_evaluator_fails_when_retrieval_is_empty_but_case_requires_evidence(): void
    {
        Storage::fake('local');
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['embedding' => ['values' => [1, 0]]])]);
        Storage::disk('local')->put('evaluation.json', json_encode([['question' => 'Q', 'expected_terms' => []]]));
        $this->artisan('rag:evaluate', ['cases' => Storage::disk('local')->path('evaluation.json')])->assertFailed();
    }

    public function test_deadline_stops_retry_without_exceeding_budget_or_sending_second_request(): void
    {
        config(['gemini.api_key' => 'test-key', 'rag.request_budget_seconds' => 2, 'rag.retry_delay_ms' => 2000]);
        Http::fake(['*' => Http::response([], 503)]);
        $service = app(GeminiService::class);
        $service->startBudget();
        try {
            $service->embedText('VLAN');
            $this->fail('Expected deadline failure');
        } catch (RagException $e) {
            $this->assertStringContainsString('Waktu pemrosesan', $e->getMessage());
        }
        Http::assertSentCount(1);
    }

    public function test_invalid_assessment_score_is_rejected(): void
    {
        config(['gemini.api_key' => 'test-key']);
        Http::fake(['*' => Http::response(['candidates' => [['content' => ['parts' => [['text' => '{"score":150,"feedback":"Good","criteria":["R"]}']]]]]])]);
        $this->expectException(RagException::class);
        app(GeminiService::class)->assessQuiz(['question' => 'Q', 'answer_key' => 'K', 'rubric' => ['R']], 'A', 'Source');
    }
}
