<?php

declare(strict_types=1);

return [
    'preset' => 'laravel',
    'ide' => 'vscode',
    'exclude' => [
        'vendor',
        'storage',
        'bootstrap/cache',
        'node_modules',
        'tests',
        'database/migrations',
        'database/seeders',
    ],
    'add' => [
        //  ExampleMetric::class => [
        //      ExampleInsight::class,
        //  ]
    ],
    'remove' => [
        //  ExampleInsight::class,
    ],
    'config' => [
        //  ExampleInsight::class => [
        //      'key' => 'value',
        //      ],
    ],
];
