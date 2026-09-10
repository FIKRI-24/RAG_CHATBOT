<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\User;
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
                if ($path || $request->boolean('remove_avatar')) {
                    $oldPath = $user->avatar;
                    $user->avatar = $path;
                }
                if ($user->isDirty('email')) {
                    $user->email_verified_at = null;
                }
                $user->save();
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
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
