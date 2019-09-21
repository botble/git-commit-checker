<?php

namespace Botble\GitCommitChecker\Commands;

use RuntimeException;
use Illuminate\Console\Command;

class InstallPhpcs extends Command
{
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
    protected $description = 'Create Phpcs.xml ';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $rootphpcs = base_path('phpcs.xml');
        $phpcs = base_path('vendor/botble/git-commit-checker/phpcs.xml');

        if (file_exists($rootphpcs)) {
            if (!$this->confirmToProceed('phpcs.xml already exists, do you want to overwrite it?', true)) {
                return false;
            }            
            unlink($rootphpcs);
        }   

        $this->writePHPCS($phpcs, $rootphpcs);
    }

    protected function writePHPCS(string $phpcs, string $rootphpcs): bool
    {    
                
        if (!copy($phpcs, $rootphpcs)) { 
            return false;
        } 
        else { 
            return true;
        }
        
    }
}