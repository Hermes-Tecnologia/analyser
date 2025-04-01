<?php

namespace HermesTecnologia\Analyser;

use HermesTecnologia\Analyser\Facades\Analyser;
use Illuminate\Support\ServiceProvider;

class AnalyserServiceProvider extends ServiceProvider
{
    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot(): void
    {
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'hermes-tecnologia');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'hermes-tecnologia');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        // Publishing is only necessary when using the CLI.
        if ($this->app->runningInConsole()) {
            $this->bootForConsole();
        }
    }

    /**
     * Register any package services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/analyser.php', 'analyser');

        // Register the service the package provides.
        $this->app->singleton('analyser', function ($app) {
            return new Analyser;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['analyser'];
    }

    /**
     * Console-specific booting.
     *
     * @return void
     */
    protected function bootForConsole(): void
    {
        // Publishing the configuration file.
        $this->publishes([
            __DIR__.'/../config/analyser.php' => config_path('analyser.php'),
        ], 'analyser.config');

        
        // Publishing the views.
        /*$this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/hermes-tecnologia'),
        ], 'analyser.views');*/

        // Publishing assets.
        /*$this->publishes([
            __DIR__.'/../resources/assets' => public_path('vendor/hermes-tecnologia'),
        ], 'analyser.assets');*/

        // Publishing the translation files.
        /*$this->publishes([
            __DIR__.'/../resources/lang' => resource_path('lang/vendor/hermes-tecnologia'),
        ], 'analyser.lang');*/

        // Registering package commands.
        // $this->commands([]);
    }
}
