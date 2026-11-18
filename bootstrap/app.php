<?php

use App\Http\Middleware\EnsureActiveAccount;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SchoolEmailVerification;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustHosts(at: function () {
            $hosts = config('security.trusted_hosts', []);
            $hosts[] = parse_url(config('app.url'), PHP_URL_HOST) ?: 'localhost';
            $allowLan = app()->environment(['local', 'testing']) || config('security.allow_lan_hosts', true);
            if ($allowLan) {
                $hosts = array_merge($hosts, ['localhost', '127.0.0.1', '::1']);
            }

            $patterns = array_map(fn ($host) => '^'.preg_quote($host, '/').'$', array_unique($hosts));
            if ($allowLan) {
                $patterns[] = '^(10(?:\.\d{1,3}){3}|192\.168(?:\.\d{1,3}){2}|172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})$';
            }

            return $patterns;
        }, subdomains: false);
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'active' => EnsureActiveAccount::class,
            'school.verified' => SchoolEmailVerification::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
