<?php

declare(strict_types=1);

namespace Tests;

use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as Orchestra;
use Satheez\DeployGuard\DeployGuardServiceProvider;

abstract class TestCase extends Orchestra
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            DeployGuardServiceProvider::class,
        ];
    }

    /**
     * @param  Application  $app
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.debug', false);
        $app['config']->set('app.env', 'testing');
        $app['config']->set('app.key', 'base64:aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa=');
        $app['config']->set('app.url', 'https://example.test');

        $app['config']->set('cache.default', 'array');
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('filesystems.default', 'local');
        $app['config']->set('filesystems.cloud', null);
        $app['config']->set('filesystems.disks.local', [
            'driver' => 'local',
            'root' => storage_path('app'),
        ]);
        $app['config']->set('mail.default', 'smtp');
        $app['config']->set('mail.from.address', 'deploy@example.test');
        $app['config']->set('queue.default', 'database');
    }

    /**
     * @param  array<int, string>  $enabledCategories
     */
    protected function enableCategories(array $enabledCategories): void
    {
        $categories = [
            'env',
            'database',
            'migrations',
            'queue',
            'cache',
            'storage',
            'mail',
            'filesystem',
            'scheduler',
        ];

        $checks = array_fill_keys($categories, false);

        foreach ($enabledCategories as $category) {
            $checks[$category] = true;
        }

        config()->set('deploy-guard.checks', $checks);
    }
}
