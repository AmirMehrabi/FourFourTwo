<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
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
        Vite::prefetch(concurrency: 3);
        // Ensure session cookie name is ASCII-only (Laravel derives from APP_NAME which here contains non-ASCII chars).
        // Non-ASCII cookie names can be ignored by some clients / intermediaries causing 419 after login.
        if (config('session.cookie') && preg_match('/[^A-Za-z0-9_\-]/', config('session.cookie'))) {
            config(['session.cookie' => 'fft_session']);
        }
    }
}
