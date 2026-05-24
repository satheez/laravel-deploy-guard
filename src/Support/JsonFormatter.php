<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

use JsonException;
use Satheez\DeployGuard\Results\CheckResult;

final class JsonFormatter
{
    /**
     * @throws JsonException
     */
    public function format(CheckReport $report, string $environment, bool $failOnWarning = false): string
    {
        return json_encode([
            'environment' => $environment,
            'status' => $report->status($failOnWarning),
            'summary' => $report->summary(),
            'results' => array_map(
                static fn (CheckResult $result): array => $result->toArray(),
                $report->results,
            ),
        ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
    }
}
