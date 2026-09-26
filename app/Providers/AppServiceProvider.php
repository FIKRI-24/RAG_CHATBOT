<?php

namespace App\Providers;

use App\Services\AuditService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Queue\Events\Looping;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(Login::class, function ($event) {
            app(AuditService::class)->record('account.login', $event->user, [], $event->user->id);
        });
        Event::listen(Logout::class, function ($event) {
            if ($event->user) {
                app(AuditService::class)->record('account.logout', $event->user, [], $event->user->id);
            }
        });
        Event::listen(Looping::class, function ($event) {
            if ($event->connectionName === 'rag') {
                Cache::put('rag-worker:heartbeat', time(), 390);
            }
        });
        $proxies = config('security.trusted_proxies', []);
        TrustProxies::at(in_array('*', $proxies, true) ? '*' : $proxies);
        ResetPassword::createUrlUsing(function ($user, string $token) {
            return rtrim(config('app.url'), '/').'/reset-password/'.rawurlencode($token).'?'.http_build_query(['email' => $user->getEmailForPasswordReset()]);
        });
        Event::listen(PasswordReset::class, function ($event) {
            app(AuditService::class)->record('account.password_reset', $event->user);
        });
        RateLimiter::for('account-entry', fn ($request) => Limit::perMinute(6)->by($request->ip()));

        if (! $this->app->runningInConsole() && ! $this->app->runningUnitTests()) {
            try {
                \App\Models\Module::ensureKb4Module();
            } catch (\Throwable) {
                // Ignore during bootstrap if DB is not ready
            }
        }
    }
}
