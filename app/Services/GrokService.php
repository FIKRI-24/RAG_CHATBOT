<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GrokService
{
    protected string $apiKey;
    protected string $baseUrl = 'https://api.x.ai/v1';

    public function __construct()
    {
        $this->apiKey = env('GROK_API_KEY', '');
    }

    /**
     * Embed text using Grok API.
     *
     * @param string $text
     * @return array
     * @throws Exception
     */
    public function embedText(string $text): array
    {
        if (empty(trim($this->apiKey))) {
            throw new Exception("Grok API key is not configured in .env file.");
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/embeddings", [
                    'model' => 'grok-embedding', // fallback model
                    'input' => $text
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['data'][0]['embedding'])) {
                    return $data['data'][0]['embedding'];
                }
            }

            Log::error('Grok embedText Error', ['status' => $response->status(), 'response' => $response->json()]);
            throw new Exception("Gagal mendapatkan embedding dari Grok API: " . $response->body());
            
        } catch (Exception $e) {
            Log::error('Grok embedText Exception: ' . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memanggil Grok API (Embedding): " . $e->getMessage());
        }
    }

    /**
     * Generate answer based on context using Grok API.
     *
     * @param string $question
     * @param string $context
     * @return string
     * @throws Exception
     */
    public function generateAnswer(string $question, string $context): string
    {
        if (empty(trim($this->apiKey))) {
            throw new Exception("Grok API key is not configured in .env file.");
        }

        $systemInstruction = "Anda adalah asisten pembelajaran TKJ. Jawab pertanyaan HANYA berdasarkan konteks materi yang diberikan. Jika jawaban tidak ditemukan dalam konteks, katakan bahwa materi tersebut belum tersedia dalam modul. Jawab dalam Bahasa Indonesia.";
        
        try {
            $response = Http::withToken($this->apiKey)
                ->post("{$this->baseUrl}/chat/completions", [
                    'model' => 'grok-2-latest',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemInstruction
                        ],
                        [
                            'role' => 'user',
                            'content' => "Konteks:\n{$context}\n\nPertanyaan:\n{$question}"
                        ]
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                if (isset($data['choices'][0]['message']['content'])) {
                    return $data['choices'][0]['message']['content'];
                }
                return "Maaf, tidak dapat menghasilkan jawaban saat ini.";
            }

            Log::error('Grok generateAnswer Error', ['status' => $response->status(), 'response' => $response->json()]);
            throw new Exception("Gagal mendapatkan jawaban dari Grok API: " . $response->body());
            
        } catch (Exception $e) {
            Log::error('Grok generateAnswer Exception: ' . $e->getMessage());
            throw new Exception("Terjadi kesalahan saat memanggil Grok API (Generation): " . $e->getMessage());
        }
    }
}
