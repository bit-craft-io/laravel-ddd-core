<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Feature;

use App\DataSources\DsMItem;
use App\Domains\Item\VoMItem;
use App\Domains\Item\VoMUnknown;
use App\Models\MItem;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Libraries\Stateful\Static\StfStaDataSource;
use BitCraft\Core\Libraries\Stateless\Static\StlStaDomain;
use BitCraft\Core\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** ルール R1〜R4 を、利用側を模した fixture(tests/App)で、実際の Laravel 上で確かめる。 */
final class MstVoTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('m_items', function (Blueprint $t): void {
            $t->unsignedInteger('id')->primary();
            $t->string('name');
            $t->tinyInteger('is_active')->default(1);
            $t->dateTime('begin_at')->nullable();
            $t->dateTime('end_at')->nullable();
        });
        MItem::insert([
            ['id' => 1, 'name' => 'sword', 'is_active' => 1, 'begin_at' => null, 'end_at' => null],
            ['id' => 2, 'name' => 'expired', 'is_active' => 1, 'begin_at' => null, 'end_at' => '2000-01-01 00:00:00'],
            ['id' => 3, 'name' => 'inactive', 'is_active' => 0, 'begin_at' => null, 'end_at' => null],
            ['id' => 4, 'name' => 'shield', 'is_active' => 1, 'begin_at' => '2000-01-01 00:00:00', 'end_at' => null],
        ]);
    }

    public function test_R1_R3_VoMItem_は_DsHub_の_DS_M_ITEM_から_DsMItem_を引く(): void
    {
        config(['core.cache_enable' => false]);
        $vo = StlStaDomain::mstVo(VoMItem::class)->findOrFail(1);
        $this->assertSame('sword', $vo->name);
    }

    public function test_有効期間外と無効は_ExceptModel(): void
    {
        config(['core.cache_enable' => false]);
        foreach ([2, 3, 99] as $id) {
            try {
                StlStaDomain::mstVo(VoMItem::class)->findOrFail($id);
                $this->fail("id={$id} が見つかってしまった");
            } catch (ExceptModel $e) {
                $this->assertSame(10100001, $e->getCode());
            }
        }
    }

    public function test_get_は有効な行だけを返す(): void
    {
        config(['core.cache_enable' => false]);
        $names = [];
        foreach (StlStaDomain::mstVo(VoMItem::class)->get() as $vo) {
            $names[] = $vo->name;
        }
        sort($names);
        $this->assertSame(['shield', 'sword'], $names);
    }

    public function test_R2_DsMItem_は_Models_MItem_を使う(): void
    {
        $ds = StfStaDataSource::make(DsMItem::class);
        $model = (new \ReflectionProperty($ds, '_model'))->getValue($ds);
        $this->assertInstanceOf(MItem::class, $model);
    }

    public function test_キャッシュ有効なら2回目は_DB_に問い合わせない(): void
    {
        config(['core.cache_enable' => true, 'core.cache_default_ttl_sec' => 60]);
        DB::enableQueryLog();
        StlStaDomain::mstVo(VoMItem::class)->findOrFail(4);
        $first = count(DB::getQueryLog());
        StlStaDomain::mstVo(VoMItem::class)->findOrFail(4);
        $this->assertGreaterThan(0, $first);
        $this->assertCount($first, DB::getQueryLog());
    }

    public function test_R1違反は_Error_になる(): void
    {
        config(['core.cache_enable' => false]);
        $this->expectException(\Error::class);
        $this->expectExceptionMessageMatches('/DS_M_UNKNOWN/');
        StlStaDomain::mstVo(VoMUnknown::class)->findOrFail(1);
    }
}
