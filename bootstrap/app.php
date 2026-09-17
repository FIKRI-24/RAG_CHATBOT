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
            if (app()->environment(['local', 'testing'])) {
                $hosts = array_merge($hosts, ['localhost', '127.0.0.1', '::1']);
            }

            return array_map(fn ($host) => '^'.preg_quote($host, '/').'$', array_unique($hosts));
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
