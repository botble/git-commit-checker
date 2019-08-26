<?php

namespace Botble\GitCommitChecker\Providers;

use Illuminate\Support\ServiceProvider;

class GitCommitCheckerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/git-commit-checker.php', 'git-commit-checker');

        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__ . '/../../config/git-commit-checker.php' => config_path('git-commit-checker.php')], 'config');
        }

        $this->app->register(CommandServiceProvider::class);
    }
}
