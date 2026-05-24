<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class)->in('Feature', 'Unit');

function enableDeployGuardCategories(array $enabledCategories): void
{
    $categories = [
        'env',
        'database',
        'migrations',
        'queue',
        'cache',
        'storage',
        'mail',
        'filesystem',
        'scheduler',
    ];

    $checks = array_fill_keys($categories, false);

    foreach ($enabledCategories as $category) {
        $checks[$category] = true;
    }

    config()->set('deploy-guard.checks', $checks);
}
