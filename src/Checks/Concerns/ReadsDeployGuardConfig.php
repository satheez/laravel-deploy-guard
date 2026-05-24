<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Concerns;

trait ReadsDeployGuardConfig
{
    private function targetEnvironment(): string
    {
        return (string) config('deploy-guard.runtime.environment', app()->environment());
    }

    private function isProductionEnvironment(): bool
    {
        return in_array(
            mb_strtolower($this->targetEnvironment()),
            array_map(
                mb_strtolower(...),
                config('deploy-guard.production_environments', ['production', 'prod']),
            ),
            true,
        );
    }

    private function isAllowed(string $key): bool
    {
        return (bool) config('deploy-guard.allow.'.$key, false);
    }
}
