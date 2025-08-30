<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Services
        $this->app->bind(\App\Services\AuthService::class, \App\Services\AuthService::class);
        $this->app->bind(\App\Services\PlayerService::class, \App\Services\PlayerService::class);
        $this->app->bind(\App\Services\SituationService::class, \App\Services\SituationService::class);
        $this->app->bind(\App\Services\MicroActionService::class, \App\Services\MicroActionService::class);
        $this->app->bind(\App\Services\AdminService::class, \App\Services\AdminService::class);
        $this->app->bind(\App\Services\AnalyticsService::class, \App\Services\AnalyticsService::class);

        // Repositories
        $this->app->bind(\App\Repositories\PlayerRepository::class, \App\Repositories\PlayerRepository::class);
        $this->app->bind(\App\Repositories\SituationRepository::class, \App\Repositories\SituationRepository::class);
        $this->app->bind(\App\Repositories\MicroActionRepository::class, \App\Repositories\MicroActionRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
