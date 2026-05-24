<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Support;

use Satheez\DeployGuard\Results\CheckResult;
use Satheez\DeployGuard\Results\CheckStatus;

final readonly class CheckReport
{
    /**
     * @param  array<int, CheckResult>  $results
     */
    public function __construct(
        public array $results,
    ) {}

    public function total(): int
    {
        return count($this->results);
    }

    public function passed(): int
    {
        return $this->count(CheckStatus::Pass);
    }

    public function warnings(): int
    {
        return $this->count(CheckStatus::Warning);
    }

    public function failed(): int
    {
        return $this->count(CheckStatus::Fail);
    }

    public function skipped(): int
    {
        return $this->count(CheckStatus::Skipped);
    }

    /**
     * @return array{total: int, passed: int, warnings: int, failed: int, skipped: int}
     */
    public function summary(): array
    {
        return [
            'total' => $this->total(),
            'passed' => $this->passed(),
            'warnings' => $this->warnings(),
            'failed' => $this->failed(),
            'skipped' => $this->skipped(),
        ];
    }

    public function status(bool $failOnWarning = false): string
    {
        if ($this->failed() > 0) {
            return 'fail';
        }

        if ($this->warnings() > 0) {
            return $failOnWarning ? 'fail' : 'warning';
        }

        return 'pass';
    }

    public function exitCode(bool $failOnWarning = false): int
    {
        if ($this->failed() > 0) {
            return 1;
        }

        if ($failOnWarning && $this->warnings() > 0) {
            return 2;
        }

        return 0;
    }

    private function count(CheckStatus $status): int
    {
        return count(array_filter(
            $this->results,
            static fn (CheckResult $result): bool => $result->status === $status,
        ));
    }
}
