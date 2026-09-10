<?php

namespace App\Services;

use App\Models\ModuleChunk;
use Illuminate\Database\Eloquent\Builder;

class RetrievalService
{
    public function eligible(string $mapel = 'Semua'): Builder
    {
        return ModuleChunk::whereHas('module', function ($query) use ($mapel) {
            $query->available();
            if ($mapel !== 'Semua') {
                $query->where('mapel', $mapel);
            }
        })->where(function ($query) {
            // Existing indexes used this same hard-coded model; validate their vectors on read.
            $query->whereNull('embedding_model')->orWhere('embedding_model', GeminiService::EMBEDDING_MODEL);
        })->whereNotNull('embedding_vector')->where('chunk_text', '!=', '');
    }

    public function search(array $vector, string $mapel = 'Semua'): array
    {
        $best = [];
        $seen = [];
        $limit = max(1, (int) config('rag.top_k', 3));
        foreach ($this->eligible($mapel)->with('module')->lazyById(100) as $chunk) {
            $candidate = $chunk->embedding_vector;
            if (! is_array($candidate) || ($chunk->embedding_dimensions !== null && (int) $chunk->embedding_dimensions !== count($candidate))) {
                continue;
            }
            $score = VectorService::cosine($vector, $candidate);
            if ($score === null || $score < config('rag.similarity_threshold', 0.65)) {
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
            $source['text'] = $neighbors->pluck('chunk_text')->implode("\n");
            $source['chunk_ids'] = $neighbors->pluck('id')->values()->all();
            foreach ($source['chunk_ids'] as $id) {
                $used[$id] = true;
            }
            $sources[] = $source;
        }

        return $sources;
    }

    public function source(ModuleChunk $chunk, ?float $score = null): array
    {
        return [
            'chunk_id' => $chunk->id, 'module_id' => $chunk->module_id,
            'judul' => $chunk->module->judul, 'mapel' => $chunk->module->mapel,
            'kb_nomor' => $chunk->module->kb_nomor, 'chunk_index' => $chunk->chunk_index,
            'text' => $chunk->chunk_text, 'similarity' => $score,
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
