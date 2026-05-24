<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

use Satheez\DeployGuard\Results\CheckResult;
use Satheez\DeployGuard\Results\CheckStatus;

final class ReportFormatter
{
    public function format(CheckReport $report, string $environment): string
    {
        $lines = [
            'Laravel Deploy Guard',
            '',
            'Environment: '.$environment,
            'Checks run: '.$report->total(),
            'Passed: '.$report->passed(),
            'Warnings: '.$report->warnings(),
            'Failed: '.$report->failed(),
            'Skipped: '.$report->skipped(),
        ];

        $this->appendSection($lines, 'FAILURES', $report, CheckStatus::Fail);
        $this->appendSection($lines, 'WARNINGS', $report, CheckStatus::Warning);
        $this->appendSection($lines, 'SKIPPED', $report, CheckStatus::Skipped);

        return implode(PHP_EOL, $lines);
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function appendSection(array &$lines, string $title, CheckReport $report, CheckStatus $status): void
    {
        $results = array_values(array_filter(
            $report->results,
            static fn (CheckResult $result): bool => $result->status === $status,
        ));

        if ($results === []) {
            return;
        }

        $lines[] = '';
        $lines[] = $title;

        foreach ($results as $result) {
            $lines[] = '['.mb_strtoupper($result->status->value).'] '.$result->checkKey;
            $lines[] = $result->message;

            if ($result->suggestion !== null) {
                $lines[] = 'Suggestion: '.$result->suggestion;
            }

            if ($result->details !== []) {
                $lines[] = 'Details: '.$this->formatDetails($result->details);
            }

            $lines[] = '';
        }

        array_pop($lines);
    }

    /**
     * @param  array<string, mixed>  $details
     */
    private function formatDetails(array $details): string
    {
        $formatted = [];

        foreach ($details as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', array_map(static fn (mixed $item): string => (string) $item, $value));
            }

            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }

            $formatted[] = $key.'='.$value;
        }

        return implode('; ', $formatted);
    }
}
