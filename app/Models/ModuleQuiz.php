<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuleQuiz extends Model
{
    protected $fillable = ['module_id', 'title', 'questions', 'is_published', 'version'];

    protected $hidden = ['questions'];

    protected $casts = ['questions' => 'array', 'is_published' => 'boolean', 'version' => 'integer'];

    public function studentQuestions(): array
    {
        return array_map(fn ($question) => array_intersect_key($question, array_flip(['text', 'options'])), $this->questions);
    }
}
