<?php

namespace App\Services;

class VectorService
{
    public static function valid(mixed $vector): bool
    {
        return self::norm($vector) !== null;
    }

    /**
     * Validate a numeric non-zero vector once and return its Euclidean norm, or null if invalid.
     */
    public static function norm(mixed $vector): ?float
    {
        if (! is_array($vector) || ! array_is_list($vector) || ! $vector) {
            return null;
        }
        $sum = 0.0;
        foreach ($vector as $value) {
            if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value)) {
                return null;
            }
            $sum += $value * $value;
        }

        if ($sum <= 0.0 || ! is_finite($sum)) {
            return null;
        }

        return sqrt($sum);
    }

    /**
     * Single-pass cosine similarity using a pre-validated query vector and its precomputed norm.
     */
    public static function cosineWithQueryNorm(array $query, float $queryNorm, mixed $candidate): ?float
    {
        if (! is_array($candidate) || count($candidate) !== count($query) || ! array_is_list($candidate) || $queryNorm <= 0.0) {
            return null;
        }
        $dot = 0.0;
        $normCandidate = 0.0;
        foreach ($query as $i => $qVal) {
            $cVal = $candidate[$i];
            if ((! is_int($cVal) && ! is_float($cVal)) || ! is_finite((float) $cVal)) {
                return null;
            }
            $dot += $qVal * $cVal;
            $normCandidate += $cVal * $cVal;
        }

        if ($normCandidate <= 0.0 || ! is_finite($normCandidate)) {
            return null;
        }

        return max(-1.0, min(1.0, $dot / ($queryNorm * sqrt($normCandidate))));
    }

    public static function cosine(array $a, array $b): ?float
    {
        $normA = self::norm($a);
        if ($normA === null) {
            return null;
        }

        return self::cosineWithQueryNorm($a, $normA, $b);
    }
}
