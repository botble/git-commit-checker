<?php

namespace Botble\GitCommitChecker\Commands;

use Illuminate\Console\Application;
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
            if ($this->components->confirm('A <comment>pint.json</comment> exists. Do you want to overwrite this file?')) {
                $this->generatePintConfiguration($pintConfigFilePath);
            }

            return self::SUCCESS;
        }

        if ($this->components->confirm('A <comment>pint.json</comment> does not exists. Do you want to create this file?')) {
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
        $relativePath = ltrim(str_replace($this->laravel->basePath(), '', $path), DIRECTORY_SEPARATOR);

        if (
            $this->laravel['files']->exists($path) &&
            ! $this->confirmToProceed($relativePath . ' already exists, do you want to overwrite it?', true)
        ) {
            return false;
        }

        return $this->writeHookScript($path, $script);
    }

    protected function generateHookScript(string $signature): string
    {
        return sprintf("#!/bin/sh\n\n%s\n", Application::formatCommandString($signature));
    }

    protected function generatePintConfiguration(string $path): void
    {
        $presets = $this->laravel['config']->get('git-commit-checker.pint.presets', []);

        if (empty($presets)) {
            $this->components->error('Do not found a list of supported presets');
            abort(1);
        }

        $standard = $this->components->choice('Which standard you want to use?', array_values($presets), 0);

        $preset = array_flip($presets)[$standard];

        if (! $this->laravel['files']->put(
            $path,
            json_encode(
                $preset !== 'recommended'
                    ? ['preset' => $preset]
                    : $this->laravel['config']->get('git-commit-checker.pint.recommended_preset'),
                JSON_PRETTY_PRINT
            ) . PHP_EOL
        )) {
            $this->components->error('Unable to write ' . $path);
            abort(1);
        }

        $this->components->info(
            "Created <comment>$path</comment> using <comment>$presets[$preset]</comment> preset successfully"
        );
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
