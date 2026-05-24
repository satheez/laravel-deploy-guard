# JSON Output

Use JSON output for release gates and report artifacts:

```bash
php artisan deploy:guard --ci --json
```

## Schema

```json
{
  "environment": "production",
  "status": "fail",
  "summary": {
    "total": 22,
    "passed": 17,
    "warnings": 3,
    "failed": 2,
    "skipped": 0
  },
  "results": [
    {
      "status": "fail",
      "check_key": "env.app_debug",
      "category": "env",
      "title": "APP_DEBUG is enabled in production",
      "message": "APP_DEBUG is enabled in a production environment.",
      "details": [],
      "suggestion": "Set APP_DEBUG=false before deploying to production."
    }
  ]
}
```

## Status Values

| Status | Meaning |
|---|---|
| `pass` | No failures or warnings |
| `warning` | Warnings exist and warning failure mode is disabled |
| `fail` | Failures exist, or warnings exist while warning failure mode is enabled |

## Secret Safety

JSON output must not contain secret values. It reports whether values are present, missing, unsafe, or queryable.
