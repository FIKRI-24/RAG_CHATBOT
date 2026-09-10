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
    ];

    protected $casts = ['sources' => 'array'];

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
