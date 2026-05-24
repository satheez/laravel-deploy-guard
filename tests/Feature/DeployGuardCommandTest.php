<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

it('runs successfully with readable output', function (): void {
    enableDeployGuardCategories(['env']);

    $exitCode = Artisan::call('deploy:guard');
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('Laravel Deploy Guard')
        ->and($output)->toContain('Environment: testing');
});

it('returns failure exit code for failed checks', function (): void {
    enableDeployGuardCategories(['env']);
    config()->set('app.env', 'production');
    config()->set('app.debug', true);

    $exitCode = Artisan::call('deploy:guard', ['--ci' => true]);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('APP_DEBUG is enabled');
});

it('supports json output', function (): void {
    enableDeployGuardCategories(['env']);

    $exitCode = Artisan::call('deploy:guard', ['--json' => true]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload)->toHaveKeys(['environment', 'status', 'summary', 'results'])
        ->and($payload['environment'])->toBe('testing')
        ->and($payload['summary'])->toHaveKeys(['total', 'passed', 'warnings', 'failed', 'skipped']);
});

it('supports only filter by category', function (): void {
    enableDeployGuardCategories(['env', 'mail']);

    $exitCode = Artisan::call('deploy:guard', [
        '--json' => true,
        '--only' => 'env',
    ]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload['results'])->not->toBeEmpty();

    foreach ($payload['results'] as $result) {
        expect($result['category'])->toBe('env');
    }
});

it('supports only filter by exact check key', function (): void {
    enableDeployGuardCategories(['env']);

    $exitCode = Artisan::call('deploy:guard', [
        '--json' => true,
        '--only' => 'env.app_key',
    ]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload['summary']['total'])->toBe(1)
        ->and($payload['results'][0]['check_key'])->toBe('env.app_key');
});

it('supports except filter', function (): void {
    enableDeployGuardCategories(['env', 'mail']);

    $exitCode = Artisan::call('deploy:guard', [
        '--json' => true,
        '--except' => 'mail',
    ]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and(array_column($payload['results'], 'category'))->not->toContain('mail');
});

it('supports fail on warning exit code', function (): void {
    enableDeployGuardCategories(['scheduler']);

    $exitCode = Artisan::call('deploy:guard', ['--fail-on' => 'warning']);

    expect($exitCode)->toBe(2)
        ->and(Artisan::output())->toContain('Scheduler execution cannot be confirmed');
});

it('uses ci fail on warning config', function (): void {
    enableDeployGuardCategories(['scheduler']);
    config()->set('deploy-guard.ci.fail_on_warning', true);

    $exitCode = Artisan::call('deploy:guard', ['--ci' => true]);

    expect($exitCode)->toBe(2);
});

it('rejects unsupported fail on value', function (): void {
    $exitCode = Artisan::call('deploy:guard', ['--fail-on' => 'notice']);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('Unsupported --fail-on value');
});

it('rejects invalid filters', function (): void {
    $exitCode = Artisan::call('deploy:guard', ['--only' => 'unknown']);

    expect($exitCode)->toBe(1)
        ->and(Artisan::output())->toContain('Unknown deploy guard check filter');
});

it('respects disabled package config', function (): void {
    config()->set('deploy-guard.enabled', false);

    $exitCode = Artisan::call('deploy:guard', ['--json' => true]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(0)
        ->and($payload['summary']['total'])->toBe(0)
        ->and($payload['results'])->toBe([]);
});

it('uses env option as target environment', function (): void {
    enableDeployGuardCategories(['env']);
    config()->set('app.debug', true);

    $exitCode = Artisan::call('deploy:guard', [
        '--env' => 'production',
        '--json' => true,
    ]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($exitCode)->toBe(1)
        ->and($payload['environment'])->toBe('production')
        ->and(array_column($payload['results'], 'status'))->toContain('fail');
});

it('does not expose configured secrets in output', function (): void {
    enableDeployGuardCategories(['env']);
    config()->set('app.key', 'base64:this-secret-value-must-not-appear');

    Artisan::call('deploy:guard', ['--json' => true]);

    expect(Artisan::output())->not->toContain('this-secret-value-must-not-appear');
});

it('respects category config values', function (): void {
    enableDeployGuardCategories(['env', 'queue']);
    config()->set('deploy-guard.checks.queue', false);

    Artisan::call('deploy:guard', ['--json' => true]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect(array_column($payload['results'], 'category'))->not->toContain('queue');
});

it('registers configured custom checks', function (): void {
    config()->set('deploy-guard.custom_checks', [PassingCustomCheck::class]);

    Artisan::call('deploy:guard', [
        '--json' => true,
        '--only' => 'custom.release_gate',
    ]);
    $payload = json_decode(Artisan::output(), true, 512, JSON_THROW_ON_ERROR);

    expect($payload['summary']['total'])->toBe(1)
        ->and($payload['results'][0]['check_key'])->toBe('custom.release_gate')
        ->and($payload['results'][0]['status'])->toBe('pass');
});

final class PassingCustomCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'custom.release_gate';
    }

    public function category(): string
    {
        return 'custom';
    }

    public function description(): string
    {
        return 'Custom release gate passes';
    }

    public function run(): CheckResult
    {
        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Custom release gate passed.',
        );
    }
}
