<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
use App\Services\AccountService;
use App\Services\AuditService;
use App\Services\StoredFileService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'transferTeachers' => User::where('role', 'guru')->where('is_active', true)->whereKeyNot($request->user()->id)->get(['id', 'name']),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        $files = app(StoredFileService::class);
        $path = $request->hasFile('avatar')
            ? $files->store($request->file('avatar'), 'avatars', 'public', 'avatar') : null;
        $oldPath = null;
        try {
            DB::transaction(function () use ($request, $validated, $path, &$oldPath) {
                $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                $user->fill(['name' => $validated['name'], 'email' => $validated['email']]);
                if (array_key_exists('teacher_number', $validated) && $user->isGuru()) {
                    $user->teacher_number = $validated['teacher_number'];
                }
                if ($path || $request->boolean('remove_avatar')) {
                    $oldPath = $user->avatar;
                    $user->avatar = $path;
                }
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
                $user->save();
                app(AuditService::class)->record('account.profile_updated', $user);
            });
        } catch (\Throwable $e) {
            $files->delete($path, 'public');
            Log::warning('Profile update failed', ['error_type' => get_class($e)]);

            return back()->withErrors(['avatar' => 'Profil belum dapat disimpan. Foto sebelumnya tetap tersedia.']);
        }
        $files->delete($oldPath, 'public');

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
            'transfer_to' => 'nullable|integer',
        ]);

        $user = $request->user();

        try {
            app(AccountService::class)->delete($user, $request->integer('transfer_to') ?: null);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e->errorBag('userDeletion');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
