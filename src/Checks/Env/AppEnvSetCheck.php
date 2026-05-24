<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Env;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class AppEnvSetCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'env.app_env';
    }

    public function category(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'APP_ENV is set';
    }

    public function run(): CheckResult
    {
        if (trim($this->targetEnvironment()) === '') {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_ENV is not set.',
                suggestion: 'Set APP_ENV to the deployment environment name.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'APP_ENV is set.',
        );
    }
}
