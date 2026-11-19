<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleQuizAttempt extends Model
{
    protected $fillable = [
        'module_quiz_id', 'module_id', 'module_guru_id', 'user_id', 'module_title', 'quiz_title',
        'quiz_version', 'answers', 'questions_snapshot', 'correct_count', 'question_count', 'score',
        'attempt_number', 'previous_attempts', 'remedial_approved_at', 'remedial_approved_by',
        'remedial_note', 'adjusted_score_by', 'adjusted_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (ModuleQuizAttempt $attempt) {
            if ($attempt->module_guru_id === null && $attempt->module_id !== null) {
                $attempt->module_guru_id = Module::whereKey($attempt->module_id)->value('guru_id');
            }
        });
    }

    protected $hidden = ['questions_snapshot', 'previous_attempts'];

    protected $casts = [
        'answers' => 'array',
        'questions_snapshot' => 'array',
        'previous_attempts' => 'array',
        'remedial_approved_at' => 'datetime',
        'adjusted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'remedial_approved_by');
    }

    public function adjustedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'adjusted_score_by');
    }

    public function hasApprovedRemedial(): bool
    {
        return $this->remedial_approved_at !== null;
    }

    public function historyEntries(): array
    {
        $entries = $this->previous_attempts;

        return is_array($entries) ? array_values($entries) : [];
    }

    public function hasRemedialHistory(): bool
    {
        return (int) $this->attempt_number > 1 || $this->adjusted_at !== null || count($this->historyEntries()) > 0;
    }

    /**
     * Build a chronological history snapshot before replacing score or answers via remedial/manual adjustment.
     */
    public function toHistoryEntry(string $type, ?User $actor = null, ?string $note = null): array
    {
        return [
            'type' => $type,
            'attempt_number' => (int) ($this->attempt_number ?: 1),
            'quiz_version' => (int) $this->quiz_version,
            'score' => (int) $this->score,
            'correct_count' => (int) $this->correct_count,
            'question_count' => (int) $this->question_count,
            'submitted_at' => ($this->adjusted_at ?? $this->updated_at ?? $this->created_at)?->toIso8601String(),
            'recorded_at' => now()->toIso8601String(),
            'actor_id' => $actor?->id,
            'actor_name' => $actor?->name,
            'note' => $note,
        ];
    }
}
