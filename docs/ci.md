# CI/CD Usage

Use `deploy:guard` before release steps that publish code, restart workers, or switch traffic.

## GitHub Actions

```yaml
- name: Run Laravel Deploy Guard
  run: php artisan deploy:guard --ci
```

With JSON output:

```yaml
- name: Run Laravel Deploy Guard
  run: php artisan deploy:guard --ci --json
```

## GitLab CI

```yaml
deploy_guard:
  stage: test
  script:
    - composer install --no-interaction --prefer-dist
    - php artisan deploy:guard --ci
```

## Bitbucket Pipelines

```yaml
pipelines:
  default:
    - step:
        name: Deploy Guard
        script:
          - composer install --no-interaction --prefer-dist
          - php artisan deploy:guard --ci
```

## Strict Warning Mode

```bash
php artisan deploy:guard --ci --fail-on=warning
```

Strict warning mode is useful for production release gates. It may be too strict for local development or early staging environments.

## Exit Codes

| Exit Code | Meaning |
|---:|---|
| `0` | No failed checks, or warnings only when warnings are allowed |
| `1` | One or more checks failed |
| `2` | Warnings exist and strict warning mode is enabled |
