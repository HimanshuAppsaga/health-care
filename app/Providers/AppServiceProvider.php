<?php

namespace App\Providers;

use App\Boost\Agents\Antigravity;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Laravel\Boost\BoostManager;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (class_exists(BoostManager::class)) {
            $this->app->make(BoostManager::class)->registerAgent('antigravity', Antigravity::class);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);
    }
}
