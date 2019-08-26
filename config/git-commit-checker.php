<?php

use Botble\GitCommitChecker\Commands\PreCommitHook;

return [
    'enabled' => env('GIT_COMMIT_CHECKER_ENABLED', true),
    'psr2'    => [
        'standard' => __DIR__ . '/../phpcs.xml',
        'ignored'  => [
            '*/database/*',
            '*/public/*',
            '*/assets/*',
            '*/vendor/*',
        ],
    ],
    'hooks'   => [
        'pre-commit' => PreCommitHook::class,
    ],
];
