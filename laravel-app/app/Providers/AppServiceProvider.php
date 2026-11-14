<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Weather\WeatherProviderInterface::class,
            \App\Services\Weather\OpenMeteoProvider::class
        );
    }

    public function boot(): void
    {
        Blade::if('permission', function (string $permission) {
            return auth()->check() && auth()->user()->hasPermission($permission);
        });
    }
}
