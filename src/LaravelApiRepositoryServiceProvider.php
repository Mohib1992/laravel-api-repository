<?php
namespace Mohib\LaravelApiRepository;

use Illuminate\Support\ServiceProvider;

class LaravelApiRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-api-repository.php', 'laravel-api-repository');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-api-repository.php' => config_path('laravel-api-repository.php'),
        ]);
    }
}
