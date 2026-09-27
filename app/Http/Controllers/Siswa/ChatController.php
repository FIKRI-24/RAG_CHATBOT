<?php

namespace App\Http\Controllers\Siswa;

use App\Exceptions\RagException;
use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Models\Module;
use App\Models\ModuleChunk;
use App\Services\GeminiService;
use App\Services\RetrievalService;
use App\Services\VectorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['mapel' => 'nullable|string|max:255', 'module_id' => 'nullable|integer|exists:modules,id']);
        $historyPages = ChatHistory::where('siswa_id', auth()->id())->latest('id')->cursorPaginate(30)->withQueryString();
        $chats = $historyPages->getCollection()->reverse();
        $modules = Module::available()->with('quiz')->orderBy('kb_nomor')->orderBy('id')->get();
        $mapels = $modules->pluck('mapel')->unique();
        $selectedMapel = $request->query('mapel', 'Semua');
        $selectedModule = $request->integer('module_id') ?: null;
        $activeQuiz = ChatHistory::where('siswa_id', auth()->id())->where('kind', 'quiz')
            ->where('quiz_status', 'pending')->where('mapel', $selectedMapel)
            ->where('scope_module_id', $selectedModule)
            ->whereHas('referensiChunk.module', fn ($q) => $q->available())->latest('id')->first();

        return view('siswa.dashboard', compact('chats', 'historyPages', 'modules', 'mapels', 'selectedMapel', 'selectedModule', 'activeQuiz'));
    }

    public function ask(Request $request, GeminiService $gemini, RetrievalService $retrieval)
    {
        $request->validate([
            'pertanyaan' => 'nullable|string|max:1000',
            'mapel' => 'nullable|string|max:255',
            'action' => 'nullable|in:ask,quiz,quiz_answer,cancel_quiz',
            'quiz_id' => 'nullable|integer|min:1',
            'module_id' => 'nullable|integer|min:1',
        ]);
        $question = trim((string) $request->input('pertanyaan', ''));
        $mapel = $request->input('mapel') ?: 'Semua';
        $action = $request->input('action', $question === '[LATIHAN_SOAL]' ? 'quiz' : 'ask');
        $moduleId = $request->integer('module_id') ?: null;
        $retrieval->scope($moduleId);
        if ($moduleId && $action !== 'cancel_quiz' && ! Module::available()->whereKey($moduleId)
            ->when($mapel !== 'Semua', fn ($q) => $q->where('mapel', $mapel))->exists()) {
            return $this->error('Modul yang dipilih tidak tersedia atau berbeda mata pelajaran.', 422);
        }
        if (in_array($action, ['ask', 'quiz_answer']) && $question === '') {
            return $this->error('Pertanyaan atau jawaban wajib diisi.', 422);
        }
        if ($action === 'quiz_answer' && ! $request->filled('quiz_id')) {
            return $this->error('Pilih kuis yang ingin dijawab.', 422);
        }

        $lock = Cache::lock('rag-chat:'.auth()->id(), 300);
        if (! $lock->get()) {
            return $this->error('Pertanyaan sebelumnya masih diproses. Tunggu hingga selesai.', 409);
        }
        $started = microtime(true);
        try {
            if ($action === 'cancel_quiz') {
                $this->cancelQuizzes();

                return response()->json(['success' => true, 'data' => ['quiz_id' => null]]);
            }
            if ($action === 'quiz') {
                $chunk = $retrieval->eligible($mapel)->with('module')->inRandomOrder()->cursor()
                    ->first(fn ($chunk) => VectorService::valid($chunk->embedding_vector));
                if (! $chunk) {
                    return $this->error('Belum ada materi yang siap untuk dijadikan soal.', 422);
                }
                $gemini->startBudget();
                $payload = $gemini->structuredQuiz($chunk->chunk_text);
                $answer = $payload['question'];
                $chat = DB::transaction(function () use ($chunk, $answer, $payload, $mapel, $moduleId, $retrieval) {
                    $chunk = $this->lockSource($chunk, $mapel, $retrieval);
                    if (! $chunk) {
                        return null;
                    }
                    $this->cancelQuizzes();

                    return ChatHistory::create([
                        'siswa_id' => auth()->id(), 'pertanyaan' => '[LATIHAN_SOAL]',
                        'jawaban' => $answer, 'referensi_chunk_id' => $chunk->id,
                        'kind' => 'quiz', 'quiz_status' => 'pending', 'mapel' => $mapel,
                        'scope_module_id' => $moduleId, 'quiz_payload' => $payload,
                        'sources' => [$retrieval->source($chunk)],
                    ]);
                });

                return $chat ? $this->reply($chat, $chat->id)
                    : $this->error('Sumber kuis berubah saat diproses. Silakan mulai kuis baru.', 409);
            }
            if ($action === 'quiz_answer') {
                return $this->grade($request->integer('quiz_id'), $question, $mapel, $moduleId, $gemini, $retrieval);
            }

            // Normal questions never implicitly become quiz answers.
            $this->cancelQuizzes();

            // 1. Tangani sapaan ramah (Greetings / Chitchat)
            if ($this->isGreeting($question)) {
                $greetingAnswer = $this->buildGreetingResponse($mapel, $moduleId);
                $chat = ChatHistory::create([
                    'siswa_id' => auth()->id(), 'pertanyaan' => $question, 'jawaban' => $greetingAnswer,
                    'referensi_chunk_id' => null, 'mapel' => $mapel, 'kind' => 'answer', 'sources' => [],
                    'retrieval_query' => $question, 'scope_module_id' => $moduleId,
                ]);

                return $this->reply($chat);
            }

            // 2. Tangani pertanyaan seputar katalog & ketersediaan modul (Meta-questions)
            if ($catalogAnswer = $this->handleCatalogInquiry($question, $mapel, $moduleId)) {
                $chat = ChatHistory::create([
                    'siswa_id' => auth()->id(), 'pertanyaan' => $question, 'jawaban' => $catalogAnswer,
                    'referensi_chunk_id' => null, 'mapel' => $mapel, 'kind' => 'answer', 'sources' => [],
                    'retrieval_query' => $question, 'scope_module_id' => $moduleId,
                ]);

                return $this->reply($chat);
            }

            $sources = [];
            $queryText = $question;
            if ($retrieval->eligible($mapel)->exists()) {
                $gemini->startBudget();
                $history = ChatHistory::where('siswa_id', auth()->id())->where('kind', 'answer')
                    ->where('mapel', $mapel)->where('created_at', '>=', now()->subMinutes(30))
                    ->where('scope_module_id', $moduleId)
                    ->latest('id')->limit(config('rag.history_turns', 3))->get()->reverse()
                    ->map(fn ($chat) => ['pertanyaan' => $chat->retrieval_query ?: $chat->pertanyaan,
                        'jawaban' => mb_substr($chat->jawaban, 0, 1500)])->values()->all();
                $queryText = $gemini->rewriteQuestion($question, $history);
                $matches = $retrieval->search($gemini->embedText($queryText), $mapel);
                $sources = $retrieval->sources($matches, $mapel);
            }
            $answer = ! $sources
                ? 'Materi untuk menjawab pertanyaan tersebut belum ditemukan dalam modul yang tersedia.'
                : $gemini->generateAnswer($queryText, $retrieval->context($sources));
            $chat = DB::transaction(function () use ($question, $answer, $sources, $mapel, $moduleId, $queryText, $retrieval) {
                // Lock every source module in stable order, then verify the complete source window.
                $moduleIds = array_values(array_unique(array_column($sources, 'module_id')));
                sort($moduleIds);
                $available = Module::available()->whereIn('id', $moduleIds)->orderBy('id')->lockForUpdate()->get();
                if ($available->count() !== count($moduleIds)) {
                    return null;
                }
                $chunkIds = [];
                foreach ($sources as $source) {
                    $chunkIds = array_merge($chunkIds, $source['chunk_ids'] ?? [$source['chunk_id']]);
                }
                $chunkIds = array_values(array_unique($chunkIds));
                $current = $retrieval->eligible($mapel)->whereIn('id', $chunkIds)->orderBy('id')->lockForUpdate()->get();
                if ($current->count() !== count($chunkIds)) {
                    return null;
                }
                foreach ($sources as $source) {
                    $module = $available->firstWhere('id', $source['module_id']);
                    $text = collect($source['chunk_ids'] ?? [$source['chunk_id']])
                        ->map(fn ($id) => $current->firstWhere('id', $id)->chunk_text)->implode("\n");
                    if ($module->indexing_version !== ($source['indexing_version'] ?? null)
                        || ! hash_equals($source['content_hash'], hash('sha256', $text))) {
                        return null;
                    }
                }
                $reference = isset($sources[0]) ? $current->firstWhere('id', $sources[0]['chunk_id']) : null;

                return ChatHistory::create([
                    'siswa_id' => auth()->id(), 'pertanyaan' => $question, 'jawaban' => $answer,
                    'referensi_chunk_id' => $reference?->id,
                    'mapel' => $mapel, 'kind' => 'answer', 'sources' => $sources, 'retrieval_query' => $queryText,
                    'scope_module_id' => $moduleId,
                ]);
            });
            if (! $chat) {
                return $this->error('Sumber materi berubah saat jawaban diproses. Silakan kirim pertanyaan kembali.', 409);
            }
            Log::info('RAG answer completed', [
                'chat_id' => $chat->id, 'source_count' => count($sources),
                'scores' => array_column($sources, 'similarity'),
                'duration_ms' => (int) ((microtime(true) - $started) * 1000),
            ]);

            return $this->reply($chat);
        } catch (Throwable $e) {
            Log::warning('RAG chat failed', ['error_type' => get_class($e)]);

            return $this->error($e instanceof RagException ? $e->getMessage() : 'Layanan AI belum dapat memproses permintaan. Silakan coba lagi; jika berulang, hubungi pengelola.', 503);
        } finally {
            $lock->release();
        }
    }

    private function grade(int $id, string $answer, string $mapel, ?int $moduleId, GeminiService $gemini, RetrievalService $retrieval)
    {
        $quiz = ChatHistory::whereKey($id)->where('siswa_id', auth()->id())
            ->where('kind', 'quiz')->where('quiz_status', 'pending')->where('mapel', $mapel)->where('scope_module_id', $moduleId)->first();
        if (! $quiz) {
            return $this->error('Kuis sudah selesai, dibatalkan, atau tidak sesuai mata pelajaran.', 409);
        }
        $chunk = $retrieval->eligible($mapel)->whereKey($quiz->referensi_chunk_id)->first();
        if (! $chunk) {
            $quiz->update(['quiz_status' => 'cancelled']);

            return $this->error('Sumber kuis sudah tidak tersedia. Silakan mulai kuis baru.', 409);
        }
        if (! $quiz->quiz_payload) {
            $quiz->update(['quiz_status' => 'cancelled']);

            return $this->error('Kuis lama perlu dibuat kembali agar dapat dinilai dengan rubrik.', 409);
        }
        $gemini->startBudget();
        $assessment = $gemini->assessQuiz($quiz->quiz_payload, $answer, $chunk->chunk_text);
        $feedback = 'Nilai AI sementara: '.$assessment['score'].'/100. '.$assessment['feedback'].' Nilai dapat ditinjau guru.';
        $chat = DB::transaction(function () use ($quiz, $answer, $feedback, $assessment, $mapel, $moduleId, $chunk, $retrieval) {
            $chunk = $this->lockSource($chunk, $mapel, $retrieval);
            $quiz = ChatHistory::whereKey($quiz->id)->where('quiz_status', 'pending')->lockForUpdate()->first();
            if (! $quiz) {
                return null;
            }
            if (! $chunk) {
                $quiz->update(['quiz_status' => 'cancelled']);

                return null;
            }
            $quiz->update(['quiz_status' => 'answered']);

            return ChatHistory::create([
                'siswa_id' => auth()->id(), 'pertanyaan' => $answer, 'jawaban' => $feedback,
                'referensi_chunk_id' => $chunk->id, 'mapel' => $mapel,
                'kind' => 'quiz_feedback', 'sources' => $quiz->sources,
                'quiz_id' => $quiz->id, 'scope_module_id' => $moduleId,
                'assessment' => $assessment, 'score' => $assessment['score'],
            ]);
        });

        return $chat ? $this->reply($chat) : $this->error('Kuis atau sumbernya berubah saat diproses. Silakan mulai kuis baru.', 409);
    }

    private function lockSource(ModuleChunk $chunk, string $mapel, RetrievalService $retrieval): ?ModuleChunk
    {
        // Same lock order as reindex: module, then chunk, then chat. No API call holds a DB lock.
        $module = Module::available()->whereKey($chunk->module_id)->lockForUpdate()->first();
        if (! $module) {
            return null;
        }
        $current = $retrieval->eligible($mapel)->whereKey($chunk->id)->lockForUpdate()->first();
        if (! $current || $current->chunk_text !== $chunk->chunk_text || ! VectorService::valid($current->embedding_vector)) {
            return null;
        }

        return $current->setRelation('module', $module);
    }

    private function cancelQuizzes(): void
    {
        ChatHistory::where('siswa_id', auth()->id())->where('kind', 'quiz')
            ->where('quiz_status', 'pending')->update(['quiz_status' => 'cancelled']);
    }

    private function reply(ChatHistory $chat, ?int $quizId = null)
    {
        return response()->json(['success' => true, 'data' => [
            'pertanyaan' => $chat->pertanyaan, 'jawaban' => $chat->jawaban,
            'created_at' => $chat->created_at->timezone(config('app.display_timezone'))->format('H:i'),
            'sources' => $chat->sources ?? [], 'quiz_id' => $quizId,
        ]]);
    }

    private function isGreeting(string $text): bool
    {
        $normalized = trim(mb_strtolower(preg_replace('/[^\p{L}\p{N}\s]/u', ' ', $text)));
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        $greetings = [
            'halo', 'halo halo', 'hai', 'hi', 'hey', 'hei', 'hallo',
            'assalamualaikum', 'assalamu alaikum', 'assalamualaikum wr wb', 'salam',
            'selamat pagi', 'pagi', 'selamat siang', 'siang', 'selamat sore', 'sore', 'selamat malam', 'malam',
            'siapa kamu', 'kamu siapa', 'kamu siapa sih', 'apakah kamu ai', 'kamu ai ya',
            'bisa bantu apa', 'kamu bisa apa', 'tolong bantu', 'bantuan', 'bisa tolong',
            'tes', 'test', 'halo tes', 'testing',
        ];

        if (in_array($normalized, $greetings, true)) {
            return true;
        }

        if (preg_match('/^(halo|hai|hi|hey|hei|assalamu\s*alaikum|selamat (pagi|siang|sore|malam))(\s+(bot|ai|admin|pak|bu|kak|kawan|sahabat))?$/i', $normalized)) {
            return true;
        }

        return false;
    }

    private function buildGreetingResponse(string $mapel, ?int $moduleId): string
    {
        $modules = Module::available()
            ->when($mapel !== 'Semua', fn ($q) => $q->where('mapel', $mapel))
            ->orderBy('kb_nomor')
            ->orderBy('id')
            ->get(['id', 'judul', 'kb_nomor']);

        $greeting = "Halo! Saya adalah **Asisten AI Pembelajaran TKJ SMK Negeri 1 Kinali**.\n\n"
            . "Saya siap membantu Anda memahami konsep jaringan komputer, konfigurasi perangkat, dan materi pembelajaran lainnya.";

        if ($modules->isNotEmpty()) {
            $list = $modules->map(function ($m) {
                $label = $m->kb_nomor ? "**{$m->kb_nomor}**" : "**Modul**";
                return "• {$label}: {$m->judul}";
            })->implode("\n");

            $greeting .= "\n\n📚 **Kegiatan Belajar (KB) yang dapat dipelajari:**\n" . $list;
        }

        $greeting .= "\n\nSilakan ajukan pertanyaan seputar materi di atas, dan saya akan menjelaskannya berdasarkan modul pembelajaran resmi!";

        return $greeting;
    }

    private function handleCatalogInquiry(string $text, string $mapel, ?int $moduleId): ?string
    {
        $normalized = trim(mb_strtolower($text));

        // 1. Cek apakah menanyakan ketersediaan / topik KB tertentu (misal: "apakah ada materi kb 4", "kb 4 ada?", "kb 4 tentang apa")
        if (preg_match('/\b(kb|kegiatan belajar)\s*([1-9]\d*)\b/i', $normalized, $matches)) {
            $kbNum = $matches[2];
            $kbLabel = "KB {$kbNum}";

            $isMetaQuestion = preg_match('/(ada|tersedia|aktif|tentang apa|bahas apa|materi apa|penjelasan|judul)/i', $normalized);
            if ($isMetaQuestion) {
                $module = Module::available()
                    ->when($mapel !== 'Semua', fn ($q) => $q->where('mapel', $mapel))
                    ->where(function ($q) use ($kbLabel, $kbNum) {
                        $q->where('kb_nomor', $kbLabel)
                            ->orWhere('kb_nomor', "KB-{$kbNum}")
                            ->orWhere('judul', 'like', "%{$kbLabel}%");
                    })->first();

                if ($module) {
                    $hasQuiz = $module->quiz && $module->quiz->is_published;
                    $quizStatus = $hasQuiz ? '✅ Kuis Objektif Tersedia (1x kesempatan pengerjaan)' : '⏳ Kuis Belum Diterbitkan';
                    $response = "Ya, materi **{$module->kb_nomor}: {$module->judul}** sudah **aktif dan tersedia** di sistem E-Modul SMK Negeri 1 Kinali!\n\n";
                    if ($module->tp) {
                        $response .= "🎯 **Tujuan Pembelajaran:**\n{$module->tp}\n\n";
                    }
                    $response .= "📌 **Status Kuis:** {$quizStatus}\n\n";
                    $response .= "Anda dapat membaca materi selengkapnya pada menu **Katalog E-Modul** atau langsung menanyakan konsep teknis seputar materi ini kepada saya!";

                    return $response;
                } else {
                    return "Saat ini materi **{$kbLabel}** belum ditemukan atau belum diaktifkan dalam sistem. Silakan periksa daftar Kegiatan Belajar yang tersedia.";
                }
            }
        }

        // 2. Cek pertanyaan umum tentang katalog modul (misal: "ada materi apa saja", "daftar materi", "kegiatan belajar apa saja", "modul apa yang ada")
        $isGeneralCatalog = preg_match('/(ada materi apa( saja)?|apa( saja)? materi yang ada|daftar (materi|modul|kb|kegiatan belajar)|modul apa( saja)? yang (ada|tersedia)|kegiatan belajar apa( saja)?)/i', $normalized);

        if ($isGeneralCatalog) {
            $modules = Module::available()
                ->when($mapel !== 'Semua', fn ($q) => $q->where('mapel', $mapel))
                ->orderBy('kb_nomor')
                ->orderBy('id')
                ->get(['id', 'judul', 'kb_nomor', 'mapel']);

            if ($modules->isEmpty()) {
                return 'Saat ini belum ada modul pembelajaran yang aktif dalam sistem.';
            }

            $list = $modules->map(function ($m) {
                $label = $m->kb_nomor ? "**{$m->kb_nomor}**" : "**Modul**";
                return "• {$label}: {$m->judul}";
            })->implode("\n");

            return "Saat ini terdapat **{$modules->count()} Kegiatan Belajar (KB)** yang aktif dan dapat Anda pelajari di E-Modul TKJ SMK Negeri 1 Kinali:\n\n"
                . $list . "\n\n"
                . "💡 Anda dapat menanyakan materi atau konsep apa pun dari daftar di atas, dan saya siap membantu!";
        }

        return null;
    }

    private function error(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
