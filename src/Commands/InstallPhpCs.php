<?php

namespace Botble\GitCommitChecker\Commands;

use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class InstallPhpCs extends Command
{
    use ConfirmableTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'git:create-phpcs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create default phpcs.xml';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $phpCs = __DIR__ . '/../../phpcs.xml';
        $rootPhpCs = base_path('phpcs.xml');

        // Checkout existence of sample phpcs.xml.
        if (!file_exists($phpCs)) {
            $this->error('The sample phpcs.xml does not exist! Try to reinstall botble/git-commit-checker package!');

            return 1;
        }

        // Checkout existence phpcs.xml in root path of project.
        if (file_exists($rootPhpCs)) {
            if (!$this->confirmToProceed('phpcs.xml already exists, do you want to overwrite it?', true)) {
                return 1;
            }

            // Remove old phpcs.xml file form root
            unlink($rootPhpCs);
        }

        $this->writePHPCS($phpCs, $rootPhpCs)
            ? $this->info('Phpcs.xml successfully created!')
            : $this->error('Unable to create phpcs.xml');

        return 0;
    }

    /**
     * Copy phpcs.xml file to root and return true on success, false otherwise.
     *
     * @param string $phpcs
     * @param string $rootphpcs
     * @return bool
     */
    protected function writePHPCS(string $phpCs, string $rootPhpCs): bool
    {
        // phpcs.xml file to root
        if (!copy($phpCs, $rootPhpCs)) {
            return false;
        }

        return true;
    }
}
