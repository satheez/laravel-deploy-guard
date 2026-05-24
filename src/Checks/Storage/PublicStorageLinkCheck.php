<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Storage;

use Illuminate\Filesystem\Filesystem;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final readonly class PublicStorageLinkCheck implements DeploymentCheck
{
    public function __construct(
        private Filesystem $files,
    ) {}

    public function key(): string
    {
        return 'storage.public_link';
    }

    public function category(): string
    {
        return 'storage';
    }

    public function description(): string
    {
        return 'Public storage link exists';
    }

    public function run(): CheckResult
    {
        if (! (bool) config('deploy-guard.storage.check_public_link', true)) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Public storage link validation is disabled.',
            );
        }

        $path = public_path('storage');

        if (! $this->files->exists($path)) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Public storage link is missing.',
                suggestion: 'Run php artisan storage:link if the application serves public files from storage.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Public storage link exists.',
        );
    }
}
