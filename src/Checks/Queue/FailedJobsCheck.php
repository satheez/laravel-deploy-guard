<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Queue;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;
use Throwable;

final class FailedJobsCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'queue.failed_jobs';
    }

    public function category(): string
    {
        return 'queue';
    }

    public function description(): string
    {
        return 'Failed jobs storage is queryable';
    }

    public function run(): CheckResult
    {
        $driver = (string) config('queue.failed.driver', 'database-uuids');

        if (! in_array($driver, ['database', 'database-uuids'], true)) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Failed jobs storage is not database-backed.',
                suggestion: 'Verify failed job storage manually before deployment if workers are used.',
                details: ['driver' => $driver],
            );
        }

        $table = (string) config('queue.failed.table', 'failed_jobs');
        $database = config('queue.failed.database');

        try {
            if (! Schema::connection($database)->hasTable($table)) {
                return CheckResult::skipped(
                    checkKey: $this->key(),
                    category: $this->category(),
                    title: $this->description(),
                    message: 'Failed jobs table was not found.',
                    suggestion: 'Create the failed jobs table if this application stores failed jobs in the database.',
                );
            }

            $count = DB::connection($database)->table($table)->count();
        } catch (Throwable) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Failed jobs storage could not be queried safely.',
                suggestion: 'Verify failed job storage manually before deployment if workers are used.',
            );
        }

        $threshold = (int) config('deploy-guard.queue.failed_jobs_warning_threshold', 1);

        if ($count >= $threshold) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: 'Failed jobs exist',
                message: 'Existing failed jobs were detected.',
                suggestion: 'Review failed jobs before deployment and retry or clear them if appropriate.',
                details: ['failed_jobs_count' => $count],
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'No failed jobs were detected.',
            details: ['failed_jobs_count' => $count],
        );
    }
}
