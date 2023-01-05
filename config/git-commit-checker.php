<?php

use Botble\GitCommitChecker\Commands\PreCommitHookCommand;

return [
    'enabled' => env('GIT_COMMIT_CHECKER_ENABLED', true),

    'hooks' => [
        'pre-commit' => PreCommitHookCommand::class,
    ],

    'pint' => [
        'presets' => [
            'laravel' => 'Laravel (Default)',
            'symfony' => 'Symfony',
            'psr12' => 'PSR-12',
            'recommended' => 'Recommended (PSR-12 Extended)',
        ],

        'recommended_preset' => [
            'preset' => 'psr12',
            'rules' => [
                'array_syntax' => ['syntax' => 'short'],
                'binary_operator_spaces' => [
                    'default' => 'single_space',
                    'operators' => [
                        '=' => null,
                    ],
                ],
                'blank_line_before_statement' => [
                    'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
                ],
                'concat_space' => [
                    'spacing' => 'one',
                ],
                'function_typehint_space' => true,
                'native_function_casing' => true,
                'no_extra_blank_lines' => true,
                'no_leading_namespace_whitespace' => true,
                'no_spaces_around_offset' => true,
                'no_unused_imports' => true,
                'not_operator_with_successor_space' => true,
                'object_operator_without_whitespace' => true,
                'single_quote' => true,
                'trailing_comma_in_multiline' => true,
                'unary_operator_spaces' => true,
                'whitespace_after_comma_in_array' => true,
            ],
        ],
    ],
];
