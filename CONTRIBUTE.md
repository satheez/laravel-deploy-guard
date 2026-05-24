# Contribute

Thanks for helping improve Laravel Deploy Guard.

## Development Setup

```bash
composer install
```

Run tests:

```bash
composer test
```

Run formatting checks:

```bash
vendor/bin/pint --test
```

Run Rector in dry-run mode:

```bash
composer rector:test
```

Run Larastan:

```bash
composer analyse
```

## Pull Request Checklist

- Add or update Pest tests for behavior changes.
- Keep checks read-only by default.
- Avoid printing secrets in output.
- Update documentation when command behavior, config, or check results change.
- Run Pest, Pint, Rector dry-run, and Larastan before opening a pull request.

## Coding Guidelines

- Keep each deployment check focused on one risk.
- Prefer Laravel APIs over shelling out.
- Return `CheckResult` objects with actionable messages.
- Use `warning` for advisory risks and `fail` for risks that should normally block deployment.
- Keep v1 features CLI-first and lightweight.

## Commit Messages

Use concise conventional commit style:

```text
feat(checks): add redis queue validation
fix(command): respect exact check filters
docs(readme): document json output
```
