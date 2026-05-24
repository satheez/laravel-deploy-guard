<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Cache;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class CacheDriverCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'cache.driver';
    }

    public function category(): string
    {
        return 'cache';
    }

    public function description(): string
    {
        return 'Cache driver is production-safe';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Cache production validation was skipped outside a production environment.',
            );
        }

        $store = (string) config('cache.default');

        if ($store === 'array' && ! $this->isAllowed('array_cache_in_production')) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Cache driver is array in production.',
                suggestion: 'Use a persistent cache driver such as database, redis, memcached, or dynamodb.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Cache driver is production-safe.',
        );
    }
}
