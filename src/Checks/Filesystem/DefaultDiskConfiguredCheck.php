<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Filesystem;

use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final class DefaultDiskConfiguredCheck implements DeploymentCheck
{
    public function key(): string
    {
        return 'filesystem.default_disk_config';
    }

    public function category(): string
    {
        return 'filesystem';
    }

    public function description(): string
    {
        return 'Default filesystem disk has required config';
    }

    public function run(): CheckResult
    {
        $disk = (string) config('filesystems.default');
        $config = config('filesystems.disks.'.$disk);

        if (! is_array($config)) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Default filesystem disk configuration is missing.',
                suggestion: 'Review the default filesystem disk configuration.',
            );
        }

        $missing = $this->missingRequiredKeys($config);

        if ($missing !== []) {
            return CheckResult::fail(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Default filesystem disk is missing required configuration values.',
                suggestion: 'Review filesystems.php for the default disk.',
                details: ['missing' => $missing],
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Default filesystem disk has required configuration values.',
        );
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, string>
     */
    private function missingRequiredKeys(array $config): array
    {
        $driver = (string) ($config['driver'] ?? '');
        $required = match ($driver) {
            'local' => ['root'],
            's3' => ['key', 'secret', 'region', 'bucket'],
            'ftp' => ['host', 'username', 'password'],
            'sftp' => ['host', 'username'],
            default => ['driver'],
        };

        return array_values(array_filter(
            $required,
            static fn (string $key): bool => blank($config[$key] ?? null),
        ));
    }
}
