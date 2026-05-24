<?php

declare(strict_types=1);

namespace Satheez\DeployGuard\Checks\Cache;

use Illuminate\Filesystem\Filesystem;
use Satheez\DeployGuard\Checks\Concerns\ReadsDeployGuardConfig;
use Satheez\DeployGuard\Contracts\DeploymentCheck;
use Satheez\DeployGuard\Results\CheckResult;

final readonly class ViewsCachedCheck implements DeploymentCheck
{
    use ReadsDeployGuardConfig;

    public function __construct(
        private Filesystem $files,
    ) {}

    public function key(): string
    {
        return 'cache.views';
    }

    public function category(): string
    {
        return 'cache';
    }

    public function description(): string
    {
        return 'Compiled views are available in production';
    }

    public function run(): CheckResult
    {
        if (! $this->isProductionEnvironment()) {
            return CheckResult::skipped(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'View cache validation was skipped outside a production environment.',
            );
        }

        $compiledPath = (string) config('view.compiled', storage_path('framework/views'));

        if (! $this->files->isDirectory($compiledPath) || ! $this->hasCompiledViewFiles($compiledPath)) {
            return CheckResult::warning(
                checkKey: $this->key(),
                category: $this->category(),
                title: $this->description(),
                message: 'Compiled views were not detected.',
                suggestion: 'Run php artisan view:cache during deployment if your application uses Blade views.',
            );
        }

        return CheckResult::pass(
            checkKey: $this->key(),
            category: $this->category(),
            title: $this->description(),
            message: 'Compiled views were detected.',
        );
    }

    private function hasCompiledViewFiles(string $compiledPath): bool
    {
        foreach ($this->files->files($compiledPath) as $file) {
            if (! str_starts_with($file->getFilename(), '.')) {
                return true;
            }
        }

        return false;
    }
}
