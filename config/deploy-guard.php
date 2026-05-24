<?php

declare(strict_types=1);

return [
    'enabled' => env('DEPLOY_GUARD_ENABLED', true),

    'production_environments' => [
        'production',
        'prod',
    ],

    'checks' => [
        'env' => true,
        'database' => true,
        'migrations' => true,
        'queue' => true,
        'cache' => true,
        'storage' => true,
        'mail' => true,
        'filesystem' => true,
        'scheduler' => true,
    ],

    'allow' => [
        'sync_queue_in_production' => false,
        'array_cache_in_production' => false,
        'log_mailer_in_production' => false,
        'array_mailer_in_production' => false,
    ],

    'storage' => [
        'check_public_link' => true,
    ],

    'filesystem' => [
        'require_cloud_disk' => false,
    ],

    'migrations' => [
        'pending_status' => 'warning',
    ],

    'queue' => [
        'failed_jobs_warning_threshold' => 1,
    ],

    'scheduler' => [
        'validate' => true,
    ],

    'ci' => [
        'fail_on_warning' => false,
    ],

    'custom_checks' => [
        // App\DeployGuard\Checks\CustomDeploymentCheck::class,
    ],
];
