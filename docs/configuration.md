# Configuration

Publish the config file:

```bash
php artisan vendor:publish --tag=deploy-guard-config
```

The published file is `config/deploy-guard.php`.

## Enable or Disable the Package

```php
'enabled' => env('DEPLOY_GUARD_ENABLED', true),
```

When disabled, no checks are run and the report summary is empty.

## Production Environments

```php
'production_environments' => [
    'production',
    'prod',
],
```

Production-only checks use this list.

## Checks

Enable or disable categories:

```php
'checks' => [
    'env' => true,
    'database' => true,
    'queue' => true,
],
```

Enable or disable exact checks:

```php
'checks' => [
    'env.app_debug' => true,
    'scheduler.validation' => false,
],
```

## Allowances

```php
'allow' => [
    'sync_queue_in_production' => false,
    'array_cache_in_production' => false,
    'log_mailer_in_production' => false,
    'array_mailer_in_production' => false,
],
```

Use allowances only when the risk is intentional for the target application.

## Storage

```php
'storage' => [
    'check_public_link' => true,
],
```

Disable this when the application does not serve public files from storage.

## Filesystem

```php
'filesystem' => [
    'require_cloud_disk' => false,
],
```

Enable this when the application expects a cloud disk in deployment environments.

## Migrations

```php
'migrations' => [
    'pending_status' => 'warning',
],
```

Set `pending_status` to `fail` to block deployment when pending migrations are detected.

## CI

```php
'ci' => [
    'fail_on_warning' => false,
],
```

This makes `--ci` return exit code `2` when warnings exist and failures do not.
