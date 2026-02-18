<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Request;
use App\Observers\RequestObserver;

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
        Request::observe(RequestObserver::class);
    }
}
