<?php

namespace App\Services;

use App\Models\ModuleChunk;
use Illuminate\Database\Eloquent\Builder;

class RetrievalService
{
    private ?int $moduleId = null;

    public function scope(?int $moduleId): void
    {
        $this->moduleId = $moduleId;
    }

    public function eligible(string $mapel = 'Semua'): Builder
    {
        return ModuleChunk::when($this->moduleId, fn ($query) => $query->where('module_id', $this->moduleId))->whereHas('module', function ($query) use ($mapel) {
            $query->available();
            if ($mapel !== 'Semua') {
                $query->where('mapel', $mapel);
            }
        })->where(function ($query) {
            // Existing indexes used this same hard-coded model; validate their vectors on read.
            $query->whereNull('embedding_model')->orWhere('embedding_model', GeminiService::EMBEDDING_MODEL);
        })->whereNotNull('embedding_vector')->where('chunk_text', '!=', '');
    }

    public function search(array $vector, string $mapel = 'Semua', string $queryText = ''): array
    {
        $best = [];
        $seen = [];
        $limit = max(1, (int) config('rag.top_k', 3));
        $threshold = (float) config('rag.similarity_threshold', 0.65);
        $keywords = $this->extractKeywords($queryText);
        foreach ($this->eligible($mapel)->with('module')->lazyById(100) as $chunk) {
            $candidate = $chunk->embedding_vector;
            if (! is_array($candidate) || ($chunk->embedding_dimensions !== null && (int) $chunk->embedding_dimensions !== count($candidate))) {
                continue;
            }
            $score = VectorService::cosine($vector, $candidate);
            if ($score === null) {
                continue;
            }
            if ($keywords !== [] && $score >= max(0.35, $threshold - 0.15)) {
                $score = min(1.0, $score + $this->lexicalBoost($chunk->chunk_text, $keywords));
            }
            if ($score < $threshold) {
                continue;
            }
            $hash = hash('sha256', preg_replace('/\s+/u', ' ', trim($chunk->chunk_text)));
            if (isset($seen[$hash])) {
                $duplicate = array_search($hash, array_column($best, 'hash'), true);
                if ($score <= $best[$duplicate]['similarity']) {
                    continue;
                }
                array_splice($best, $duplicate, 1);
            }
            $best[] = ['chunk' => $chunk, 'similarity' => $score, 'hash' => $hash];
            usort($best, fn ($a, $b) => $b['similarity'] <=> $a['similarity'] ?: $a['chunk']->id <=> $b['chunk']->id);
            $best = array_slice($best, 0, $limit);
            $seen = array_fill_keys(array_column($best, 'hash'), true);
        }

        return $best;
    }

    /** Expand a relevant hit by one neighboring chunk on each side, in the same module.
     * This preserves lists/steps at chunk boundaries without admitting unrelated search hits.
     * The score belongs to the anchor; chunk_ids records every part of the source window.
     */
    public function sources(array $matches, string $mapel = 'Semua'): array
    {
        $sources = [];
        $used = [];
        foreach ($matches as $match) {
            $chunk = $match['chunk'];
            if (isset($used[$chunk->id])) {
                continue;
            }
            $source = $this->source($chunk, $match['similarity']);
            $neighbors = $chunk->chunk_index === null ? $this->eligible($mapel)->whereKey($chunk->id)->get() : $this->eligible($mapel)
                ->where('module_id', $chunk->module_id)
                ->whereBetween('chunk_index', [max(0, $chunk->chunk_index - 1), $chunk->chunk_index + 1])
                ->orderBy('chunk_index')->get()->reject(fn ($item) => isset($used[$item->id]))
                ->filter(fn ($item) => VectorService::valid($item->embedding_vector)
                    && count($item->embedding_vector) === count($chunk->embedding_vector));
            if (! $neighbors->contains('id', $chunk->id)) {
                continue; // Index was replaced or module was withdrawn during retrieval.
            }
            $rawJoined = $neighbors->pluck('chunk_text')->implode("\n");
            $source['text'] = $this->mergeChunkTexts($neighbors->pluck('chunk_text')->all());
            $source['content_hash'] = hash('sha256', $rawJoined);
            $source['chunk_ids'] = $neighbors->pluck('id')->values()->all();
            foreach ($source['chunk_ids'] as $id) {
                $used[$id] = true;
            }
            $sources[] = $source;
        }

        return $sources;
    }

    /**
     * Merge consecutive neighbor chunk texts while stripping duplicated boundary overlap
     * created by ChunkingService (default 50 chars).
     */
    public function mergeChunkTexts(array $texts): string
    {
        $texts = array_values(array_filter(array_map('strval', $texts), fn (string $t) => $t !== ''));
        if ($texts === []) {
            return '';
        }

        $merged = array_shift($texts);
        foreach ($texts as $next) {
            $overlapLen = $this->detectBoundaryOverlap($merged, $next);
            if ($overlapLen > 0) {
                $continuation = mb_substr($next, $overlapLen);
                if ($continuation !== '') {
                    $merged .= $continuation;
                }
            } else {
                $merged .= "\n".$next;
            }
        }

        return $merged;
    }

    private function detectBoundaryOverlap(string $left, string $right, int $maxOverlap = 80, int $minOverlap = 12): int
    {
        $max = min($maxOverlap, mb_strlen($left), mb_strlen($right));
        for ($len = $max; $len >= $minOverlap; $len--) {
            if (mb_substr($left, -$len) === mb_substr($right, 0, $len)) {
                return $len;
            }
        }

        return 0;
    }

    /**
     * Extract domain-relevant keywords from the student's question (excluding generic Indonesian stopwords).
     */
    public function extractKeywords(string $queryText): array
    {
        $normalized = mb_strtolower(trim($queryText));
        if ($normalized === '') {
            return [];
        }

        $tokens = preg_split('/[^\p{L}\p{N}._-]+/u', $normalized, -1, PREG_SPLIT_NO_EMPTY);
        if (! is_array($tokens)) {
            return [];
        }

        $stopwords = [
            'apa', 'apakah', 'siapa', 'kapan', 'dimana', 'mengapa', 'kenapa', 'bagaimana', 'gimana', 'berapa',
            'jelaskan', 'sebutkan', 'uraikan', 'tuliskan', 'pengertian', 'definisi', 'maksud', 'contoh', 'contohnya',
            'fungsi', 'fungsinya', 'tujuan', 'manfaat', 'cara', 'langkah', 'perbedaan', 'bedanya', 'kelebihan', 'kekurangan',
            'yang', 'dan', 'atau', 'pada', 'dalam', 'untuk', 'dengan', 'dari', 'ke', 'di', 'itu', 'ini', 'tersebut', 'tsb',
            'adalah', 'ialah', 'merupakan', 'sebagai', 'oleh', 'karena', 'jika', 'kalau', 'bisa', 'dapat', 'akan',
            'sudah', 'belum', 'tidak', 'bukan', 'saja', 'lebih', 'lanjut', 'materi', 'modul', 'topik', 'luar',
            'tentang', 'seputar', 'kegiatan', 'belajar', 'siswa', 'guru', 'sistem', 'tolong', 'bantu',
        ];
        $stopLookup = array_fill_keys($stopwords, true);

        $keywords = [];
        foreach ($tokens as $token) {
            $token = trim($token, '._-');
            if (mb_strlen($token) < 3 || isset($stopLookup[$token])) {
                continue;
            }
            $keywords[$token] = true;
        }

        return array_keys($keywords);
    }

    private function lexicalBoost(string $chunkText, array $keywords): float
    {
        if ($keywords === []) {
            return 0.0;
        }

        $haystack = mb_strtolower($chunkText);
        $matched = 0;
        foreach ($keywords as $kw) {
            if (mb_strpos($haystack, $kw) !== false) {
                $matched++;
            }
        }

        if ($matched === 0) {
            return 0.0;
        }

        $ratio = $matched / count($keywords);

        return min(0.12, (0.04 * $matched) + (0.06 * $ratio));
    }

    public function source(ModuleChunk $chunk, ?float $score = null): array
    {
        return [
            'chunk_id' => $chunk->id, 'module_id' => $chunk->module_id,
            'judul' => $chunk->module->judul, 'mapel' => $chunk->module->mapel,
            'kb_nomor' => $chunk->module->kb_nomor, 'chunk_index' => $chunk->chunk_index,
            'text' => $chunk->chunk_text, 'similarity' => $score,
            'indexing_version' => $chunk->module->indexing_version,
            'content_hash' => hash('sha256', $chunk->chunk_text),
        ];
    }

    public function context(array $sources): string
    {
        $parts = [];
        foreach ($sources as $i => $source) {
            $parts[] = '['.($i + 1).'] '.$source['judul'].' | '.$source['mapel'].' | '.$source['kb_nomor']."\n".$source['text'];
        }

        return implode("\n\n", $parts);
    }
}
