<?php

declare(strict_types=1);

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;
use Satheez\DeployGuard\Support\CheckRegistry;
use Satheez\DeployGuard\Support\CheckRunner;

it('registers filters and validates check selectors', function (): void {
    $registry = new CheckRegistry;
    $registry->register(new RegistryCheck('env.app_key', 'env'));
    $registry->register(new RegistryCheck('mail.mailer', 'mail'));

    expect($registry->all())->toHaveCount(2)
        ->and($registry->enabled(['env']))->toHaveCount(1)
        ->and($registry->enabled(['env.app_key']))->toHaveCount(1)
        ->and($registry->enabled([], ['mail']))->toHaveCount(1)
        ->and($registry->invalidSelectors(['env', 'missing']))->toBe(['missing']);
});

it('prevents duplicate check keys', function (): void {
    $registry = new CheckRegistry;
    $registry->register(new RegistryCheck('env.app_key', 'env'));

    $registry->register(new RegistryCheck('env.app_key', 'env'));
})->throws(InvalidArgumentException::class);

it('converts unexpected check errors into failed results', function (): void {
    $report = (new CheckRunner)->run([new ThrowingRegistryCheck]);

    expect($report->failed())->toBe(1)
        ->and($report->results[0]->checkKey)->toBe('custom.throws')
        ->and($report->results[0]->details['exception'])->toBe(RuntimeException::class);
});

final readonly class RegistryCheck implements DeploymentCheck
{
    public function __construct(
        private string $key,
        private string $category,
    ) {}

    public function key(): string
    {
        return $this->key;
    }

    public function category(): string
    {
        return $this->category;
    }

    public function description(): string
    {
        return 'Registry test check';
    }

    public function run(): CheckResult
    {
        return CheckResult::pass($this->key(), $this->category(), $this->description(), 'Passed.');
    }
}

final class ThrowingRegistryCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'custom.throws';
    }

    public function category(): string
    {
        return 'custom';
    }

    public function description(): string
    {
        return 'Throwing check';
    }

    public function run(): CheckResult
    {
        throw new RuntimeException('Unexpected failure.');
    }
}
