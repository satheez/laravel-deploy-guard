# Custom Checks

Custom checks allow applications to add project-specific deployment readiness rules.

## Contract

```php
use Satheez\DeployGuard\Results\CheckResult;

interface DeploymentCheck
{
    public function key(): string;

    public function category(): string;

    public function description(): string;

    public function run(): CheckResult;
}
```

## Example

```php
<?php

namespace App\DeployGuard;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class SearchIndexReadyCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'search.index_ready';
    }

    public function category(): string
    {
        return 'search';
    }

    public function description(): string
    {
        return 'Search index is ready';
    }

    public function run(): CheckResult
    {
        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Search index readiness was confirmed.',
        );
    }
}
```

## Register the Check

```php
'custom_checks' => [
    App\DeployGuard\SearchIndexReadyCheck::class,
],
```

Custom checks are resolved through Laravel's container.

## Guidance

- Keep checks read-only.
- Avoid exposing secrets in messages or details.
- Use stable machine-readable keys.
- Return actionable suggestions for warnings and failures.
