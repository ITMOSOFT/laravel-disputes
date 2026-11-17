<?php

namespace ITMOSOFT\Disputes;

use Illuminate\Support\ServiceProvider;

class DisputesServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/disputes.php', 'itmosoft-disputes');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/disputes.php' => config_path('itmosoft-disputes.php'),
        ], 'disputes-config');

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
