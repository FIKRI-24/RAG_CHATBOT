<?php

namespace App\Console\Commands;

use App\Models\Module;
use App\Services\GeminiService;
use App\Services\VectorService;
use Illuminate\Console\Command;

class CheckRagIndex extends Command
{
    protected $signature = 'rag:check-index {--strict : Fail if a ready module has no valid chunks or contains incompatible vectors}';

    protected $description = 'Check local index integrity without sending document text or calling an AI API.';

    public function handle(): int
    {
        $failed = false;
        foreach (Module::orderBy('id')->cursor() as $module) {
            $valid = 0;
            $invalid = 0;
            $dimensions = [];
            foreach ($module->chunks()->lazyById(100) as $chunk) {
                $vector = $chunk->embedding_vector;
                if (trim($chunk->chunk_text) === '' || ! VectorService::valid($vector)
                    || ($chunk->embedding_model !== null && $chunk->embedding_model !== GeminiService::EMBEDDING_MODEL)
                    || ($chunk->embedding_dimensions !== null && (int) $chunk->embedding_dimensions !== count($vector))) {
                    $invalid++;
                } else {
                    $valid++;
                    $dimensions[count($vector)] = true;
                }
            }
            $ready = Module::available()->whereKey($module->id)->exists();
            $problem = $invalid > 0 || count($dimensions) > 1 || ($ready && $valid === 0);
            $failed = $failed || $problem;
            $this->line(json_encode(['module_id' => $module->id, 'status' => $module->status_indexing,
                'available' => $ready, 'valid_chunks' => $valid, 'invalid_chunks' => $invalid,
                'dimensions' => array_keys($dimensions), 'integrity_pass' => ! $problem]));
        }

        return $this->option('strict') && $failed ? self::FAILURE : self::SUCCESS;
    }
}
