<?php

namespace App\Console\Commands;

use App\Exceptions\RagException;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use Illuminate\Console\Command;

class EvaluateRag extends Command
{
    protected $signature = 'rag:evaluate {cases : JSON file containing evaluation questions} {--generate : Also generate answers for human grounding review}';

    protected $description = 'Evaluate RAG retrieval against the current index without writing chat history (uses Gemini API).';

    public function handle(GeminiService $gemini, RetrievalService $retrieval): int
    {
        $path = $this->argument('cases');
        if (! is_file($path)) {
            $this->error('File evaluasi tidak ditemukan.');

            return self::FAILURE;
        }
        $cases = json_decode(file_get_contents($path), true);
        if (! is_array($cases) || ! array_is_list($cases) || ! $cases) {
            $this->error('Isi file harus berupa array kasus evaluasi.');

            return self::FAILURE;
        }
        foreach ($cases as $case) {
            if (! is_array($case) || ! is_string($case['question'] ?? null) || trim($case['question']) === ''
                || mb_strlen($case['question']) > 1000 || ! is_string($case['mapel'] ?? 'Semua')
                || ! is_array($case['expected_terms'] ?? []) || ! is_array($case['history'] ?? [])) {
                $this->error('Kasus evaluasi tidak valid.');

                return self::FAILURE;
            }
            foreach ($case['expected_terms'] ?? [] as $term) {
                if (! is_string($term) || $term === '') {
                    $this->error('expected_terms harus berisi kata kunci teks yang tidak kosong.');

                    return self::FAILURE;
                }
            }
        }
        $failed = false;
        foreach ($cases as $case) {
            $started = microtime(true);
            try {
                $query = $gemini->rewriteQuestion($case['question'], $case['history'] ?? []);
                $matches = $retrieval->search($gemini->embedText($query), $case['mapel'] ?? 'Semua');
                $sources = $retrieval->sources($matches, $case['mapel'] ?? 'Semua');
                $context = $retrieval->context($sources);
                $missing = array_values(array_filter($case['expected_terms'] ?? [], fn ($term) => mb_stripos($context, $term) === false));
                $pass = ! $missing && (! ($case['expect_empty'] ?? false) || ! $sources);
                $row = ['question' => $case['question'], 'retrieval_query' => $query, 'retrieval_pass' => $pass,
                    'missing_terms' => $missing, 'sources' => array_map(fn ($source) => array_diff_key($source, ['text' => true]), $sources)];
                if ($this->option('generate')) {
                    $row['answer'] = $gemini->generateAnswer($query, $context);
                }
                $row['duration_ms'] = (int) ((microtime(true) - $started) * 1000);
                $this->line(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $failed = $failed || ! $pass;
            } catch (\Throwable $e) {
                $this->line(json_encode(['question' => $case['question'], 'error' => $e instanceof RagException ? $e->getMessage() : 'Evaluasi gagal.', 'duration_ms' => (int) ((microtime(true) - $started) * 1000)], JSON_UNESCAPED_UNICODE));
                $failed = true;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
