<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Scheduler;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class SchedulerValidationCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'scheduler.validation';
    }

    public function category(): string
    {
        return 'scheduler';
    }

    public function description(): string
    {
        return 'Scheduler execution is externally configured';
    }

    public function run(): CheckResult
    {
        if (! (bool) config('deploy-guard.scheduler.validate', true)) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Scheduler validation is disabled.',
            );
        }

        return CheckResult::warning(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Scheduler execution cannot be confirmed from inside the application.',
            suggestion: 'Verify that php artisan schedule:run is configured on the server, container, or scheduler service.',
        );
    }
}
