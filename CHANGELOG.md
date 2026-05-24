# Change Log

All notable changes to this package are documented here.

## Unreleased

### Added

- Laravel package scaffold with Composer auto-discovery.
- `deploy:guard` Artisan command.
- Human-readable and JSON reports.
- CI-oriented exit codes.
- `--only`, `--except`, `--json`, `--ci`, `--env`, and `--fail-on=warning` options.
- Publishable `deploy-guard.php` configuration.
- Check contract, result objects, registry, runner, and formatters.
- Built-in checks for environment, database, migrations, queue, cache, storage, mail, filesystem, and scheduler readiness.
- Custom check registration through configuration.
- Pest test suite covering command behavior, formatters, registry, runner, results, and built-in checks.
- Pint, Rector, Larastan, and GitHub Actions workflow.
- User documentation under `docs/`.

### Security

- Secret-safe output policy documented and covered by tests.
