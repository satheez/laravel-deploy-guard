<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Storage;

use Illuminate\Filesystem\Filesystem;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final readonly class StorageDirectoryWritableCheck implements DeploymentCheck
{
    public function __construct(
        private Filesystem $files,
    ) {}

    public function key(): string
    {
        return 'storage.directory_writable';
    }

    public function category(): string
    {
        return 'storage';
    }

    public function description(): string
    {
        return 'Storage directory is writable';
    }

    public function run(): CheckResult
    {
        $path = storage_path();

        if (! $this->files->isDirectory($path) || ! $this->files->isWritable($path)) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'The storage directory is not writable.',
                suggestion: 'Update permissions so the web server and queue worker can write to the storage directory.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'The storage directory is writable.',
        );
    }
}
