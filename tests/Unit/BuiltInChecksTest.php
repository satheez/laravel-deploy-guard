<?php

declare(strict_types=1);

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

it('checks required environment values', function (): void {
    config()->set('deploy-guard.runtime.environment', '');
    expect((new AppEnvSetCheck)->run()->status->value)->toBe('fail');

    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('app.key');

    expect((new AppKeySetCheck)->run()->status->value)->toBe('fail');

    config()->set('app.key', 'base64:present');
    expect((new AppKeySetCheck)->run()->status->value)->toBe('pass');
});

it('checks debug mode only for production environments', function (): void {
    config()->set('deploy-guard.runtime.environment', 'local');
    config()->set('app.debug', true);

    expect((new AppDebugDisabledCheck)->run()->status->value)->toBe('skipped');

    config()->set('deploy-guard.runtime.environment', 'production');
    expect((new AppDebugDisabledCheck)->run()->status->value)->toBe('fail');

    config()->set('app.debug', false);
    expect((new AppDebugDisabledCheck)->run()->status->value)->toBe('pass');
});

it('checks application url production risks', function (): void {
    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('app.url', '');

    expect((new AppUrlSetCheck)->run()->status->value)->toBe('warning');

    config()->set('app.url', 'http://localhost');
    expect((new AppUrlSetCheck)->run()->status->value)->toBe('warning');

    config()->set('app.url', 'https://example.com');
    expect((new AppUrlSetCheck)->run()->status->value)->toBe('pass');
});

it('checks database configuration and connectivity', function (): void {
    expect((new DefaultConnectionConfiguredCheck)->run()->status->value)->toBe('pass')
        ->and((new DatabaseConnectionCheck)->run()->status->value)->toBe('pass');

    config()->set('database.default', 'missing');
    expect((new DefaultConnectionConfiguredCheck)->run()->status->value)->toBe('fail')
        ->and((new DatabaseConnectionCheck)->run()->status->value)->toBe('fail');
});

it('warns when migration state cannot be checked safely', function (): void {
    expect((new PendingMigrationsCheck)->run()->status->value)->toBe('warning');
});

it('checks production queue connection allowances', function (): void {
    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('queue.default', 'sync');

    expect((new QueueConnectionCheck)->run()->status->value)->toBe('warning');

    config()->set('deploy-guard.allow.sync_queue_in_production', true);
    expect((new QueueConnectionCheck)->run()->status->value)->toBe('pass');

    config()->set('queue.default', '');
    expect((new QueueConnectionCheck)->run()->status->value)->toBe('fail');
});

it('checks failed jobs storage', function (): void {
    expect((new FailedJobsCheck)->run()->status->value)->toBe('skipped');

    Schema::create('failed_jobs', function ($table): void {
        $table->id();
        $table->string('uuid')->unique();
        $table->text('connection');
        $table->text('queue');
        $table->longText('payload');
        $table->longText('exception');
        $table->timestamp('failed_at')->useCurrent();
    });

    expect((new FailedJobsCheck)->run()->status->value)->toBe('pass');

    DB::table('failed_jobs')->insert([
        'uuid' => 'failed-job-1',
        'connection' => 'database',
        'queue' => 'default',
        'payload' => '{}',
        'exception' => 'RuntimeException',
    ]);

    expect((new FailedJobsCheck)->run()->status->value)->toBe('warning');
});

it('checks cache readiness', function (): void {
    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('cache.default', 'array');

    expect((new CacheDriverCheck)->run()->status->value)->toBe('warning');

    config()->set('deploy-guard.allow.array_cache_in_production', true);
    expect((new CacheDriverCheck)->run()->status->value)->toBe('pass');

    expect((new ConfigCachedCheck)->run()->status->value)->toBe('warning')
        ->and((new RoutesCachedCheck)->run()->status->value)->toBe('warning');
});

it('checks compiled views', function (): void {
    $files = app(Filesystem::class);
    $compiledPath = storage_path('framework/views');

    $files->ensureDirectoryExists($compiledPath);
    $files->cleanDirectory($compiledPath);

    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('view.compiled', $compiledPath);

    expect((new ViewsCachedCheck($files))->run()->status->value)->toBe('warning');

    $files->put($compiledPath.'/compiled.php', '<?php echo "compiled";');

    expect((new ViewsCachedCheck($files))->run()->status->value)->toBe('pass');
});

it('checks storage paths and public link configuration', function (): void {
    $files = app(Filesystem::class);

    expect((new StorageDirectoryWritableCheck($files))->run()->status->value)->toBe('pass');

    config()->set('deploy-guard.storage.check_public_link', false);
    expect((new PublicStorageLinkCheck($files))->run()->status->value)->toBe('skipped');

    expect((new BootstrapCacheWritableCheck($files))->run()->status->value)->toBeIn(['pass', 'fail']);
});

it('checks mailer configuration and production mailers', function (): void {
    config()->set('mail.default', 'missing');
    expect((new MailerConfiguredCheck)->run()->status->value)->toBe('fail');

    config()->set('deploy-guard.runtime.environment', 'production');
    config()->set('mail.default', 'log');
    config()->set('mail.mailers.log', ['transport' => 'log']);

    expect((new ProductionMailerCheck)->run()->status->value)->toBe('warning');

    config()->set('deploy-guard.allow.log_mailer_in_production', true);
    expect((new ProductionMailerCheck)->run()->status->value)->toBe('pass');

    config()->set('mail.default', 'array');
    config()->set('mail.mailers.array', ['transport' => 'array']);
    config()->set('deploy-guard.allow.array_mailer_in_production', false);

    expect((new ProductionMailerCheck)->run()->status->value)->toBe('warning');
});

it('checks filesystem disk configuration', function (): void {
    expect((new DefaultDiskExistsCheck)->run()->status->value)->toBe('pass')
        ->and((new DefaultDiskConfiguredCheck)->run()->status->value)->toBe('pass')
        ->and((new CloudDiskConfiguredCheck)->run()->status->value)->toBe('skipped');

    config()->set('filesystems.default', 'missing');
    expect((new DefaultDiskExistsCheck)->run()->status->value)->toBe('fail');

    config()->set('filesystems.default', 'broken-s3');
    config()->set('filesystems.disks.broken-s3', ['driver' => 's3', 'key' => 'set']);

    expect((new DefaultDiskConfiguredCheck)->run()->status->value)->toBe('fail');

    config()->set('deploy-guard.filesystem.require_cloud_disk', true);
    config()->set('filesystems.cloud', 'missing-cloud');

    expect((new CloudDiskConfiguredCheck)->run()->status->value)->toBe('warning');
});

it('checks scheduler validation setting', function (): void {
    expect((new SchedulerValidationCheck)->run()->status->value)->toBe('warning');

    config()->set('deploy-guard.scheduler.validate', false);
    expect((new SchedulerValidationCheck)->run()->status->value)->toBe('skipped');
});
