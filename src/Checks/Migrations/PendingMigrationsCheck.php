<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Migrations;

use Illuminate\Database\Migrations\Migrator;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;
use Throwable;

final class PendingMigrationsCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'migrations.pending';
    }

    public function category(): string
    {
        return 'migrations';
    }

    public function description(): string
    {
        return 'Pending migrations are detected';
    }

    public function run(): CheckResult
    {
        try {
            /** @var Migrator $migrator */
            $migrator = app('migrator');

            if (! $migrator->repositoryExists()) {
                return CheckResult::warning(
                    checkKey: $this->key(),
                    category: $this->category(),
                    title: $this->description(),
                    message: 'The migration repository is not available.',
                    suggestion: 'Run migrations during deployment and verify the migrations table exists.',
                );
            }

            $pending = $this->pendingMigrations($migrator);
        } catch (Throwable) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Pending migrations could not be checked safely.',
                suggestion: 'Run php artisan migrate:status before deployment if migration state is uncertain.',
            );
        }

        if ($pending === []) {
            return CheckResult::pass(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'No pending migrations were detected.',
            );
        }

        $pendingStatus = config('deploy-guard.migrations.pending_status', 'warning') === 'fail'
            ? 'fail'
            : 'warning';

        return CheckResult::{$pendingStatus}(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Pending migrations were detected.',
            suggestion: 'Run php artisan migrate --force during deployment or review pending migrations before release.',
            details: ['pending_count' => count($pending)],
        );
    }

    /**
     * @return array<int, string>
     */
    private function migrationPaths(Migrator $migrator): array
    {
        return array_values(array_unique(array_merge(
            [database_path('migrations')],
            $migrator->paths(),
        )));
    }

    /**
     * @return array<int, string>
     */
    private function pendingMigrations(Migrator $migrator): array
    {
        $files = $migrator->getMigrationFiles($this->migrationPaths($migrator));
        $ran = array_flip($migrator->getRepository()->getRan());

        return array_values(array_diff(array_keys($files), array_keys($ran)));
    }
}
