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
        $request->validate(['mapel' => 'nullable|string|max:255']);
        $historyPages = ChatHistory::where('siswa_id', auth()->id())->latest('id')->cursorPaginate(30)->withQueryString();
        $chats = $historyPages->getCollection()->reverse();
        $modules = Module::available()->get();
        $mapels = $modules->pluck('mapel')->unique();
        $selectedMapel = $request->query('mapel', 'Semua');
        $activeQuiz = ChatHistory::where('siswa_id', auth()->id())->where('kind', 'quiz')
            ->where('quiz_status', 'pending')->where('mapel', $selectedMapel)
            ->whereHas('referensiChunk.module', fn ($q) => $q->available())->latest('id')->first();

        return view('siswa.dashboard', compact('chats', 'historyPages', 'modules', 'mapels', 'selectedMapel', 'activeQuiz'));
    }

    public function ask(Request $request, GeminiService $gemini, RetrievalService $retrieval)
    {
        $request->validate([
            'pertanyaan' => 'nullable|string|max:1000',
            'mapel' => 'nullable|string|max:255',
            'action' => 'nullable|in:ask,quiz,quiz_answer,cancel_quiz',
            'quiz_id' => 'nullable|integer|min:1',
        ]);
        $question = trim((string) $request->input('pertanyaan', ''));
        $mapel = $request->input('mapel') ?: 'Semua';
        $action = $request->input('action', $question === '[LATIHAN_SOAL]' ? 'quiz' : 'ask');
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
                $answer = $gemini->generateQuiz($chunk->chunk_text);
                $chat = DB::transaction(function () use ($chunk, $answer, $mapel, $retrieval) {
                    $chunk = $this->lockSource($chunk, $mapel, $retrieval);
                    if (! $chunk) {
                        return null;
                    }
                    $this->cancelQuizzes();

                    return ChatHistory::create([
                        'siswa_id' => auth()->id(), 'pertanyaan' => '[LATIHAN_SOAL]',
                        'jawaban' => $answer, 'referensi_chunk_id' => $chunk->id,
                        'kind' => 'quiz', 'quiz_status' => 'pending', 'mapel' => $mapel,
                        'sources' => [$retrieval->source($chunk)],
                    ]);
                });

                return $chat ? $this->reply($chat, $chat->id)
                    : $this->error('Sumber kuis berubah saat diproses. Silakan mulai kuis baru.', 409);
            }
            if ($action === 'quiz_answer') {
                return $this->grade($request->integer('quiz_id'), $question, $mapel, $gemini, $retrieval);
            }

            // Normal questions never implicitly become quiz answers.
            $this->cancelQuizzes();
            $sources = [];
            $queryText = $question;
            if ($retrieval->eligible($mapel)->exists()) {
                $history = ChatHistory::where('siswa_id', auth()->id())->where('kind', 'answer')
                    ->where('mapel', $mapel)->where('created_at', '>=', now()->subMinutes(30))
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
            $chat = DB::transaction(function () use ($question, $answer, $sources, $mapel, $queryText) {
                $reference = isset($sources[0]) ? ModuleChunk::whereKey($sources[0]['chunk_id'])->lockForUpdate()->first(['id']) : null;

                return ChatHistory::create([
                    'siswa_id' => auth()->id(), 'pertanyaan' => $question, 'jawaban' => $answer,
                    'referensi_chunk_id' => $reference?->id,
                    'mapel' => $mapel, 'kind' => 'answer', 'sources' => $sources, 'retrieval_query' => $queryText,
                ]);
            });
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

    private function grade(int $id, string $answer, string $mapel, GeminiService $gemini, RetrievalService $retrieval)
    {
        $quiz = ChatHistory::whereKey($id)->where('siswa_id', auth()->id())
            ->where('kind', 'quiz')->where('quiz_status', 'pending')->where('mapel', $mapel)->first();
        if (! $quiz) {
            return $this->error('Kuis sudah selesai, dibatalkan, atau tidak sesuai mata pelajaran.', 409);
        }
        $chunk = $retrieval->eligible($mapel)->whereKey($quiz->referensi_chunk_id)->first();
        if (! $chunk) {
            $quiz->update(['quiz_status' => 'cancelled']);

            return $this->error('Sumber kuis sudah tidak tersedia. Silakan mulai kuis baru.', 409);
        }
        $feedback = $gemini->gradeQuiz($quiz->jawaban, $answer, $chunk->chunk_text);
        $chat = DB::transaction(function () use ($quiz, $answer, $feedback, $mapel, $chunk, $retrieval) {
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
        if (! $current || ! VectorService::valid($current->embedding_vector)) {
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

    private function error(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
