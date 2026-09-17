<?php

namespace App\Services;

use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountService
{
    public function revokeSessions(User $user): void
    {
        if (config('session.driver') === 'database') {
            DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)->delete();
        }
    }

    public function setActive(User $user, bool $active): void
    {
        DB::transaction(function () use ($user, $active) {
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $current->forceFill(['is_active' => $active])->save();
            app(AuditService::class)->record($active ? 'account.activated' : 'account.deactivated', $current);
        });
        $this->revokeSessions($user);
    }

    public function delete(User $user, ?int $transferTo = null): void
    {
        $avatar = DB::transaction(function () use ($user, $transferTo) {
            // Serialize teacher removals so two simultaneous deletions cannot remove the final teachers.
            if ($user->isGuru()) {
                User::where('role', 'guru')->orderBy('id')->lockForUpdate()->get();
            }
            $current = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($current->isGuru() && ! User::where('role', 'guru')->where('is_active', true)->where('id', '!=', $current->id)->exists()) {
                throw ValidationException::withMessages(['transfer_to' => 'Akun guru aktif terakhir tidak dapat dihapus. Siapkan guru pengganti terlebih dahulu.']);
            }
            if ($current->isGuru() && Module::where('guru_id', $current->id)->exists()) {
                $replacement = User::whereKey($transferTo)->where('role', 'guru')->where('is_active', true)
                    ->where('id', '!=', $current->id)->lockForUpdate()->first();
                if (! $replacement) {
                    throw ValidationException::withMessages(['transfer_to' => 'Pilih guru aktif penerima modul sebelum menghapus akun guru.']);
                }
                Module::where('guru_id', $current->id)->update(['guru_id' => $replacement->id]);
            }
            app(AuditService::class)->record('account.deleted', $current, ['role' => $current->role, 'transfer_to' => $transferTo]);
            $avatar = $current->avatar;
            $current->delete();

            return $avatar;
        });
        $this->revokeSessions($user);
        app(StoredFileService::class)->delete($avatar, 'public');
    }
}
