<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\BandcampService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BandcampService::class, function ($app) {
            return new BandcampService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
