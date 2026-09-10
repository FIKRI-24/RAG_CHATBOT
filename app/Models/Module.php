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
        'indexing_version',
        'indexing_error',
        'berlaku_sampai',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date',
    ];

    public function scopeAvailable($query)
    {
        return $query->where('status_indexing', 'completed')->where(function ($query) {
            $query->whereNull('berlaku_sampai')->orWhereDate('berlaku_sampai', '>=', today(config('app.display_timezone'))->toDateString());
        });
    }

    public function isExpired(): bool
    {
        return $this->berlaku_sampai !== null
            && $this->berlaku_sampai->toDateString() < today(config('app.display_timezone'))->toDateString();
    }

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
