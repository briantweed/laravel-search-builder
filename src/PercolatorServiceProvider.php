<?php

namespace briantweed;

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
            __DIR__ . '/config/percolator.php' => config_path('percolator.php'),
        ], 'percolator');
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
            'percolator'
        );

        $this->app->singleton(Percolator::class);
    }
}