<?php

namespace Botble\GitCommitChecker\Providers;

use Illuminate\Support\ServiceProvider;

class GitCommitCheckerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/git-commit-checker.php',
            'git-commit-checker'
        );
    }
    public function boot()
    {
        $this->app->register(CommandServiceProvider::class);

        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'git-commit-checker');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/git-commit-checker.php' => config_path('git-commit-checker.php'),
            ], 'git-commit-checker-config');
        }
    }
}
