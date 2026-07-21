<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Force HTTPS in production (fixes Mixed Content errors on Railway)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}
