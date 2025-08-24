<?php

namespace Tanbhirhossain\LaravelLiveTerminal;

use Illuminate\Support\ServiceProvider;

class TerminalServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any package services.
     *
     * @return void
     */
    public function boot()
    {
        // 1. Load Routes
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

        // 2. Load Views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'terminal'); // The 'terminal' is the namespace

        // 3. Publishing assets
        if ($this->app->runningInConsole()) {
            // Publish config file
            $this->publishes([
                __DIR__.'/../config/terminal.php' => config_path('terminal.php'),
            ], 'terminal-config');

            // Publish views
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/terminal'),
            ], 'terminal-views');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Merge the package's config file with the application's
        $this->mergeConfigFrom(
            __DIR__.'/../config/terminal.php', 'terminal'
        );
    }
}