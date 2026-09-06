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
    ];

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
