<?php

namespace App\Services;

use InvalidArgumentException;

class ChunkingService
{
    /** Sizes use Unicode characters; every iteration advances. */
    public function chunk(string $text, int $chunkSize = 500, int $overlap = 50): array
    {
        if ($chunkSize < 2 || $overlap < 0 || $overlap >= $chunkSize) {
            throw new InvalidArgumentException('Ukuran chunk harus lebih besar dari overlap.');
        }
        $text = str_replace(["\r\n", "\r"], "\n", $text);
        $text = trim(preg_replace('/\n{3,}/u', "\n\n", preg_replace('/[\t ]+/u', ' ', $text)));
        $chunks = [];
        $start = 0;
        $length = mb_strlen($text);
        while ($start < $length) {
            $window = mb_substr($text, $start, $chunkSize);
            $take = mb_strlen($window);
            if ($start + $take < $length) {
                foreach (["\n\n", '. ', ' '] as $separator) {
                    $boundary = mb_strrpos($window, $separator);
                    if ($boundary !== false && $boundary >= max(intdiv($chunkSize, 2), $overlap + 1)) {
                        $take = $boundary + mb_strlen($separator);
                        break;
                    }
                }
            }
            $piece = trim(mb_substr($text, $start, $take));
            if ($piece !== '') {
                $chunks[] = $piece;
            }
            if ($start + $take >= $length) {
                break;
            }
            $start += max(1, $take - $overlap);
        }

        return $chunks;
    }
}
