<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Storage;

use Illuminate\Filesystem\Filesystem;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final readonly class BootstrapCacheWritableCheck implements DeploymentCheck
{
    public function __construct(
        private Filesystem $files,
    ) {}

    public function key(): string
    {
        return 'storage.bootstrap_cache_writable';
    }

    public function category(): string
    {
        return 'storage';
    }

    public function description(): string
    {
        return 'Bootstrap cache directory is writable';
    }

    public function run(): CheckResult
    {
        $path = base_path('bootstrap/cache');

        if (! $this->files->isDirectory($path) || ! $this->files->isWritable($path)) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'The bootstrap/cache directory is not writable.',
                suggestion: 'Update permissions so Laravel can write optimized bootstrap files during deployment.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'The bootstrap/cache directory is writable.',
        );
    }
}
