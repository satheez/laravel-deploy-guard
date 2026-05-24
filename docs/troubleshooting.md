# Troubleshooting

## The command returns a failure in CI

Run the command locally with the same environment values:

```bash
php artisan deploy:guard --env=production
```

Review failures first. Warnings only fail when strict warning mode is enabled.

## JSON output is hard to read in logs

Run without `--json` for human-readable output:

```bash
php artisan deploy:guard --ci
```

Use `--json` when another tool needs to parse the report.

## Pending migrations cannot be checked

The package uses Laravel migration APIs. If the migration repository cannot be queried safely, the check returns a warning instead of crashing.

## Failed jobs are skipped

The failed jobs check skips when storage is unavailable, the configured table does not exist, or `queue.failed.driver` is not `database` or `database-uuids`. Create the failed jobs table if this application stores failed jobs in the database.

## Scheduler validation is a warning

The package does not inspect server crontabs. Verify scheduler execution through your deployment platform, container scheduler, process manager, or server configuration.

## A custom check is not registered

Confirm that the class is listed in `custom_checks` and implements `Satheez\DeployGuard\Contracts\DeploymentCheck`.
