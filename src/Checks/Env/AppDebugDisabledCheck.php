<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Env;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class AppDebugDisabledCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'env.app_debug';
    }

    public function category(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'APP_DEBUG is disabled in production';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_DEBUG production validation was skipped outside a production environment.',
            );
        }

        if ((bool) config('app.debug')) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: 'APP_DEBUG is enabled in production',
                message: 'APP_DEBUG is enabled in a production environment.',
                suggestion: 'Set APP_DEBUG=false before deploying to production.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'APP_DEBUG is disabled for production.',
        );
    }
}
