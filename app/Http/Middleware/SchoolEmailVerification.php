<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SchoolEmailVerification
{
    public function handle(Request $request, Closure $next)
    {
        if (config('security.require_verified_email') && ! $request->user()->hasVerifiedEmail()) {
            return $request->expectsJson()
                ? response()->json(['success' => false, 'message' => 'Verifikasi email sebelum menggunakan aplikasi.'], 403)
                : redirect()->route('verification.notice');
        }

        return $next($request);
    }
}
