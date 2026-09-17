<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChatHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'pertanyaan',
        'jawaban',
        'referensi_chunk_id',
        'mapel',
        'kind',
        'quiz_status',
        'sources',
        'retrieval_query',
        'scope_module_id', 'quiz_id', 'quiz_payload', 'assessment', 'score', 'reviewed_by', 'reviewed_at', 'review_note',
    ];

    protected $casts = ['sources' => 'array', 'quiz_payload' => 'array', 'assessment' => 'array', 'score' => 'integer', 'reviewed_at' => 'datetime'];

    protected $hidden = ['quiz_payload'];

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(self::class, 'quiz_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeQuestions($query)
    {
        return $query->where('kind', 'answer')->where('pertanyaan', '!=', '[LATIHAN_SOAL]');
    }

    public function scopeQuizzes($query)
    {
        return $query->where(fn ($q) => $q->where('kind', 'quiz')->orWhere('pertanyaan', '[LATIHAN_SOAL]'));
    }

    public function getActivityTypeAttribute(): string
    {
        return $this->kind === 'quiz_feedback' ? 'Jawaban Kuis'
            : (($this->kind === 'quiz' || $this->pertanyaan === '[LATIHAN_SOAL]') ? 'Latihan Soal' : 'Tanya Materi');
    }

    public function sourceLabels(): array
    {
        $sources = $this->sources ?? [];
        if (! $sources && $this->referensiChunk?->module) {
            $module = $this->referensiChunk->module;
            $sources = [['judul' => $module->judul, 'mapel' => $module->mapel, 'kb_nomor' => $module->kb_nomor]];
        }

        return collect($sources)->map(fn ($source) => implode(' / ', array_filter([
            $source['mapel'] ?? '', $source['kb_nomor'] ?? '', $source['judul'] ?? '',
        ])))->filter()->unique()->values()->all();
    }

    /**
     * Get the user (siswa) that owns the chat history.
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'siswa_id');
    }

    /**
     * Get the reference chunk for the chat history.
     */
    public function referensiChunk(): BelongsTo
    {
        return $this->belongsTo(ModuleChunk::class, 'referensi_chunk_id');
    }
}
