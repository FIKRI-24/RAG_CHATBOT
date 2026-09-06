<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('gemini.api_key', '');
    }

    /**
     * Embed text using Gemini API.
     *
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function embedText(string $text): array
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-embedding-001:embedContent?key={$this->apiKey}";

        try {
            $response = Http::post($url, [
                'model' => 'models/gemini-embedding-001',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                return $response->json('embedding.values', []);
            }

            Log::error('Gemini embedText Error', ['response' => $response->json()]);
            throw new Exception("Gagal mendapatkan embedding dari Gemini API: " . $response->body());
            
        } catch (Exception $e) {
            Log::error('Gemini embedText Exception: ' . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memanggil Gemini API (Embedding): " . $e->getMessage());
        }
    }

    /**
     * Generate answer based on context using Gemini API.
     *
     * @param string $question
     * @param string $context
     * @return string
     * @throws Exception
     */
    public function generateAnswer(string $question, string $context): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
        }

        $models = array_unique([
            config('gemini.model', 'gemini-2.5-flash'),
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite-001',
        ]);

        $systemInstruction = "Anda adalah asisten pembelajaran TKJ. Jawab pertanyaan HANYA berdasarkan konteks materi yang diberikan. Jika jawaban tidak ditemukan dalam konteks, katakan bahwa materi tersebut belum tersedia dalam modul. Jawab dalam Bahasa Indonesia.";

        $contents = [
            [
                'parts' => [
                    [
                        'text' => "Konteks:\n{$context}\n\nPertanyaan:\n{$question}"
                    ]
                ]
            ]
        ];

        return $this->callApiWithRetry($models, $contents, $systemInstruction);
    }

    /**
     * Generate a quiz question based on context.
     *
     * @param string $context
     * @return string
     * @throws Exception
     */
    public function generateQuiz(string $context): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
        }

        $models = array_unique([
            config('gemini.model', 'gemini-2.5-flash'),
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite-001',
        ]);

        $systemInstruction = "Anda adalah guru TKJ yang interaktif. Buatlah 1 pertanyaan (bisa pilihan ganda atau essay singkat) untuk menguji pemahaman siswa berdasarkan materi yang diberikan. JANGAN berikan kunci jawabannya di dalam pertanyaan. Langsung berikan pertanyaannya secara jelas.";

        $contents = [
            [
                'parts' => [
                    ['text' => "Materi:\n{$context}"]
                ]
            ]
        ];

        return $this->callApiWithRetry($models, $contents, $systemInstruction);
    }

    /**
     * Grade a quiz answer based on context and question.
     *
     * @param string $question
     * @param string $answer
     * @param string $context
     * @return string
     * @throws Exception
     */
    public function gradeQuiz(string $question, string $answer, string $context): string
    {
        if (empty($this->apiKey)) {
            throw new Exception("Gemini API key is not configured.");
        }

        $models = array_unique([
            config('gemini.model', 'gemini-2.5-flash'),
            'gemini-2.5-flash',
            'gemini-2.0-flash-lite-001',
        ]);

        $systemInstruction = "Anda adalah guru TKJ. Evaluasi jawaban siswa terhadap soal yang Anda berikan berdasarkan materi. Beritahu apakah jawabannya Benar atau Salah, berikan apresiasi, dan berikan penjelasan singkat berdasarkan materi. Jawab dengan ramah dan memotivasi.";

        $contents = [
            [
                'parts' => [
                    ['text' => "Materi Acuan:\n{$context}\n\nSoal:\n{$question}\n\nJawaban Siswa:\n{$answer}"]
                ]
            ]
        ];

        return $this->callApiWithRetry($models, $contents, $systemInstruction);
    }

    /**
     * Helper method to call Gemini API with automatic retry and rate-limit handling.
     *
     * @param array $models
     * @param array $contents
     * @param string $systemInstruction
     * @return string
     * @throws Exception
     */
    protected function callApiWithRetry(array $models, array $contents, string $systemInstruction): string
    {
        $lastError = '';

        foreach ($models as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$this->apiKey}";

            for ($attempt = 1; $attempt <= 3; $attempt++) {
                try {
                    $body = [
                        'contents' => $contents
                    ];

                    if (!empty($systemInstruction)) {
                        $body['systemInstruction'] = [
                            'parts' => [['text' => $systemInstruction]]
                        ];
                    }

                    $response = Http::post($url, $body);

                    if ($response->successful()) {
                        $candidates = $response->json('candidates', []);
                        if (!empty($candidates) && isset($candidates[0]['content']['parts'][0]['text'])) {
                            return $candidates[0]['content']['parts'][0]['text'];
                        }
                        return "Maaf, tidak dapat menghasilkan jawaban saat ini.";
                    }

                    $statusCode = $response->status();
                    $lastError = $response->body();

                    // If 429 (Rate Limit / Quota Exceeded), wait briefly and retry
                    if ($statusCode === 429 && $attempt < 3) {
                        Log::info("Gemini API Rate Limit (429) for model {$model}, retrying in 2 seconds... (Attempt {$attempt})");
                        sleep(2);
                        continue;
                    }

                    break;

                } catch (Exception $e) {
                    $lastError = $e->getMessage();
                    Log::warning("Gemini API exception for model {$model}: {$lastError}");
                    break;
                }
            }
        }

        if (str_contains($lastError, '429') || str_contains($lastError, 'RESOURCE_EXHAUSTED')) {
            throw new Exception("Batas batas kecepatan Gemini API (Rate Limit) tercapai sementara. Silakan tunggu beberapa detik dan coba lagi.");
        }

        throw new Exception("Gagal mendapatkan jawaban dari Gemini API: " . $lastError);
    }
}
