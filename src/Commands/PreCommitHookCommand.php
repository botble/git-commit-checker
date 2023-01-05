<?php

namespace Botble\GitCommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;

#[AsCommand('git-commit-checker:pre-commit-hook', 'Git hook before commit')]
class PreCommitHookCommand extends Command
{
    public function handle(): int
    {
        if (! $this->laravel['config']->get('git-commit-checker.enabled')) {
            $this->components->info('git-commit-hook is disabled. Skipped.');

            return self::SUCCESS;
        }

        $uncommittedFiles = $this->uncommittedFiles();

        if (! count($uncommittedFiles)) {
            $this->components->info('No files to check coding standard. Skipped.');

            return self::SUCCESS;
        }

        $this->components->info('Running Laravel Pint...');

        $command = [
            $this->laravel->basePath('vendor/bin/pint'),
            '--test',
            '-v',
        ];

        $command = array_merge($command, $uncommittedFiles);

        $process = $this->getProcess($command);

        $process->setTty(true);

        $process->run();

        if (! $process->isSuccessful()) {
            $this->components->warn('Run <comment>./vendor/bin/pint --dirty</comment> to fix coding standard issues');

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    protected function getProcess(array $command): Process
    {
        return (new Process($command, $this->laravel->basePath()))->setTimeout(null);
    }

    protected function uncommittedFiles(): array
    {
        $process = tap(new Process(['git', 'status', '--short', '--', '*.php']))->run();

        if (! $process->isSuccessful()) {
            $this->components->error('git-commit-checker is only available when using Git.');
            abort(1);
        }

        return collect(preg_split('/\R+/', $process->getOutput(), flags: PREG_SPLIT_NO_EMPTY))
            ->mapWithKeys(fn ($file) => [substr($file, 3) => trim(substr($file, 0, 3))])
            ->reject(fn ($status) => $status === 'D')
            ->map(fn ($status, $file) => $status === 'R' ? Str::after($file, ' -> ') : $file)
            ->map(fn ($file) => $this->laravel->basePath($file))
            ->values()
            ->all();
    }
}
