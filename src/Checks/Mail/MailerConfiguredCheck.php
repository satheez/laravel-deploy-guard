<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Mail;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class MailerConfiguredCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'mail.mailer';
    }

    public function category(): string
    {
        return 'mail';
    }

    public function description(): string
    {
        return 'Mailer is configured';
    }

    public function run(): CheckResult
    {
        $mailer = (string) config('mail.default');

        if ($mailer === '' || config('mail.mailers.'.$mailer) === null) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Default mailer is missing or invalid.',
                suggestion: 'Set MAIL_MAILER and configure the matching mailer.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Mailer is configured.',
        );
    }
}
