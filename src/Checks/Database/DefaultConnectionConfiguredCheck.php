<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Database;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class DefaultConnectionConfiguredCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'database.default_connection';
    }

    public function category(): string
    {
        return 'database';
    }

    public function description(): string
    {
        return 'Default database connection is configured';
    }

    public function run(): CheckResult
    {
        $connection = config('database.default');

        if (blank($connection) || config('database.connections.'.$connection) === null) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Default database connection is missing or invalid.',
                suggestion: 'Review DB_CONNECTION and the matching database connection configuration.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Default database connection is configured.',
        );
    }
}
