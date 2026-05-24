<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Cache;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class RoutesCachedCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'cache.routes';
    }

    public function category(): string
    {
        return 'cache';
    }

    public function description(): string
    {
        return 'Routes are cached in production';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Route cache validation was skipped outside a production environment.',
            );
        }

        if (! app()->routesAreCached()) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Routes are not cached.',
                suggestion: 'Run php artisan route:cache during deployment if your application supports route caching.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Routes are cached.',
        );
    }
}
