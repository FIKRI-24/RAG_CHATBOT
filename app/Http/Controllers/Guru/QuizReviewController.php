<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Services\AuditService;
use App\Services\StudentAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuizReviewController extends Controller
{
    public function index(Request $request, StudentAccessService $access)
    {
        $request->validate(['status' => 'nullable|in:pending,reviewed,all']);
        $reviews = $access->chats()->where('kind', 'quiz_feedback')->with(['siswa', 'quiz', 'reviewer'])
            ->when($request->input('status', 'pending') === 'pending', fn ($q) => $q->whereNull('reviewed_at'))
            ->when($request->input('status') === 'reviewed', fn ($q) => $q->whereNotNull('reviewed_at'))
            ->latest('id')->paginate(20)->withQueryString();

        return view('guru.quiz-reviews', compact('reviews'));
    }

    public function update(Request $request, ChatHistory $chat, StudentAccessService $access, AuditService $audit)
    {
        abort_unless($chat->kind === 'quiz_feedback', 404);
        $access->authorize($chat->siswa);
        $data = $request->validate(['score' => 'required|integer|between:0,100', 'review_note' => 'required|string|max:2000']);
        DB::transaction(function () use ($request, $chat, $data, $audit) {
            $chat = ChatHistory::whereKey($chat->id)->lockForUpdate()->firstOrFail();
            $previous = $chat->score;
            $chat->update($data + ['reviewed_by' => $request->user()->id, 'reviewed_at' => now()]);
            $audit->record('quiz.reviewed', $chat, ['previous_score' => $previous, 'score' => $data['score']]);
        });

        return back()->with('success', 'Penilaian guru tersimpan.');
    }
}
