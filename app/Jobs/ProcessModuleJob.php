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
use Illuminate\Support\Facades\File;
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

    public ?string $oldFilePath;

    public function __construct(Module $module, ?string $oldFilePath = null)
    {
        $this->moduleId = $module->id;
        $this->version = $module->indexing_version;
        $this->filePath = $module->file_path;
        $this->oldFilePath = $oldFilePath;
        $this->onConnection('rag')->onQueue('rag')->afterCommit();
    }

    private function current()
    {
        return Module::whereKey($this->moduleId)->where('indexing_version', $this->version)
            ->where('file_path', $this->filePath)
            ->where(function ($query) {
                $query->where('status_indexing', '!=', 'completed')
                    ->orWhereIn('reindexing_status', ['pending', 'processing', 'failed'])
                    ->orWhereDoesntHave('chunks');
            });
    }

    public function handle(DocumentExtractorService $extractor, ChunkingService $chunker, GeminiService $gemini): void
    {
        $startedLiveReindex = $this->current()
            ->where('status_indexing', 'completed')
            ->whereHas('chunks')
            ->update(['reindexing_status' => 'processing', 'indexing_error' => null]);

        if (! $startedLiveReindex) {
            $startedInitial = $this->current()
                ->where(function ($query) {
                    $query->where('status_indexing', '!=', 'completed')
                        ->orWhereDoesntHave('chunks');
                })
                ->update(['status_indexing' => 'processing', 'reindexing_status' => null, 'indexing_error' => null]);

            if (! $startedInitial) {
                return;
            }
        }
        $stagingPath = $this->stagingPath();
        $writer = null;
        $reader = null;
        try {
            File::ensureDirectoryExists(dirname($stagingPath), 0700);
            $text = $extractor->extract(Storage::disk('local')->path($this->filePath));
            $chunks = $chunker->chunk($text, config('rag.chunk_size'), config('rag.chunk_overlap'));
            if (! $chunks) {
                throw RagException::permanent('Dokumen tidak memiliki teks terbaca. Gunakan PDF dengan lapisan teks atau DOCX; PDF scan perlu OCR terlebih dahulu.');
            }
            if (count($chunks) > (int) config('rag.max_chunks_per_module', 200)) {
                throw RagException::permanent('Dokumen terlalu panjang untuk satu modul. Pisahkan per kegiatan belajar sebelum diindeks.');
            }

            // Resume from any verified batches already embedded on a previous attempt of this exact version.
            $checkpoint = $this->loadStagingCheckpoint($stagingPath, $chunks);
            $startIndex = $checkpoint['count'];
            $dimensions = $checkpoint['dimensions'];

            $gemini->startBudget((int) config('rag.index_budget_seconds', 270));
            $gemini->setUsageScope('indexing');

            $batchSize = max(1, min(100, (int) config('rag.embedding_batch_size', 25)));
            if ($startIndex < count($chunks)) {
                $writer = fopen($stagingPath, $startIndex > 0 ? 'ab' : 'wb');
                if ($writer === false) {
                    throw new RagException('Penyimpanan sementara indexing tidak tersedia. Periksa ruang penyimpanan server.');
                }

                $totalChunks = count($chunks);
                for ($offset = $startIndex; $offset < $totalChunks; $offset += $batchSize) {
                    if (! $this->current()->exists()) {
                        fclose($writer);
                        $writer = null;
                        $this->cleanupStaging($stagingPath);

                        return;
                    }

                    $batch = array_slice($chunks, $offset, $batchSize);
                    $vectors = count($batch) === 1
                        ? [$gemini->embedText($batch[0])]
                        : $gemini->embedBatch($batch);

                    if (count($vectors) !== count($batch)) {
                        throw new RagException('Layanan embedding batch mengembalikan jumlah vektor tidak konsisten.');
                    }

                    foreach ($batch as $i => $chunkText) {
                        $vector = $vectors[$i] ?? null;
                        if (! VectorService::valid($vector) || ($dimensions !== null && count($vector) !== $dimensions)) {
                            throw new RagException('Embedding dokumen tidak valid atau dimensinya tidak konsisten.');
                        }
                        $dimensions = count($vector);
                        $row = [
                            'chunk_text' => $chunkText,
                            'embedding_vector' => $vector,
                            'embedding_model' => GeminiService::EMBEDDING_MODEL,
                            'embedding_dimensions' => $dimensions,
                            'chunk_index' => $offset + $i,
                        ];
                        $encoded = json_encode($row, JSON_THROW_ON_ERROR)."\n";
                        if (fwrite($writer, $encoded) !== strlen($encoded)) {
                            throw new RagException('Penyimpanan sementara indexing penuh. Indeks lama tetap tersimpan.');
                        }
                    }
                    fflush($writer);
                }

                fclose($writer);
                $writer = null;
            }

            $reader = fopen($stagingPath, 'rb');
            if ($reader === false) {
                throw new RagException('Indeks sementara tidak dapat dibaca.');
            }

            // Publish only a complete index. Stale/repeated jobs cannot replace it.
            $previousIndexedPath = null;
            $expectedCount = count($chunks);
            DB::transaction(function () use ($reader, $expectedCount, &$previousIndexedPath) {
                $module = $this->current()->lockForUpdate()->first();
                if (! $module) {
                    return;
                }
                $previousIndexedPath = $module->indexed_file_path;
                // Preserve the known reference of legacy chats before removing old IDs.
                foreach ($module->chunks()->with('module')->lazyById(100) as $oldChunk) {
                    ChatHistory::where('referensi_chunk_id', $oldChunk->id)->whereNull('sources')
                        ->update(['sources' => json_encode([(new RetrievalService)->source($oldChunk)], JSON_UNESCAPED_UNICODE)]);
                }
                $module->chunks()->delete();
                if (! rewind($reader)) {
                    throw new RagException('Indeks sementara tidak dapat dibaca.');
                }
                $writtenCount = 0;
                while (($line = fgets($reader)) !== false) {
                    if (trim($line) === '') {
                        continue;
                    }
                    $module->chunks()->create(json_decode($line, true, 512, JSON_THROW_ON_ERROR));
                    $writtenCount++;
                }
                if (! feof($reader) || $writtenCount !== $expectedCount) {
                    throw new RagException('Pembacaan indeks sementara tidak lengkap.');
                }
                $module->update([
                    'status_indexing' => 'completed',
                    'reindexing_status' => null,
                    'indexing_error' => null,
                    'indexed_file_path' => $this->filePath,
                ]);
            });
            fclose($reader);
            $reader = null;
            $this->cleanupStaging($stagingPath);

            $storageCleanup = app(\App\Services\StoredFileService::class);
            foreach (array_unique(array_filter([$this->oldFilePath, $previousIndexedPath])) as $stalePath) {
                if ($stalePath !== $this->filePath) {
                    $storageCleanup->delete($stalePath, 'local');
                }
            }
        } catch (Throwable $e) {
            if (is_resource($writer)) {
                fclose($writer);
                $writer = null;
            }
            if (is_resource($reader)) {
                fclose($reader);
                $reader = null;
            }

            $isPermanent = $e instanceof RagException && ! $e->retryable;
            $willRetry = ! $isPermanent && $this->job !== null && $this->attempts() < $this->tries;

            if ($willRetry) {
                Log::warning('RAG indexing attempt failed; retaining checkpoint for retry', [
                    'module_id' => $this->moduleId,
                    'attempt' => $this->attempts(),
                    'max_tries' => $this->tries,
                    'error_type' => get_class($e),
                ]);
                throw $e;
            }

            $this->cleanupStaging($stagingPath);

            if ($isPermanent && $this->job !== null) {
                $this->fail($e);

                return;
            }

            $this->markFailed($e);
            throw $e;
        } finally {
            if (is_resource($writer)) {
                fclose($writer);
            }
            if (is_resource($reader)) {
                fclose($reader);
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        $this->cleanupStaging($this->stagingPath());
        $this->markFailed($exception);
    }

    private function stagingPath(): string
    {
        $safeVersion = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) ($this->version ?: 'v0'));

        return storage_path("app/private/rag-staging/module_{$this->moduleId}_{$safeVersion}.jsonl");
    }

    private function cleanupStaging(string $path): void
    {
        if (is_file($path)) {
            @unlink($path);
        }
    }

    /**
     * Validate any existing staged lines from a previous retry of the same version so already-embedded
     * batches are reused instead of consuming API quota again.
     *
     * @param  array<int, string>  $chunks
     * @return array{count: int, dimensions: ?int}
     */
    private function loadStagingCheckpoint(string $path, array $chunks): array
    {
        if (! is_file($path)) {
            return ['count' => 0, 'dimensions' => null];
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            $this->cleanupStaging($path);

            return ['count' => 0, 'dimensions' => null];
        }

        $validLines = [];
        $dimensions = null;
        $needsRewrite = false;
        $expectedIndex = 0;

        try {
            while (($line = fgets($handle)) !== false) {
                if (trim($line) === '') {
                    $needsRewrite = true;
                    continue;
                }
                $decoded = json_decode($line, true);
                if (! is_array($decoded)
                    || ($decoded['chunk_index'] ?? null) !== $expectedIndex
                    || ! array_key_exists($expectedIndex, $chunks)
                    || ($decoded['chunk_text'] ?? null) !== $chunks[$expectedIndex]
                    || ! VectorService::valid($decoded['embedding_vector'] ?? null)) {
                    $needsRewrite = true;
                    break;
                }
                $vecLen = count($decoded['embedding_vector']);
                if ($dimensions !== null && $vecLen !== $dimensions) {
                    $needsRewrite = true;
                    break;
                }
                $dimensions = $vecLen;
                $validLines[] = $line;
                $expectedIndex++;
            }
        } finally {
            fclose($handle);
        }

        if ($needsRewrite) {
            if ($validLines === []) {
                $this->cleanupStaging($path);

                return ['count' => 0, 'dimensions' => null];
            }
            file_put_contents($path, implode('', $validLines), LOCK_EX);
        }

        return ['count' => count($validLines), 'dimensions' => $dimensions];
    }

    private function markFailed(?Throwable $exception): void
    {
        $message = $exception instanceof RagException ? $exception->getMessage() : 'Indexing gagal atau melewati batas waktu. Periksa koneksi API; untuk dokumen besar, pecah menjadi modul lebih kecil lalu jalankan indexing ulang.';
        $truncated = mb_substr($message, 0, 500);

        $updatedLiveReindex = $this->current()
            ->where('status_indexing', 'completed')
            ->whereHas('chunks')
            ->update(['reindexing_status' => 'failed', 'indexing_error' => $truncated]);

        if (! $updatedLiveReindex) {
            $this->current()
                ->where(function ($query) {
                    $query->where('status_indexing', '!=', 'completed')
                        ->orWhereDoesntHave('chunks');
                })
                ->update(['status_indexing' => 'failed', 'reindexing_status' => null, 'indexing_error' => $truncated]);
        }

        Log::warning('RAG indexing failed', ['module_id' => $this->moduleId, 'error_type' => $exception ? get_class($exception) : 'timeout']);
    }
}
