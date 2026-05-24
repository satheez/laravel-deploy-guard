<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Console;

use Illuminate\Console\Command;
use Satheez\DeployGuard\Support\CheckRegistry;
use Satheez\DeployGuard\Support\CheckRunner;
use Satheez\DeployGuard\Support\JsonFormatter;
use Satheez\DeployGuard\Support\ReportFormatter;

final class DeployGuardCommand extends Command
{
    protected $signature = 'deploy:guard
        {--ci : Run with CI-oriented exit code behavior}
        {--json : Output a machine-readable JSON report}
        {--only= : Run only selected check categories or check keys}
        {--except= : Exclude selected check categories or check keys}
        {--fail-on= : Fail on the given status. Supported value: warning}
        {--env= : Evaluate checks against a target environment name}';

    protected $description = 'Check the application for deployment risks before release.';

    public function handle(
        CheckRegistry $registry,
        CheckRunner $runner,
        ReportFormatter $reportFormatter,
        JsonFormatter $jsonFormatter,
    ): int {
        $only = $this->parseList($this->option('only'));
        $except = $this->parseList($this->option('except'));
        $failOn = $this->option('fail-on');
        $json = (bool) $this->option('json');
        $ci = (bool) $this->option('ci');
        $environment = (string) ($this->option('env') ?: config('app.env', app()->environment()));

        config()->set('deploy-guard.runtime.environment', $environment);

        if (! in_array($failOn, [null, '', 'warning'], true)) {
            $this->renderError($json, 'Unsupported --fail-on value. Supported value: warning.');

            return self::FAILURE;
        }

        $invalidSelectors = array_merge(
            $registry->invalidSelectors($only),
            $registry->invalidSelectors($except),
        );

        if ($invalidSelectors !== []) {
            $this->renderError(
                $json,
                'Unknown deploy guard check filter: '.implode(', ', array_unique($invalidSelectors)).'.',
            );

            return self::FAILURE;
        }

        $failOnWarning = $failOn === 'warning'
            || ($ci && (bool) config('deploy-guard.ci.fail_on_warning', config('deploy-guard.ci.fail_on_warnings', false)));

        $report = $runner->run($registry->enabled($only, $except));

        if ($json) {
            $this->output->write($jsonFormatter->format($report, $environment, $failOnWarning));
        } else {
            $this->line($reportFormatter->format($report, $environment));
        }

        return $report->exitCode($failOnWarning);
    }

    /**
     * @return array<int, string>
     */
    private function parseList(mixed $value): array
    {
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_unique(array_filter(
            array_map(
                trim(...),
                explode(',', $value),
            ),
            static fn (string $item): bool => $item !== '',
        )));
    }

    private function renderError(bool $json, string $message): void
    {
        if ($json) {
            $this->output->write(json_encode([
                'status' => 'fail',
                'error' => $message,
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));

            return;
        }

        $this->error($message);
    }
}
