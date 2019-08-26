<?php

namespace Botble\GitCommitChecker\Providers;

use Illuminate\Support\ServiceProvider;
use Botble\GitCommitChecker\Commands\InstallHooks;
use Botble\GitCommitChecker\Commands\PreCommitHook;

class CommandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallHooks::class,
                PreCommitHook::class,
            ]);
        }
    }
}
