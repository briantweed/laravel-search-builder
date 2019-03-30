<?php

namespace briantweed\LaravelSearchBuilder;

use Illuminate\Support\ServiceProvider;

class LaravelSearchBuilderServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/builder.php' => config_path('builder.php'),
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
            __DIR__.'/config/builder.php',
            'builder'
        );

        $this->app->singleton(SearchBuilder::class);
    }
}