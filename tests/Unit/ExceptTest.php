<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Unit;

use App\Exceptions\Enum\TypeExceptGacha;
use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Exceptions\ExceptApp;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Libraries\Stateless\Static\StlStaExcept;
use BitCraft\Core\Tests\TestCase;

final class ExceptTest extends TestCase
{
    public function test_core_の_enum_を渡せる(): void
    {
        $e = StlStaExcept::app(TypeExcept::AppNotOkStatus);
        $this->assertInstanceOf(ExceptApp::class, $e);
        $this->assertSame(21100001, $e->getCode());
        $this->assertSame('Not Ok Status', $e->getMessage());
    }

    public function test_利用側の_enum_を渡せる(): void
    {
        $e = StlStaExcept::app(TypeExceptGacha::AppGachaExecCountOver, ['#1' => 7]);
        $this->assertSame(20400004, $e->getCode());
        $this->assertSame('Exec count over [gacha.group_no 7]', $e->getMessage());
    }

    public function test_モデル例外(): void
    {
        $e = StlStaExcept::model(TypeExcept::ModelDataNotFound);
        $this->assertInstanceOf(ExceptModel::class, $e);
        $this->assertSame(10100001, $e->getCode());
    }

    public function test_ITypeExcept_を実装しない値は型エラー(): void
    {
        $this->expectException(\TypeError::class);
        (new ExceptApp())->init(\Illuminate\Http\Response::HTTP_OK);
    }
}
