<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleQuizAttempt extends Model
{
    protected $fillable = ['module_quiz_id', 'module_id', 'user_id', 'module_title', 'quiz_title',
        'quiz_version', 'answers', 'questions_snapshot', 'correct_count', 'question_count', 'score'];

    protected $hidden = ['questions_snapshot'];

    protected $casts = ['answers' => 'array', 'questions_snapshot' => 'array'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
