# Usage

Laravel Deploy Guard is centered around one Artisan command:

```bash
php artisan deploy:guard
```

The command runs enabled deployment checks, prints a summary, and exits with a predictable code.

## Basic Command

```bash
php artisan deploy:guard
```

Use this locally before deployment or during staging validation.

## CI Mode

```bash
php artisan deploy:guard --ci
```

CI mode keeps output readable and returns non-zero exit codes when checks fail.

## JSON Output

```bash
php artisan deploy:guard --json
```

JSON output is intended for pipeline logs, release gates, and report artifacts.

## Select Checks

Run only categories:

```bash
php artisan deploy:guard --only=env,database,cache
```

Run one exact check:

```bash
php artisan deploy:guard --only=env.app_debug
```

Skip categories:

```bash
php artisan deploy:guard --except=mail,scheduler
```

## Strict Warnings

```bash
php artisan deploy:guard --fail-on=warning
```

This returns exit code `2` when warnings exist and no failures exist.

## Target Environment

```bash
php artisan deploy:guard --env=production
```

This is useful when the runtime environment does not match the deployment target.
