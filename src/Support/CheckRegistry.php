<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

use InvalidArgumentException;
use Satheez\DeployGuard\Contracts\DeploymentCheck;

final class CheckRegistry
{
    /**
     * @var array<string, DeploymentCheck>
     */
    private array $checks = [];

    public function register(DeploymentCheck $check): void
    {
        if (array_key_exists($check->key(), $this->checks)) {
            throw new InvalidArgumentException(sprintf('A deployment check with key [%s] is already registered.', $check->key()));
        }

        $this->checks[$check->key()] = $check;
    }

    /**
     * @return array<int, DeploymentCheck>
     */
    public function all(): array
    {
        return array_values($this->checks);
    }

    /**
     * @param  array<int, string>  $only
     * @param  array<int, string>  $except
     * @return array<int, DeploymentCheck>
     */
    public function enabled(array $only = [], array $except = []): array
    {
        if (! config('deploy-guard.enabled', true)) {
            return [];
        }

        $checks = array_filter(
            $this->all(),
            $this->isEnabledByConfig(...),
        );

        if ($only !== []) {
            $checks = array_filter(
                $checks,
                fn (DeploymentCheck $check): bool => $this->matches($check, $only),
            );
        }

        if ($except !== []) {
            $checks = array_filter(
                $checks,
                fn (DeploymentCheck $check): bool => ! $this->matches($check, $except),
            );
        }

        return array_values($checks);
    }

    /**
     * @param  array<int, string>  $selectors
     * @return array<int, string>
     */
    public function invalidSelectors(array $selectors): array
    {
        $valid = array_unique(array_merge(
            array_map(static fn (DeploymentCheck $check): string => $check->key(), $this->all()),
            array_map(static fn (DeploymentCheck $check): string => $check->category(), $this->all()),
        ));

        return array_values(array_diff($selectors, $valid));
    }

    private function isEnabledByConfig(DeploymentCheck $check): bool
    {
        $configuredChecks = config('deploy-guard.checks', []);

        if (array_key_exists($check->key(), $configuredChecks)) {
            return (bool) $configuredChecks[$check->key()];
        }

        if (array_key_exists($check->category(), $configuredChecks)) {
            return (bool) $configuredChecks[$check->category()];
        }

        return true;
    }

    /**
     * @param  array<int, string>  $selectors
     */
    private function matches(DeploymentCheck $check, array $selectors): bool
    {
        return in_array($check->key(), $selectors, true)
            || in_array($check->category(), $selectors, true);
    }
}
