# FAQ

## Does this package deploy my application?

No. It only inspects deployment readiness and reports risks.

## Does it run migrations?

No. It can detect pending migrations when Laravel migration state is queryable, but it does not run them.

## Does it change `.env` values?

No. All checks are read-only.

## Can warnings fail CI?

Yes:

```bash
php artisan deploy:guard --ci --fail-on=warning
```

You can also enable warning failure for CI in `config/deploy-guard.php`.

## Can I run only production-critical checks?

Yes:

```bash
php artisan deploy:guard --ci --only=env,database,migrations,queue
```

## Does it expose secrets?

No. Checks report whether sensitive settings are present or missing, not their values.

## Does it support custom checks?

Yes. Register classes that implement `DeploymentCheck` in `custom_checks`.

## Is scheduler validation complete?

No. Version 1 provides a lightweight advisory warning. Server-level scheduler verification remains outside the package.
