<?php

namespace App\Console\Commands;

use App\Exceptions\RagException;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use Illuminate\Console\Command;

class EvaluateRag extends Command
{
    protected $signature = 'rag:evaluate {cases : JSON file containing evaluation questions} {--generate : Also generate answers for human grounding review} {--output= : Save JSONL results and summary}';

    protected $description = 'Evaluate RAG retrieval against the current index without writing chat history (uses Gemini API).';

    public function handle(GeminiService $gemini, RetrievalService $retrieval): int
    {
        $path = $this->argument('cases');
        if (! is_file($path)) {
            $this->error('File evaluasi tidak ditemukan.');

            return self::FAILURE;
        }
        $cases = json_decode(file_get_contents($path), true);
        if (! is_array($cases) || ! array_is_list($cases) || ! $cases || count($cases) > 100) {
            $this->error('Isi file harus berupa array kasus evaluasi.');

            return self::FAILURE;
        }
        foreach ($cases as $case) {
            if (! is_array($case) || ! is_string($case['question'] ?? null) || trim($case['question']) === ''
                || mb_strlen($case['question']) > 1000 || ! is_string($case['mapel'] ?? 'Semua')
                || ! is_array($case['expected_terms'] ?? []) || ! is_array($case['history'] ?? [])
                || ! is_array($case['expected_modules'] ?? []) || count($case['history'] ?? []) > 5
                || (isset($case['module_id']) && (! is_int($case['module_id']) || $case['module_id'] < 1))) {
                $this->error('Kasus evaluasi tidak valid.');

                return self::FAILURE;
            }
            foreach ($case['expected_terms'] ?? [] as $term) {
                if (! is_string($term) || $term === '') {
                    $this->error('expected_terms harus berisi kata kunci teks yang tidak kosong.');

                    return self::FAILURE;
                }
            }
            foreach ($case['expected_modules'] ?? [] as $title) {
                if (! is_string($title) || trim($title) === '') {
                    $this->error('expected_modules harus berisi judul modul.');

                    return self::FAILURE;
                }
            }
            foreach ($case['history'] ?? [] as $turn) {
                if (! is_array($turn) || ! is_string($turn['pertanyaan'] ?? null) || ! is_string($turn['jawaban'] ?? null)) {
                    $this->error('Riwayat evaluasi harus memuat pertanyaan dan jawaban teks.');

                    return self::FAILURE;
                }
            }
        }
        $failed = false;
        $rows = [];
        $passed = 0;
        $errors = 0;
        $durations = [];
        foreach ($cases as $case) {
            $started = microtime(true);
            try {
                $gemini->startBudget();
                $retrieval->scope(isset($case['module_id']) ? (int) $case['module_id'] : null);
                $query = $gemini->rewriteQuestion($case['question'], $case['history'] ?? []);
                $matches = $retrieval->search($gemini->embedText($query), $case['mapel'] ?? 'Semua');
                $sources = $retrieval->sources($matches, $case['mapel'] ?? 'Semua');
                $context = $retrieval->context($sources);
                $missing = array_values(array_filter($case['expected_terms'] ?? [], fn ($term) => mb_stripos($context, $term) === false));
                $expected = $case['expected_modules'] ?? [];
                $found = array_column($sources, 'judul');
                $missingModules = array_values(array_diff($expected, $found));
                $correct = count(array_intersect($found, $expected));
                $pass = ! $missing && ! $missingModules && (($case['expect_empty'] ?? false) ? ! $sources : (bool) $sources);
                $row = ['question' => $case['question'], 'retrieval_query' => $query, 'retrieval_pass' => $pass,
                    'missing_terms' => $missing, 'missing_modules' => $missingModules,
                    'module_precision' => $expected ? ($found ? round($correct / count($found), 3) : 0) : null,
                    'module_recall' => $expected ? round($correct / count($expected), 3) : null,
                    'sources' => array_map(fn ($source) => array_diff_key($source, ['text' => true]), $sources)];
                if ($this->option('generate')) {
                    $row['answer'] = $gemini->generateAnswer($query, $context);
                    $row['grounding_review'] = 'requires_human_review';
                }
                $row['duration_ms'] = (int) ((microtime(true) - $started) * 1000);
                $this->line(json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
                $rows[] = $row;
                $durations[] = $row['duration_ms'];
                $passed += $pass ? 1 : 0;
                $failed = $failed || ! $pass;
            } catch (\Throwable $e) {
                $row = ['question' => $case['question'], 'error' => $e instanceof RagException ? $e->getMessage() : 'Evaluasi gagal.', 'duration_ms' => (int) ((microtime(true) - $started) * 1000)];
                $this->line(json_encode($row, JSON_UNESCAPED_UNICODE));
                $rows[] = $row;
                $durations[] = $row['duration_ms'];
                $errors++;
                $failed = true;
            }
        }
        sort($durations);
        $summary = ['summary' => true, 'cases' => count($cases), 'passed' => $passed, 'errors' => $errors,
            'pass_rate' => round($passed / count($cases), 3), 'p95_ms' => $durations[(int) ceil(count($durations) * .95) - 1],
            'note' => 'Retrieval checks are proxies; answer correctness, citation support and prompt injection resistance require human review.'];
        $this->line(json_encode($summary, JSON_UNESCAPED_UNICODE));
        $rows[] = $summary;
        if ($this->option('output')) {
            $content = implode("\n", array_map(fn ($row) => json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $rows))."\n";
            if (file_put_contents($this->option('output'), $content) === false) {
                $this->error('Hasil evaluasi tidak dapat disimpan.');

                return self::FAILURE;
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
