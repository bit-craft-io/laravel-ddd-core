<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Feature;

use App\Models\LogAccessInfo;
use BitCraft\Core\Jobs\Contexts\CtxAccessInfo;
use BitCraft\Core\Jobs\JobAccessInfo;
use BitCraft\Core\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

/** JobAccessInfo は、利用側の App\Models\LogAccessInfo と接続 log_db を直接参照する(意図した例外)。 */
final class JobAccessInfoTest extends TestCase
{
    private function ctx(): CtxAccessInfo
    {
        return new CtxAccessInfo(_user_id: 7, _public_id: 'p', _level: 1, _api: 'api/x', _param: ['a' => 1], _file: 'f.php', _line: 12, _option_info: ['debug' => 12]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        LogAccessInfo::$created = [];
        LogAccessInfo::$connection = null;
    }

    public function test_handle_は_log_db_に8項目を書く(): void
    {
        (new JobAccessInfo($this->ctx()))->handle();

        $this->assertSame('log_db', LogAccessInfo::$connection);
        $this->assertCount(1, LogAccessInfo::$created);
        $this->assertSame(
            ['user_id', 'public_id', 'level', 'api', 'param', 'file', 'line', 'option_info'],
            array_keys(LogAccessInfo::$created[0])
        );
        $this->assertSame(7, LogAccessInfo::$created[0]['user_id']);
    }

    public function test_dispatch_でキューに積まれる(): void
    {
        Queue::fake();
        JobAccessInfo::dispatch($this->ctx());
        Queue::assertPushed(JobAccessInfo::class);
    }

    public function test_CtxAccessInfo_make_は呼び出し元を取る(): void
    {
        $ctx = CtxAccessInfo::make(7, ['debug' => 1]);
        $this->assertSame(7, $ctx->_user_id);
        $this->assertNotSame('', $ctx->_file);
    }
}
