<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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
        // Forzar HTTPS en producción o cuando se usa un proxy como ngrok
        if (env('APP_ENV') !== 'local' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) || isset($_SERVER['HTTP_X_FORWARDED_PORT'])) {
            URL::forceScheme('https');
        }
    }
}
