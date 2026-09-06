<?php

namespace App\Services;

class ChunkingService
{
    /**
     * Split extracted text into smaller chunks for embedding.
     *
     * @param string $text
     * @param int $chunkSize
     * @param int $overlap
     * @return array
     */
    public function chunk(string $text, int $chunkSize = 500, int $overlap = 50): array
    {
        // Standardize newlines and clean up excessive whitespace while preserving paragraphs
        $text = str_replace("\r\n", "\n", $text);
        $text = preg_replace('/[ \t]+/', ' ', $text); // multiple spaces to one
        $text = preg_replace('/\n{3,}/', "\n\n", trim($text));

        $paragraphs = explode("\n\n", $text);
        
        $chunks = [];
        $currentChunk = "";
        
        foreach ($paragraphs as $paragraph) {
            $paragraph = trim(str_replace("\n", " ", $paragraph));
            if (empty($paragraph)) {
                continue;
            }
            
            if (empty($currentChunk)) {
                $currentChunk = $paragraph;
            } elseif (strlen($currentChunk) + strlen($paragraph) + 1 <= $chunkSize) {
                $currentChunk .= " " . $paragraph;
            } else {
                $chunks[] = $currentChunk;
                
                // Generate overlap
                $overlapText = substr($currentChunk, -$overlap);
                $firstSpace = strpos($overlapText, ' ');
                if ($firstSpace !== false) {
                    $overlapText = substr($overlapText, $firstSpace + 1);
                }
                
                $currentChunk = $overlapText . " " . $paragraph;
                
                // If a single paragraph is too large
                while (strlen($currentChunk) > $chunkSize) {
                    // Try to break at a space
                    $sub = substr($currentChunk, 0, $chunkSize);
                    $lastSpace = strrpos($sub, ' ');
                    
                    if ($lastSpace !== false && $lastSpace > 0) {
                        $chunks[] = trim(substr($currentChunk, 0, $lastSpace));
                        $currentChunk = substr($currentChunk, $lastSpace + 1);
                    } else {
                        $chunks[] = $sub;
                        $currentChunk = substr($currentChunk, $chunkSize);
                    }
                    
                    if (strlen($currentChunk) > 0) {
                        // Apply overlap for split chunks
                        $prev = end($chunks);
                        $overlapText = substr($prev, -$overlap);
                        $firstSpace = strpos($overlapText, ' ');
                        if ($firstSpace !== false) {
                            $overlapText = substr($overlapText, $firstSpace + 1);
                        }
                        $currentChunk = $overlapText . " " . $currentChunk;
                    }
                }
            }
        }
        
        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }
        
        return $chunks;
    }
}
