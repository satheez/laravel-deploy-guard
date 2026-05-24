<?php

declare(strict_types=1);

namespace Satheez\DeployGuard;

use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;
use Satheez\DeployGuard\Checks\Cache\CacheDriverCheck;
use Satheez\DeployGuard\Checks\Cache\ConfigCachedCheck;
use Satheez\DeployGuard\Checks\Cache\RoutesCachedCheck;
use Satheez\DeployGuard\Checks\Cache\ViewsCachedCheck;
use Satheez\DeployGuard\Checks\Database\DatabaseConnectionCheck;
use Satheez\DeployGuard\Checks\Database\DefaultConnectionConfiguredCheck;
use Satheez\DeployGuard\Checks\Env\AppDebugDisabledCheck;
use Satheez\DeployGuard\Checks\Env\AppEnvSetCheck;
use Satheez\DeployGuard\Checks\Env\AppKeySetCheck;
use Satheez\DeployGuard\Checks\Env\AppUrlSetCheck;
use Satheez\DeployGuard\Checks\Filesystem\CloudDiskConfiguredCheck;
use Satheez\DeployGuard\Checks\Filesystem\DefaultDiskConfiguredCheck;
use Satheez\DeployGuard\Checks\Filesystem\DefaultDiskExistsCheck;
use Satheez\DeployGuard\Checks\Mail\MailerConfiguredCheck;
use Satheez\DeployGuard\Checks\Mail\ProductionMailerCheck;
use Satheez\DeployGuard\Checks\Migrations\PendingMigrationsCheck;
use Satheez\DeployGuard\Checks\Queue\FailedJobsCheck;
use Satheez\DeployGuard\Checks\Queue\QueueConnectionCheck;
use Satheez\DeployGuard\Checks\Scheduler\SchedulerValidationCheck;
use Satheez\DeployGuard\Checks\Storage\BootstrapCacheWritableCheck;
use Satheez\DeployGuard\Checks\Storage\PublicStorageLinkCheck;
use Satheez\DeployGuard\Checks\Storage\StorageDirectoryWritableCheck;
use Satheez\DeployGuard\Console\DeployGuardCommand;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Support\CheckRegistry;
use Satheez\DeployGuard\Support\CheckRunner;
use Satheez\DeployGuard\Support\JsonFormatter;
use Satheez\DeployGuard\Support\ReportFormatter;

final class DeployGuardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/deploy-guard.php', 'deploy-guard');

        $this->app->singleton(CheckRegistry::class, function (): CheckRegistry {
            $registry = new CheckRegistry;

            foreach ($this->builtInChecks() as $checkClass) {
                $registry->register($this->app->make($checkClass));
            }

            foreach (config('deploy-guard.custom_checks', []) as $checkClass) {
                $check = $this->app->make($checkClass);

                if (! $check instanceof DeploymentCheck) {
                    throw new InvalidArgumentException(sprintf('Deploy Guard custom check [%s] must implement ', $checkClass).DeploymentCheck::class.'.');
                }

                $registry->register($check);
            }

            return $registry;
        });

        $this->app->singleton(CheckRunner::class);
        $this->app->singleton(ReportFormatter::class);
        $this->app->singleton(JsonFormatter::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/deploy-guard.php' => config_path('deploy-guard.php'),
        ], 'deploy-guard-config');

        if ($this->app->runningInConsole()) {
            $this->commands([
                DeployGuardCommand::class,
            ]);
        }
    }

    /**
     * @return array<int, class-string<DeploymentCheck>>
     */
    private function builtInChecks(): array
    {
        return [
            AppEnvSetCheck::class,
            AppKeySetCheck::class,
            AppDebugDisabledCheck::class,
            AppUrlSetCheck::class,
            DefaultConnectionConfiguredCheck::class,
            DatabaseConnectionCheck::class,
            PendingMigrationsCheck::class,
            QueueConnectionCheck::class,
            FailedJobsCheck::class,
            CacheDriverCheck::class,
            ConfigCachedCheck::class,
            RoutesCachedCheck::class,
            ViewsCachedCheck::class,
            StorageDirectoryWritableCheck::class,
            BootstrapCacheWritableCheck::class,
            PublicStorageLinkCheck::class,
            MailerConfiguredCheck::class,
            ProductionMailerCheck::class,
            DefaultDiskExistsCheck::class,
            DefaultDiskConfiguredCheck::class,
            CloudDiskConfiguredCheck::class,
            SchedulerValidationCheck::class,
        ];
    }
}
