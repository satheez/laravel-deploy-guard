# Checks Reference

Each check returns `pass`, `warning`, `fail`, or `skipped`.

## Environment

| Check | Purpose |
|---|---|
| `env.app_env` | Confirms the target environment is set |
| `env.app_key` | Confirms `APP_KEY` is configured without printing it |
| `env.app_debug` | Fails when debug mode is enabled in production |
| `env.app_url` | Warns when `APP_URL` is missing or local in production |

## Database

| Check | Purpose |
|---|---|
| `database.default_connection` | Confirms the default connection exists in config |
| `database.connection` | Confirms the application can establish a database connection |

## Migrations

| Check | Purpose |
|---|---|
| `migrations.pending` | Detects pending migrations when Laravel migration state can be queried |

## Queue

| Check | Purpose |
|---|---|
| `queue.connection` | Warns when production uses `sync` unless allowed |
| `queue.failed_jobs` | Reports existing failed jobs when database-backed storage is queryable |

## Cache

| Check | Purpose |
|---|---|
| `cache.driver` | Warns when production uses the `array` cache store unless allowed |
| `cache.config` | Warns when production config is not cached |
| `cache.routes` | Warns when production routes are not cached |
| `cache.views` | Warns when compiled views are not detected |

## Storage

| Check | Purpose |
|---|---|
| `storage.directory_writable` | Confirms `storage` is writable |
| `storage.bootstrap_cache_writable` | Confirms `bootstrap/cache` is writable |
| `storage.public_link` | Warns when the public storage link is missing and enabled |

## Mail

| Check | Purpose |
|---|---|
| `mail.mailer` | Confirms the default mailer exists |
| `mail.production_mailer` | Warns when production uses `log` or `array` unless allowed |

## Filesystem

| Check | Purpose |
|---|---|
| `filesystem.default_disk` | Confirms the default disk exists |
| `filesystem.default_disk_config` | Confirms required default disk config values exist |
| `filesystem.cloud_disk` | Warns when a required cloud disk is missing |

## Scheduler

| Check | Purpose |
|---|---|
| `scheduler.validation` | Warns that scheduler execution must be confirmed outside the application |
