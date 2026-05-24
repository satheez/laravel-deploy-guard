<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Env;

use Illuminate\Encryption\Encrypter;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class AppKeySetCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'env.app_key';
    }

    public function category(): string
    {
        return 'env';
    }

    public function description(): string
    {
        return 'APP_KEY is configured';
    }

    public function run(): CheckResult
    {
        if (blank(config('app.key'))) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_KEY is missing.',
                suggestion: 'Generate an application key before deployment using php artisan key:generate.',
            );
        }

        if (! $this->isSupportedKey((string) config('app.key'), (string) config('app.cipher', 'AES-256-CBC'))) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'APP_KEY is invalid.',
                suggestion: 'Generate a valid application key for the configured cipher using php artisan key:generate.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'APP_KEY is configured.',
        );
    }

    private function isSupportedKey(string $key, string $cipher): bool
    {
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(mb_substr($key, 7), true);

            if ($decoded === false) {
                return false;
            }

            $key = $decoded;
        }

        return Encrypter::supported($key, $cipher);
    }
}
