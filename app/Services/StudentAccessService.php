<?php

namespace App\Services;

use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class StudentAccessService
{
    public function students(): Builder
    {
        return User::where('role', 'siswa');
    }

    public function authorize(User $student): void
    {
        abort_unless(auth()->user()?->isGuru() && $this->students()->whereKey($student->id)->exists(), 403, 'Akses siswa ditolak.');
    }

    public function chats(): Builder
    {
        return ChatHistory::whereIn('siswa_id', $this->students()->select('id'));
    }
}
