<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'guru_id',
        'judul',
        'mapel',
        'kb_nomor',
        'tp',
        'file_path',
        'video_url',
        'kuis_url',
        'status_indexing',
        'berlaku_sampai',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date',
    ];

    /**
     * Get the user (guru) that owns the module.
     */
    public function guru(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guru_id');
    }

    /**
     * Get the chunks for the module.
     */
    public function chunks(): HasMany
    {
        return $this->hasMany(ModuleChunk::class);
    }
}
