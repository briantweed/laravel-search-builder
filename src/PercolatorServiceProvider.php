<?php

namespace briantweed\Percolator;

use Illuminate\Support\ServiceProvider;

class PercolatorServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/percolator.php' => config_path('builder.php'),
        ], 'builder');
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/percolator.php',
            'builder'
        );

        $this->app->singleton(Percolator::class);
    }
}