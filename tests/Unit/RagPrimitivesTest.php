<?php

namespace Tests\Unit;

use App\Services\ChunkingService;
use App\Services\VectorService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class RagPrimitivesTest extends TestCase
{
    public function test_long_first_paragraph_and_unicode_never_exceed_limit(): void
    {
        foreach ([str_repeat('materi ', 300), str_repeat('é网络', 500), str_repeat('x', 2000)."\n\nakhir"] as $text) {
            $chunks = (new ChunkingService)->chunk($text);
            $this->assertGreaterThan(1, count($chunks));
            foreach ($chunks as $chunk) {
                $this->assertLessThanOrEqual(500, mb_strlen($chunk));
                $this->assertTrue(mb_check_encoding($chunk, 'UTF-8'));
            }
        }
    }

    public function test_overlap_and_final_characters_are_preserved(): void
    {
        $text = implode('', range('a', 'z'));
        $chunks = (new ChunkingService)->chunk($text, 10, 3);
        $rebuilt = array_shift($chunks);
        foreach ($chunks as $chunk) {
            $this->assertSame(substr($rebuilt, -3), substr($chunk, 0, 3));
            $rebuilt .= substr($chunk, 3);
        }
        $this->assertSame($text, $rebuilt);
        $this->assertSame([], (new ChunkingService)->chunk(" \n\t "));
        $this->assertSame(['0'], (new ChunkingService)->chunk('0'));
    }

    public function test_invalid_overlap_is_rejected_instead_of_looping(): void
    {
        $this->expectException(InvalidArgumentException::class);
        (new ChunkingService)->chunk('materi', 50, 50);
    }

    public function test_invalid_vectors_are_rejected(): void
    {
        foreach ([[], [0, 0], ['1', 0], [NAN, 1], [INF, 1], ['x' => 1]] as $vector) {
            $this->assertFalse(VectorService::valid($vector));
        }
        $this->assertNull(VectorService::cosine([1, 0], [1, 0, 100]));
        $this->assertSame(1.0, VectorService::cosine([1, 0], [1, 0]));
        $this->assertSame(0.0, VectorService::cosine([1, 0], [0, 1]));
    }
}
