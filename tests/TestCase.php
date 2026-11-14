<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
        // @note 利用側の config/core.php を模す(core は、既定値を持たない)
        $app['config']->set('core', [
            'cache_enable' => true,
            'cache_default_ttl_sec' => 600,
            'account_mail_domain' => 'account.invalid',
        ]);
    }
}
