<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;
use Throwable;

final class CheckRunner
{
    /**
     * @param  array<int, DeploymentCheck>  $checks
     */
    public function run(array $checks): CheckReport
    {
        $results = [];

        foreach ($checks as $check) {
            try {
                $results[] = $check->run();
            } catch (Throwable $exception) {
                $results[] = CheckResult::fail(
                    checkKey: $check->key(),
                    category: $check->category(),
                    title: $check->description(),
                    message: 'The deployment check could not be completed.',
                    suggestion: 'Review the application configuration required by this check.',
                    details: ['exception' => $exception::class],
                );
            }
        }

        return new CheckReport($results);
    }
}
