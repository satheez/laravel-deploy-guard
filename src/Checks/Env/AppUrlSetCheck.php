<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Env;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class AppUrlSetCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'env.app_url';
    }

    public function category(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'APP_URL is configured';
    }

    public function run(): CheckResult
    {
        $url = trim((string) config('app.url'));

        if ($url === '') {
            $status = $this->isProductionEnvironment() ? 'warning' : 'skipped';

            return CheckResult::{$status}(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_URL is not configured.',
                suggestion: 'Set APP_URL to the public application URL for the target environment.',
            );
        }

        if ($this->isProductionEnvironment() && $this->isLocalUrl($url)) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_URL appears to point to a local address in production.',
                suggestion: 'Set APP_URL to the public production URL.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'APP_URL is configured.',
        );
    }

    private function isLocalUrl(string $url): bool
    {
        return str_contains($url, 'localhost')
            || str_contains($url, '127.0.0.1')
            || str_contains($url, '::1');
    }
}
