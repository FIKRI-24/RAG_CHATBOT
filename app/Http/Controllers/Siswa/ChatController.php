<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Services\GeminiService;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        // Ambil riwayat chat pengguna saat ini
        $chats = ChatHistory::where('siswa_id', auth()->id())->orderBy('created_at', 'asc')->get();
        // Ambil daftar modul yang tersedia (aktif / belum kedaluwarsa)
        $modules = Module::where('status_indexing', 'completed')
            ->where(function($q) {
                $q->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
            })->get();
        
        $mapels = $modules->pluck('mapel')->unique();
        $selectedMapel = $request->query('mapel', 'Semua');

        return view('siswa.dashboard', compact('chats', 'modules', 'mapels', 'selectedMapel'));
    }

    public function ask(Request $request, GeminiService $gemini)
    {
        $request->validate([
            'pertanyaan' => 'required|string|max:1000',
            'mapel' => 'nullable|string'
        ]);

        $pertanyaan = $request->input('pertanyaan');
        $mapel = $request->input('mapel');

        try {
            // Cek apakah mode Kuis
            if ($pertanyaan === '[LATIHAN_SOAL]') {
                $query = ModuleChunk::query()->whereHas('module', function($q) {
                    $q->where(function($sub) {
                        $sub->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
                    });
                });
                
                if ($mapel && $mapel !== 'Semua') {
                    $query->whereHas('module', function($q) use ($mapel) {
                        $q->where('mapel', $mapel);
                    });
                }
                $chunk = $query->inRandomOrder()->first();

                if (!$chunk) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Belum ada materi untuk dijadikan soal.'
                    ]);
                }

                $jawaban = $gemini->generateQuiz($chunk->chunk_text);
                
                $chat = ChatHistory::create([
                    'siswa_id' => auth()->id(),
                    'pertanyaan' => '[LATIHAN_SOAL]',
                    'jawaban' => $jawaban,
                    'referensi_chunk_id' => $chunk->id
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'pertanyaan' => 'Tolong berikan saya latihan soal.',
                        'jawaban' => $chat->jawaban,
                        'created_at' => $chat->created_at->format('H:i')
                    ]
                ]);
            }

            // Cek apakah chat sebelumnya adalah kuis (tunggu jawaban siswa)
            $lastChat = ChatHistory::where('siswa_id', auth()->id())->orderBy('created_at', 'desc')->first();
            if ($lastChat && $lastChat->pertanyaan === '[LATIHAN_SOAL]') {
                // Evaluasi kuis
                $chunk = ModuleChunk::find($lastChat->referensi_chunk_id);
                $context = $chunk ? $chunk->chunk_text : '';

                $jawaban = $gemini->gradeQuiz($lastChat->jawaban, $pertanyaan, $context);

                $chat = ChatHistory::create([
                    'siswa_id' => auth()->id(),
                    'pertanyaan' => $pertanyaan,
                    'jawaban' => $jawaban,
                    'referensi_chunk_id' => $lastChat->referensi_chunk_id
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'pertanyaan' => $chat->pertanyaan,
                        'jawaban' => $chat->jawaban,
                        'created_at' => $chat->created_at->format('H:i')
                    ]
                ]);
            }

            // 1. Embed pertanyaan
            $questionEmbedding = $gemini->embedText($pertanyaan);

            // 2. Ambil semua chunk dari database, pastikan modul aktif
            $query = ModuleChunk::query()->whereHas('module', function($q) {
                $q->where(function($sub) {
                    $sub->whereNull('berlaku_sampai')->orWhere('berlaku_sampai', '>=', now());
                });
            });

            if ($mapel && $mapel !== 'Semua') {
                $query->whereHas('module', function($q) use ($mapel) {
                    $q->where('mapel', $mapel);
                });
            }
            $chunks = $query->get();

            $bestChunks = [];
            foreach ($chunks as $chunk) {
                // Pastikan embedding_vector adalah array
                $vector = is_string($chunk->embedding_vector) ? json_decode($chunk->embedding_vector, true) : $chunk->embedding_vector;
                
                if (!empty($vector) && is_array($vector)) {
                    $similarity = $this->cosineSimilarity($questionEmbedding, $vector);
                    $bestChunks[] = [
                        'chunk' => $chunk,
                        'similarity' => $similarity
                    ];
                }
            }

            // Urutkan berdasarkan similarity tertinggi
            usort($bestChunks, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });

            // Ambil top 3 chunk
            $topK = array_slice($bestChunks, 0, 3);
            $contextText = "";
            $referensiChunkId = null;

            if (count($topK) > 0 && $topK[0]['similarity'] > 0.3) {
                // Gunakan top chunks sebagai konteks
                foreach ($topK as $index => $item) {
                    $contextText .= "--- Potongan " . ($index + 1) . " ---\n";
                    $contextText .= $item['chunk']->chunk_text . "\n\n";
                }
                $referensiChunkId = $topK[0]['chunk']->id; // Simpan chunk paling relevan
            }

            // 3. Generate jawaban menggunakan Gemini
            $jawaban = $gemini->generateAnswer($pertanyaan, $contextText);

            // 4. Simpan ke database
            $chat = ChatHistory::create([
                'siswa_id' => auth()->id(),
                'pertanyaan' => $pertanyaan,
                'jawaban' => $jawaban,
                'referensi_chunk_id' => $referensiChunkId
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'pertanyaan' => $chat->pertanyaan,
                    'jawaban' => $chat->jawaban,
                    'created_at' => $chat->created_at->format('H:i')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Maaf, terjadi kesalahan saat memproses pertanyaan Anda: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hitung cosine similarity antara 2 vektor.
     */
    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0;
        $normA = 0;
        $normB = 0;
        $length = min(count($vec1), count($vec2));

        for ($i = 0; $i < $length; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $normA += $vec1[$i] ** 2;
            $normB += $vec2[$i] ** 2;
        }

        if ($normA == 0 || $normB == 0) {
            return 0;
        }

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}
