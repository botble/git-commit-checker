<?php

namespace Botble\GitCommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use JakubOnderka\PhpParallelLint\ConsoleWriter;
use JakubOnderka\PhpParallelLint\TextOutputColored;
use RuntimeException;

class PreCommitHook extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:pre-commit-hook';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hook before commit GIT';

    /**
     * Execute the console command.
     *
     * @return bool
     * @throws \JakubOnderka\PhpConsoleColor\InvalidStyleException
     */
    public function handle()
    {
        if (!config('git-commit-checker.enabled')) {
            return false;
        }

        $changed = $this->getChangedPhpFiles();

        $output = new TextOutputColored(new ConsoleWriter);

        if (empty($changed)) {
            $output->writeLine('Success: Nothing to check!', TextOutputColored::TYPE_OK);

            return false;
        }

        $this->info('Running PHP lint...');
        if (!$this->lint($changed)) {
            exit($this->fails());
        }

        $this->info('Checking PSR-2 Coding Standard...');
        $start = now();
        if (!$this->checkPsr2($changed)) {
            exit($this->fails());
        }
        $end = now();
        $this->output->writeln('Checked ' . count($changed) . ' file(s) in ' . $end->diffInRealSeconds($start) . ' second(s)');

        $output->writeLine('Your code is perfect, no syntax error found!', TextOutputColored::TYPE_OK);

        return true;
    }

    /**
     * Get a list of changed PHP files.
     *
     * @return array
     */
    protected function getChangedPhpFiles(): array
    {
        $changed = [];

        foreach ($this->getChangedFiles() as $path) {
            if (Str::endsWith($path, '.php') && !Str::endsWith($path, '.blade.php')) {
                $changed[] = $path;
            }
        }

        return $changed;
    }

    /**
     * Get a list of changed files.
     *
     * @return array
     */
    protected function getChangedFiles(): array
    {
        if (!$this->exec($cmd = 'git status --short', $output)) {
            throw new RuntimeException('Unable to run command: ' . $cmd);
        }

        $changed = [];

        foreach ($output as $line) {
            if ($path = $this->parseGitStatus($line)) {
                $changed[] = $path;
            }
        }

        return $changed;
    }

    /**
     * Execute the command, return true if status is success, false otherwise.
     *
     * @param string $command
     * @param array &$output
     * @param int &$status
     * @return bool
     */
    protected function exec(string $command, &$output = null, &$status = null): bool
    {
        exec($command, $output, $status);

        return $status == 0;
    }

    /**
     * Parses the git status line and return the changed file or null if the
     * file hasn't changed.
     *
     * @param string $line
     * @return string|null
     */
    protected function parseGitStatus(string $line): ?string
    {
        if (!preg_match('/^(.)(.)\s(\S+)(\s->\S+)?$/', $line, $matches)) {
            return null; // ignore incorrect lines
        }

        [, $first, , $path] = $matches;

        if (!in_array($first, ['M', 'A'])) {
            return null;
        }

        return $path;
    }

    /**
     * Lint the given files (using JakubOnderka/PHP-Parallel-Lint).
     * @see https://github.com/JakubOnderka/PHP-Parallel-Lint
     *
     * @param array $changed
     * @return bool
     */
    protected function lint(array $changed): bool
    {
        $process = $this->openParallelLintProcess($pipes);

        foreach ($changed as $path) {
            fwrite($pipes[0], $path . "\n");
        }

        fclose($pipes[0]);

        if (false === $output = stream_get_contents($pipes[1])) {
            throw new RuntimeException('Unable to get the lint result');
        }

        if (!$this->option('quiet') && trim($output)) {
            $this->output->writeln(trim($output));
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process) === 0;
    }

    /**
     * Opens the parallel-lint program as a process and return the resource
     * (the pipes can be obtained as an out-argument).
     *
     * @param array &$pipes
     * @return resource
     */
    protected function openParallelLintProcess(&$pipes = null)
    {
        $options = [
            '--stdin',
            '--no-progress',
        ];

        if (!$this->option('no-ansi')) {
            $options[] = '--colors';
        }

        $cmd = base_path('vendor/bin/parallel-lint') . ' ' . implode(' ', $options);

        return $this->openProcess($cmd, $pipes);
    }

    /**
     * Open a process and give the pipes to stdin, stdout, stderr in $pipes
     * out-parameter. Returns the opened process as a resource.
     *
     * @param string $cmd
     * @param array &$pipes
     * @return resource
     */
    protected function openProcess(string $cmd, &$pipes = null)
    {
        $descriptionOrSpec = [
            0 => ['pipe', 'r'],  // stdin is a pipe that the child will read from
            1 => ['pipe', 'w'],  // stdout is a pipe that the child will write to
            2 => ['pipe', 'w'],  // stderr is a pipe that the child will write to
        ];

        return proc_open($cmd, $descriptionOrSpec, $pipes);
    }

    /**
     * Command failed message, returns 1.
     *
     * @return int
     */
    protected function fails()
    {
        $message = 'Commit aborted: you have errors in your code!';

        if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' && $this->exec('which cowsay')) {
            $this->exec('cowsay -f unipony-smaller "{$message}"', $output);
            $message = implode("\n", $output);
        }

        $this->output->writeln('<fg=red>' . $message . '</fg=red>');

        return 1;
    }

    /**
     * Checks the PSR-2 compliance of changed files.
     *
     * @param array $changed
     * @return bool
     */
    protected function checkPsr2(array $changed): bool
    {
        $ignored = config('git-commit-checker.psr2.ignored');

        $options = [
            '--standard=' . config('git-commit-checker.psr2.standard'),
            '--ignore=' . implode(',', $ignored),
        ];

        if (!$this->option('no-ansi')) {
            $options[] = '--colors';
        }

        $cmd = base_path('vendor/bin/phpcs') . ' ' . implode(' ', $options) . ' ' . implode(' ', $changed);

        $status = $this->exec($cmd, $output);

        if (!$this->option('quiet') && $output) {
            $this->output->writeln(implode("\n", $output));
        }

        return $status;
    }
}
