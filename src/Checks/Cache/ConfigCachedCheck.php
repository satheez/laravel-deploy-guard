<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Cache;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class ConfigCachedCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'cache.config';
    }

    public function category(): string
    {
        return 'cache';
    }

    public function description(): string
    {
        return 'Configuration is cached in production';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Configuration cache validation was skipped outside a production environment.',
            );
        }

        if (! app()->configurationIsCached()) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Configuration is not cached.',
                suggestion: 'Run php artisan config:cache during deployment.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Configuration is cached.',
        );
    }
}
