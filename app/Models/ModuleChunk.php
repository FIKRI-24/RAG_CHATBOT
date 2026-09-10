<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'chunk_text',
        'embedding_vector',
        'embedding_model',
        'embedding_dimensions',
        'chunk_index',
    ];

    protected $casts = [
        'embedding_vector' => 'array',
    ];

    /**
     * Get the module that owns the chunk.
     */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }
}
