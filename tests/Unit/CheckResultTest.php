<?php

declare(strict_types=1);

use Satheez\DeployGuard\Results\CheckResult;
use Satheez\DeployGuard\Results\CheckStatus;
use Satheez\DeployGuard\Support\CheckReport;
use Satheez\DeployGuard\Support\JsonFormatter;
use Satheez\DeployGuard\Support\ReportFormatter;

it('serializes check results correctly', function (): void {
    $result = CheckResult::fail(
        checkKey: 'env.app_debug',
        category: 'env',
        title: 'APP_DEBUG is enabled in production',
        message: 'APP_DEBUG should be false in production.',
        suggestion: 'Set APP_DEBUG=false before deploying to production.',
        details: ['production' => true],
    );

    expect($result->status)->toBe(CheckStatus::Fail)
        ->and($result->toArray())->toBe([
            'status' => 'fail',
            'check_key' => 'env.app_debug',
            'category' => 'env',
            'title' => 'APP_DEBUG is enabled in production',
            'message' => 'APP_DEBUG should be false in production.',
            'details' => ['production' => true],
            'suggestion' => 'Set APP_DEBUG=false before deploying to production.',
        ])
        ->and(json_decode(json_encode($result, JSON_THROW_ON_ERROR), true, 512, JSON_THROW_ON_ERROR))
        ->toBe($result->toArray());
});

it('summarizes reports and exit codes', function (): void {
    $report = new CheckReport([
        CheckResult::pass('a', 'env', 'A', 'A passed.'),
        CheckResult::warning('b', 'queue', 'B', 'B warned.'),
        CheckResult::fail('c', 'mail', 'C', 'C failed.'),
        CheckResult::skipped('d', 'cache', 'D', 'D skipped.'),
    ]);

    expect($report->summary())->toBe([
        'total' => 4,
        'passed' => 1,
        'warnings' => 1,
        'failed' => 1,
        'skipped' => 1,
    ])
        ->and($report->status())->toBe('fail')
        ->and($report->exitCode())->toBe(1)
        ->and($report->exitCode(true))->toBe(1);
});

it('returns warning status and strict warning exit code when only warnings exist', function (): void {
    $report = new CheckReport([
        CheckResult::warning('scheduler.validation', 'scheduler', 'Scheduler', 'Scheduler warning.'),
    ]);

    expect($report->status())->toBe('warning')
        ->and($report->status(true))->toBe('fail')
        ->and($report->exitCode())->toBe(0)
        ->and($report->exitCode(true))->toBe(2);
});

it('formats console and json reports', function (): void {
    $report = new CheckReport([
        CheckResult::warning(
            checkKey: 'queue.connection',
            category: 'queue',
            title: 'Queue connection is production-safe',
            message: 'Queue connection is sync in production.',
            suggestion: 'Use an async queue driver.',
            details: ['connection' => 'sync'],
        ),
    ]);

    $console = (new ReportFormatter)->format($report, 'production');
    $json = json_decode((new JsonFormatter)->format($report, 'production'), true, 512, JSON_THROW_ON_ERROR);

    expect($console)->toContain('Laravel Deploy Guard')
        ->and($console)->toContain('Details: connection=sync')
        ->and($json['environment'])->toBe('production')
        ->and($json['results'][0]['check_key'])->toBe('queue.connection');
});
