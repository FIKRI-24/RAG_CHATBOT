<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $version = $request->session()->get('auth_version');
            if (! $user->is_active || ($version !== null && (int) $version !== (int) $user->auth_version)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return $request->expectsJson()
                    ? response()->json(['success' => false, 'message' => 'Sesi berakhir atau akun dinonaktifkan. Silakan masuk kembali.'], 401)
                    : redirect()->route('login')->withErrors(['email' => 'Sesi berakhir atau akun dinonaktifkan.']);
            }
            $request->session()->put('auth_version', (int) $user->auth_version);
        }

        return $next($request);
    }
}
