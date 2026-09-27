<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $attributes = ['is_active' => true, 'auth_version' => 1];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'avatar',
        'is_active',
        'class_name',
        'student_number',
        'teacher_number',
    ];

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if ($user->isDirty('email')) {
                $user->email = Str::lower(trim($user->email));
                if ($user->exists) {
                    $user->email_verified_at = null;
                }
            }
            if ($user->exists && ($user->isDirty('password') || $user->isDirty('is_active'))) {
                $user->auth_version = (int) $user->getOriginal('auth_version') + 1;
                $user->remember_token = Str::random(60);
            }
        });
    }

    /**
     * Get avatar public URL attribute.
     */
    public function getAvatarUrlAttribute(): ?string
    {
        return $this->avatar ? asset('storage/'.$this->avatar) : null;
    }

    /**
     * Check if user is guru.
     */
    public function isGuru(): bool
    {
        return $this->role === 'guru';
    }

    /**
     * Check if user is siswa.
     */
    public function isSiswa(): bool
    {
        return $this->role === 'siswa';
    }

    /**
     * Get chat histories for this student.
     */
    public function chatHistories()
    {
        return $this->hasMany(ChatHistory::class, 'siswa_id');
    }

    /**
     * Get module quiz attempts for this user.
     */
    public function quizAttempts()
    {
        return $this->hasMany(\App\Models\ModuleQuizAttempt::class, 'user_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'auth_version' => 'integer',
        ];
    }
}
