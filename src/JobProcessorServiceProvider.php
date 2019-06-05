<?php

namespace MaksimM\JobProcessor;

use Exception;
use Illuminate\Support\ServiceProvider;
use MaksimM\JobProcessor\Commands\JobProcessor;

class JobProcessorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @throws Exception
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            if (!str_contains($this->app->version(), 'Lumen')) {
                $this->publishes(
                    [
                        __DIR__.'/../config/job-processor.php' => config_path('job-processor.php'),
                    ],
                    'config'
                );
            }
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/job-processor.php', 'job-processor');

        /*
         * @var Router $router
         */
        if (str_contains($this->app->version(), 'Lumen') && !property_exists($this->app, 'router')) {
            $router = $this->app;
        } else {
            $router = $this->app->make('router');
        }

        $this->commands(
            [
                JobProcessor::class,
            ]
        );

        $router->group(
            [
                'middleware' => 'auth',
                'namespace'  => 'MaksimM\JobProcessor\Http\Controllers',
            ],
            function ($router) {
                require __DIR__.'/Http/routes.php';
            }
        );

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }
}
