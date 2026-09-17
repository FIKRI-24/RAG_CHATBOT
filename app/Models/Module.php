<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
        'additional_videos',
    ];

    protected $casts = [
        'berlaku_sampai' => 'date',
        'additional_videos' => 'array',
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

    public function additionalVideos(): array
    {
        $videos = $this->additional_videos;
        if ($videos === null && $this->mapel === 'Teknik Komputer dan Jaringan') {
            // Config keys contain dots in file names, so access the literal array key.
            $videos = config('module-media', [])[$this->file_path] ?? [];
        }

        return array_map(fn ($video) => $video + ['icon' => 'fa-solid fa-video', 'color' => 'text-blue-500'], $videos ?? []);
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

    public function quiz(): HasOne
    {
        return $this->hasOne(ModuleQuiz::class);
    }
}
