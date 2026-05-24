<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Queue;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class QueueConnectionCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'queue.connection';
    }

    public function category(): string
    {
        return 'queue';
    }

    public function description(): string
    {
        return 'Queue connection is production-safe';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Queue production validation was skipped outside a production environment.',
            );
        }

        $connection = (string) config('queue.default');

        if ($connection === '') {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Queue connection is not configured.',
                suggestion: 'Set QUEUE_CONNECTION to the queue driver used by this environment.',
            );
        }

        if ($connection === 'sync' && ! $this->isAllowed('sync_queue_in_production')) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Queue connection is sync in production.',
                suggestion: 'Use database, redis, sqs, or another async queue driver.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Queue connection is production-safe.',
        );
    }
}
