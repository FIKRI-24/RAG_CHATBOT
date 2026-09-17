<?php

namespace Tests\Feature;

use App\Services\DocumentExtractorService;
use App\Services\GeminiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use RuntimeException;
use Tests\TestCase;

class RagApiAndExtractionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['gemini.api_key' => 'secret-test-key', 'rag.retry_delay_ms' => 0]);
        Http::preventStrayRequests();
    }

    public function test_embedding_retries_transient_errors_and_keeps_key_out_of_url(): void
    {
        Http::fake(['*' => Http::sequence()->push([], 429)->push([], 503)->push(['embedding' => ['values' => [1, 0]]])]);
        $this->assertSame([1, 0], app(GeminiService::class)->embedText('VLAN'));
        Http::assertSentCount(3);
        Http::assertSent(fn ($request) => ! str_contains($request->url(), 'secret-test-key') && $request->hasHeader('x-goog-api-key', 'secret-test-key'));
    }

    public function test_success_without_vector_is_rejected(): void
    {
        Http::fake(['*' => Http::response(['embedding' => ['values' => []]])]);
        $this->expectException(RuntimeException::class);
        app(GeminiService::class)->embedText('VLAN');
    }

    public function test_blocked_generation_is_not_saved_as_success_text(): void
    {
        Http::fake(['*' => Http::response(['candidates' => [['finishReason' => 'SAFETY']]])]);
        $this->expectException(RuntimeException::class);
        app(GeminiService::class)->generateAnswer('VLAN?', 'VLAN adalah jaringan logis.');
    }

    public function test_permanent_api_error_is_not_retried_or_exposed(): void
    {
        Http::fake(['*' => Http::response(['message' => 'secret-provider-detail'], 403)]);
        try {
            app(GeminiService::class)->embedText('VLAN');
            $this->fail('Expected failure');
        } catch (RuntimeException $e) {
            $this->assertStringNotContainsString('secret-provider-detail', $e->getMessage());
        }
        Http::assertSentCount(1);
    }

    public function test_real_docx_extraction_preserves_paragraphs_and_table_cells(): void
    {
        Storage::fake('local');
        $doc = new PhpWord;
        $section = $doc->addSection();
        $section->addText('Paragraf pertama');
        $section->addText('Paragraf kedua');
        $table = $section->addTable();
        $table->addRow();
        $table->addCell()->addText('VLAN 10');
        $table->addCell()->addText('Guru');
        $path = Storage::disk('local')->path('rag-extraction.docx');
        IOFactory::createWriter($doc, 'Word2007')->save($path);
        $text = app(DocumentExtractorService::class)->extract($path);
        $this->assertMatchesRegularExpression('/Paragraf pertama\s*\n\nParagraf kedua/', $text);
        $this->assertStringContainsString('VLAN 10  | Guru', $text);
    }
}
