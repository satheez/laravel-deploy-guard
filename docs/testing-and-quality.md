# Testing and Quality Tools

The package uses Pest, Pint, Rector, and Larastan.

## Pest

```bash
composer test
```

The Pest suite covers:

- command behavior
- JSON output
- filters
- exit codes
- configuration behavior
- custom checks
- built-in checks
- result serialization
- registry and runner behavior

## Pint

```bash
vendor/bin/pint --test
```

Run `composer pint` to format code.

## Rector

```bash
composer rector:test
```

Run `composer rector` to apply safe refactors.

## Larastan

```bash
composer analyse
```

Larastan checks the package source, config, and tests.

## GitHub Actions

The workflow at `.github/workflows/tests.yaml` runs Composer validation, Pest, Pint, Rector dry-run, and Larastan across Laravel 12 (PHP 8.2, 8.3, and 8.4) and Laravel 13 (PHP 8.3 and 8.4).
