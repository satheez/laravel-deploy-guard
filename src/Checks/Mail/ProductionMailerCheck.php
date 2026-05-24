<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Mail;

use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class ProductionMailerCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function key(): string
    {
        return 'mail.production_mailer';
    }

    public function category(): string
    {
        return 'mail';
    }

    public function description(): string
    {
        return 'Production mailer can deliver mail';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Mailer production validation was skipped outside a production environment.',
            );
        }

        $mailer = (string) config('mail.default');

        if ($mailer === 'log' && ! $this->isAllowed('log_mailer_in_production')) {
            return $this->unsafeMailerResult($mailer);
        }

        if ($mailer === 'array' && ! $this->isAllowed('array_mailer_in_production')) {
            return $this->unsafeMailerResult($mailer);
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Production mailer is configured for delivery.',
        );
    }

    private function unsafeMailerResult(string $mailer): CheckResult
    {
        return CheckResult::warning(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: sprintf('Mailer [%s] may not deliver mail in production.', $mailer),
            suggestion: 'Use smtp, ses, mailgun, postmark, resend, or another production mailer.',
        );
    }
}
