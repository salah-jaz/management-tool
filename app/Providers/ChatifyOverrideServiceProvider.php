<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;

class ChatifyOverrideServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Override Chatify routes with empty routes to prevent conflicts
        Route::prefix('chatify')->group(function () {
            Route::get('/', function () {
                return response('Chatify disabled', 404);
            });
        });
    }
}


