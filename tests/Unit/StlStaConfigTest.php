<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Unit;

use BitCraft\Core\Libraries\Stateless\Static\StlStaConfig;
use BitCraft\Core\Tests\TestCase;

final class StlStaConfigTest extends TestCase
{
    public function test_core_は_config_core_php_の値を返す(): void
    {
        config(['core' => ['cache_enable' => false, 'cache_default_ttl_sec' => 60]]);
        $core = StlStaConfig::core();
        $this->assertFalse($core->cache_enable);
        $this->assertSame(60, $core->cache_default_ttl_sec);
    }

    public function test_実行時の上書きが次の呼び出しに反映される(): void
    {
        config(['core.cache_default_ttl_sec' => 60]);
        $this->assertSame(60, StlStaConfig::core()->cache_default_ttl_sec);
        config(['core.cache_default_ttl_sec' => 30]);
        $this->assertSame(30, StlStaConfig::core()->cache_default_ttl_sec);
    }

    public function test_core_は_既定値を持たない_書かないキーは未定義(): void
    {
        config(['core' => ['cache_enable' => true]]);
        $core = StlStaConfig::core();
        $this->assertTrue($core->cache_enable);
        $this->assertFalse(isset($core->cache_default_ttl_sec));
        $this->assertFalse(isset($core->account_mail_domain));
    }

    public function test_app_は_Laravel_の設定を返す(): void
    {
        config(['app.debug' => true]);
        $this->assertTrue(StlStaConfig::app()->debug);
    }
}
