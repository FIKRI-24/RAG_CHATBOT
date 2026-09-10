<?php

namespace App\Services;

class VectorService
{
    public static function valid(mixed $vector): bool
    {
        if (! is_array($vector) || ! array_is_list($vector) || ! $vector) {
            return false;
        }
        $norm = 0.0;
        foreach ($vector as $value) {
            if ((! is_int($value) && ! is_float($value)) || ! is_finite((float) $value)) {
                return false;
            }
            $norm += $value * $value;
        }

        return $norm > 0 && is_finite($norm);
    }

    public static function cosine(array $a, array $b): ?float
    {
        if (count($a) !== count($b) || ! self::valid($a) || ! self::valid($b)) {
            return null;
        }
        $dot = $normA = $normB = 0.0;
        foreach ($a as $i => $value) {
            $dot += $value * $b[$i];
            $normA += $value * $value;
            $normB += $b[$i] * $b[$i];
        }

        return max(-1.0, min(1.0, $dot / (sqrt($normA) * sqrt($normB))));
    }
}
