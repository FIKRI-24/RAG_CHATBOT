<?php

namespace Tests\Support;

use App\Services\GeminiService;

/** Explicit offline fixture, loaded only by the isolated E2E bootstrap. */
class FakeGeminiService extends GeminiService
{
    public function embedText(string $text): array
    {
        return [1, 0];
    }

    public function rewriteQuestion(string $question, array $history): string
    {
        return $question;
    }

    public function generateAnswer(string $question, string $context): string
    {
        return 'VLAN memisahkan jaringan secara logis [1].';
    }

    public function structuredQuiz(string $context): array
    {
        return ['question' => 'Apa fungsi VLAN?', 'answer_key' => 'Memisahkan jaringan secara logis.', 'rubric' => ['Menyebut pemisahan jaringan secara logis']];
    }

    public function assessQuiz(array $quiz, string $answer, string $context): array
    {
        return ['score' => str_contains(strtolower($answer), 'logis') ? 100 : 0, 'feedback' => 'Penilaian berdasarkan pemisahan jaringan secara logis.', 'criteria' => ['Pemisahan logis']];
    }
}
