<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Filesystem;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class DefaultDiskExistsCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'filesystem.default_disk';
    }

    public function category(): string
    {
        return 'filesystem';
    }

    public function description(): string
    {
        return 'Default filesystem disk exists';
    }

    public function run(): CheckResult
    {
        $disk = (string) config('filesystems.default');

        if ($disk === '' || config('filesystems.disks.'.$disk) === null) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Default filesystem disk is missing or invalid.',
                suggestion: 'Set FILESYSTEM_DISK and define the matching disk in filesystems.php.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Default filesystem disk exists.',
        );
    }
}
