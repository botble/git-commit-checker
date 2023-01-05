<?php

namespace Botble\GitCommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputOption;

#[AsCommand('git-commit-checker:install', 'Install "pre_commit" hook into your git.')]
class InstallCommand extends Command
{
    use ConfirmableTrait;

    public function handle(): int
    {
        if (! $this->laravel->isLocal()) {
            $this->components->error('git-commit-checker is only available in local environment.');

            return self::FAILURE;
        }

        if (! $this->laravel['files']->isDirectory($this->laravel->basePath('.git'))) {
            $this->components->error('git-commit-checker is only available when using Git.');

            return self::FAILURE;
        }

        foreach ($this->laravel['config']->get('git-commit-checker.hooks') as $hook => $command) {
            $this->install($hook, $command)
                ? $this->components->info("Hook [$hook] is installed successfully.")
                : $this->components->error("Unable to install hook [$hook].");
        }

        $pintConfigFilePath = $this->laravel->basePath('pint.json');

        if ($this->laravel['files']->exists($pintConfigFilePath)) {
            if ($this->components->confirm('A pint.json exists. Do you want to overwrite this file?')) {
                $this->generatePintConfiguration($pintConfigFilePath);
            }

            return self::SUCCESS;
        }

        if ($this->components->confirm('A pint.json does not exists. Do you want to create this file?')) {
            $this->generatePintConfiguration($pintConfigFilePath);
        }

        return self::SUCCESS;
    }

    protected function install(string $hook, string $class): bool
    {
        if (! class_exists($class)) {
            $this->components->error("Class [$class] not found.");
            abort(1);
        }

        $command = new $class();

        if ($command instanceof Command === false) {
            $this->components->error("Class [$class] is not instance of " . Command::class . '.');
            abort(1);
        }

        $script = $this->generateHookScript($command->getName());

        $path = $this->laravel->basePath('.git/hooks/' . $hook);

        if (
            $this->laravel['files']->exists($path) &&
            ! $this->confirmToProceed($path . ' already exists, do you want to overwrite it?', true)
        ) {
            return false;
        }

        return $this->writeHookScript($path, $script);
    }

    protected function generateHookScript(string $signature): string
    {
        $artisan = addslashes($this->laravel->basePath('artisan'));

        return "#!/bin/sh\n\nphp $artisan $signature\n";
    }

    protected function generatePintConfiguration(string $path): void
    {
        $presets = [
            'laravel' => 'Laravel',
            'symfony' => 'Symfony',
            'psr12' => 'PSR-12',
            'psr2' => 'PSR-2',
            'recommended' => 'Recommended (PSR-12 Extended)',
        ];

        $standard = $this->components->choice('Which standard you want to use?', array_values($presets), 0);
        $preset = array_flip($presets)[$standard];

        if (! $this->laravel['files']->put(
            $path,
            json_encode(
                $standard !== 'recommended'
                    ? ['preset' => $preset]
                    : $this->laravel['config']->get('git-commit-checker.recommended_preset'),
                JSON_PRETTY_PRINT
            )
        )) {
            $this->components->error('Unable to write ' . $path);
            abort(1);
        }

        $this->components->info("Created [$path] using $presets[$preset] preset successfully");
    }

    protected function writeHookScript(string $path, string $script): bool
    {
        if (! $this->laravel['files']->put($path, $script)) {
            return false;
        }

        if (! $this->laravel['files']->chmod($path, 0755)) {
            return false;
        }

        return true;
    }

    protected function configure(): void
    {
        $this->addOption('force', 'f', InputOption::VALUE_NONE, 'Force the operation to overwrite existing git hook files');
    }
}
