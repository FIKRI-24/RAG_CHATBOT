<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AccountService;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    /**
     * Update the user's password.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validateWithBag('updatePassword', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'max:1024', Password::defaults(), 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($request, $validated) {
            $user = User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
            abort_unless(Hash::check($validated['current_password'], $user->password), 409);
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
            app(AccountService::class)->revokeSessions($user);
            app(AuditService::class)->record('account.password_changed', $user);

            return $user;
        });
        Auth::setUser($user);
        $request->session()->regenerate();
        $request->session()->put('auth_version', (int) $user->auth_version);
        $request->session()->put('password_hash_web', $user->password);

        return back()->with('status', 'password-updated');
    }
}
