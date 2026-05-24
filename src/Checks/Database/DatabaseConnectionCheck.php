<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Database;

use Illuminate\Support\Facades\DB;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;
use Throwable;

final class DatabaseConnectionCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'database.connection';
    }

    public function category(): string
    {
        return 'database';
    }

    public function description(): string
    {
        return 'Database connection works';
    }

    public function run(): CheckResult
    {
        if (blank(config('database.default'))) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Database connection cannot be checked because no default connection is configured.',
                suggestion: 'Set DB_CONNECTION and configure the matching database connection.',
            );
        }

        try {
            DB::connection()->getPdo();
        } catch (Throwable) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Database connection failed.',
                suggestion: 'Verify database credentials, host, port, network access, and DB_CONNECTION settings.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Database connection works.',
        );
    }
}
