<?php

declare(strict_types=1);

namespace Laravelplus\Fortress;

use Illuminate\Support\ServiceProvider;
use Laravelplus\Fortress\Commands\InstallFortressCommand;

final class FortressServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'fortress');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'fortress');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('fortress.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/fortress'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/fortress'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/fortress'),
            ], 'lang');*/

            // Registering package commands.
            // Register the installation command
            $this->commands([
                InstallFortressCommand::class,
            ]);
        }

    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'fortress');

        // Register the main class to use with the facade
        $this->app->singleton('fortress', fn () => new Fortress());
    }
}
