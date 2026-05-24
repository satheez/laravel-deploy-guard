<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Env;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class AppKeySetCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'env.app_key';
    }

    public function category(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'APP_KEY is configured';
    }

    public function run(): CheckResult
    {
        if (blank(config('app.key'))) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_KEY is missing.',
                suggestion: 'Generate an application key before deployment using php artisan key:generate.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'APP_KEY is configured.',
        );
    }
}
