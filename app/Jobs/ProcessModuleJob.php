<?php

namespace App\Jobs;

use App\Exceptions\RagException;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Services\ChunkingService;
use App\Services\DocumentExtractorService;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use App\Services\VectorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessModuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;

    public $tries = 3;

    public $backoff = [15, 60];

    public $failOnTimeout = true;

    public int $moduleId;

    public ?string $version;

    public string $filePath;

    public function __construct(Module $module)
    {
        $this->moduleId = $module->id;
        $this->version = $module->indexing_version;
        $this->filePath = $module->file_path;
        $this->onConnection('rag')->onQueue('rag')->afterCommit();
    }

    private function current()
    {
        return Module::whereKey($this->moduleId)->where('indexing_version', $this->version)
            ->where('file_path', $this->filePath)->where('status_indexing', '!=', 'completed');
    }

    public function handle(DocumentExtractorService $extractor, ChunkingService $chunker, GeminiService $gemini): void
    {
        if (! $this->current()->update(['status_indexing' => 'processing', 'indexing_error' => null])) {
            return;
        }
        $staged = null;
        try {
            $staged = tmpfile();
            if ($staged === false) {
                throw new RagException('Penyimpanan sementara indexing tidak tersedia. Periksa ruang penyimpanan server.');
            }
            $text = $extractor->extract(Storage::disk('local')->path($this->filePath));
            $chunks = $chunker->chunk($text, config('rag.chunk_size'), config('rag.chunk_overlap'));
            if (! $chunks) {
                throw new RagException('Dokumen tidak memiliki teks terbaca. Gunakan PDF dengan lapisan teks atau DOCX; PDF scan perlu OCR terlebih dahulu.');
            }
            if (count($chunks) > (int) config('rag.max_chunks_per_module', 200)) {
                throw new RagException('Dokumen terlalu panjang untuk satu modul. Pisahkan per kegiatan belajar sebelum diindeks.');
            }
            $gemini->startBudget((int) config('rag.index_budget_seconds', 270));
            $dimensions = null;
            foreach ($chunks as $index => $chunk) {
                if (! $this->current()->exists()) {
                    return;
                }
                $vector = $gemini->embedText($chunk);
                if (! VectorService::valid($vector) || ($dimensions !== null && count($vector) !== $dimensions)) {
                    throw new RagException('Embedding dokumen tidak valid atau dimensinya tidak konsisten.');
                }
                $dimensions = count($vector);
                $row = ['chunk_text' => $chunk, 'embedding_vector' => $vector,
                    'embedding_model' => GeminiService::EMBEDDING_MODEL,
                    'embedding_dimensions' => $dimensions, 'chunk_index' => $index];
                $encoded = json_encode($row, JSON_THROW_ON_ERROR)."\n";
                if (fwrite($staged, $encoded) !== strlen($encoded)) {
                    throw new RagException('Penyimpanan sementara indexing penuh. Indeks lama tetap tersimpan.');
                }
            }
            // Publish only a complete index. Stale/repeated jobs cannot replace it.
            DB::transaction(function () use ($staged) {
                $module = $this->current()->lockForUpdate()->first();
                if (! $module) {
                    return;
                }
                // Preserve the known reference of legacy chats before removing old IDs.
                foreach ($module->chunks()->with('module')->lazyById(100) as $oldChunk) {
                    ChatHistory::where('referensi_chunk_id', $oldChunk->id)->whereNull('sources')
                        ->update(['sources' => json_encode([(new RetrievalService)->source($oldChunk)], JSON_UNESCAPED_UNICODE)]);
                }
                $module->chunks()->delete();
                if (! rewind($staged)) {
                    throw new RagException('Indeks sementara tidak dapat dibaca.');
                }
                while (($line = fgets($staged)) !== false) {
                    $module->chunks()->create(json_decode($line, true, 512, JSON_THROW_ON_ERROR));
                }
                if (! feof($staged)) {
                    throw new RagException('Pembacaan indeks sementara terhenti.');
                }
                $module->update(['status_indexing' => 'completed', 'indexing_error' => null]);
            });
        } catch (Throwable $e) {
            $this->markFailed($e);
            throw $e;
        } finally {
            if (is_resource($staged)) {
                fclose($staged);
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->markFailed($exception);
    }

    private function markFailed(?Throwable $exception): void
    {
        $message = $exception instanceof RagException ? $exception->getMessage() : 'Indexing gagal atau melewati batas waktu. Periksa koneksi API; untuk dokumen besar, pecah menjadi modul lebih kecil lalu jalankan indexing ulang.';
        $this->current()->update(['status_indexing' => 'failed', 'indexing_error' => mb_substr($message, 0, 500)]);
        Log::warning('RAG indexing failed', ['module_id' => $this->moduleId, 'error_type' => $exception ? get_class($exception) : 'timeout']);
    }
}
