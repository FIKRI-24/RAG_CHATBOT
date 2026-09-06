<?php

namespace App\Jobs;

use App\Models\Module;
use App\Models\ModuleChunk;
use App\Services\ChunkingService;
use App\Services\DocumentExtractorService;
use App\Services\GeminiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessModuleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 300;
    public $tries = 2;
    public $deleteWhenMissingModels = true;

    protected Module $module;

    /**
     * Create a new job instance.
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * Execute the job.
     */
    public function handle(
        DocumentExtractorService $extractor,
        ChunkingService $chunker,
        GeminiService $gemini
    ): void {
        // Cek apakah modul masih ada atau telah dihapus sebelum job diproses
        if (!$this->module || !$this->module->exists) {
            return;
        }

        try {
            $this->module->update(['status_indexing' => 'processing']);

            // Get full file path
            $fullPath = Storage::disk('local')->path($this->module->file_path);

            // 1. Extract Text
            $text = $extractor->extract($fullPath);

            // 2. Chunk Text
            $chunks = $chunker->chunk($text);

            // 3 & 4. Embed & Save each chunk
            foreach ($chunks as $chunkText) {
                // Ensure text is not empty before sending to API
                if (empty(trim($chunkText))) {
                    continue;
                }

                $embedding = $gemini->embedText($chunkText);

                ModuleChunk::create([
                    'module_id' => $this->module->id,
                    'chunk_text' => $chunkText,
                    'embedding_vector' => $embedding,
                ]);
            }

            // Update status to completed jika modul masih ada
            if ($this->module && $this->module->exists) {
                $this->module->update(['status_indexing' => 'completed']);
            }

        } catch (\Throwable $e) {
            if ($this->module && $this->module->exists) {
                $this->module->update(['status_indexing' => 'failed']);
            }
            Log::error("Proses indexing gagal untuk modul ID " . ($this->module->id ?? 'unknown') . ": " . $e->getMessage(), [
                'exception' => $e
            ]);
            
            // Rethrow so the queue worker knows it failed
            throw $e;
        }
    }
}
