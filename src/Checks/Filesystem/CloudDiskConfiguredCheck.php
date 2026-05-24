<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Filesystem;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class CloudDiskConfiguredCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'filesystem.cloud_disk';
    }

    public function category(): string
    {
        return 'filesystem';
    }

    public function description(): string
    {
        return 'Cloud filesystem disk is configured when required';
    }

    public function run(): CheckResult
    {
        if (! (bool) config('deploy-guard.filesystem.require_cloud_disk', false)) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Cloud filesystem disk validation is disabled.',
            );
        }

        $cloudDisk = (string) config('filesystems.cloud');

        if ($cloudDisk === '' || config('filesystems.disks.'.$cloudDisk) === null) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Cloud filesystem disk is required but not configured.',
                suggestion: 'Set filesystems.cloud and define the matching cloud disk configuration.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Cloud filesystem disk is configured.',
        );
    }
}
