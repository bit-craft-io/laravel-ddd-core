<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Unit;

use App\Exceptions\Enum\TypeExceptGacha;
use App\Exceptions\Enum\TypeExceptGachaDraw;
use App\Exceptions\Enum\TypeExceptItem;
use App\Exceptions\Enum\TypeExceptUser;
use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Libraries\Stateless\Static\StlStaExceptCode;
use BitCraft\Core\Tests\TestCase;
use Fixtures\Enum;
use PHPUnit\Framework\Attributes\DataProvider;

final class StlStaExceptCodeTest extends TestCase
{
    private const array VALID = [TypeExcept::class, TypeExceptUser::class, TypeExceptItem::class, TypeExceptGacha::class, TypeExceptGachaDraw::class];

    public function test_正しい構成は違反なし(): void
    {
        $r = StlStaExceptCode::check(self::VALID);
        $this->assertSame([], $r['violations']);
        $this->assertSame(13, $r['count']);
    }

    public function test_core_の移行後のコード(): void
    {
        $this->assertSame(10100001, TypeExcept::ModelDataNotFound->code());
        $this->assertSame(21100001, TypeExcept::AppNotOkStatus->code());
        $this->assertSame(21200001, TypeExcept::AppAccountLoginFailed->code());
        $this->assertSame(90900001, TypeExcept::DevelopGeneralError->code());
    }

    public function test_discover_はディレクトリから_enum_を集める(): void
    {
        $found = StlStaExceptCode::discover(__DIR__ . '/../App/Exceptions/Enum', 'App\\Exceptions\\Enum');
        $this->assertEqualsCanonicalizing(
            [TypeExceptGacha::class, TypeExceptGachaDraw::class, TypeExceptItem::class, TypeExceptUser::class],
            $found
        );
    }

    /** @return array<string, array{0: class-string, 1: string}> */
    public static function violations(): array
    {
        return [
            'V5 重複' => [Enum\TypeExceptDupItem::class, '20300001 が重複'],
            'V4 利用側が TT=10' => [Enum\TypeExceptOwn::class, 'TT=10 はcore管理'],
            'V2 予約 TT=30' => [Enum\TypeExceptReserved::class, 'TT=30 は予約'],
            'V2 未割当 TT=22' => [Enum\TypeExceptUnassigned::class, 'TT=22(アプリ)は未割当'],
            'V1 7桁' => [Enum\TypeExceptShortCode::class, '8桁ではない'],
            'V6 メッセージ未定義' => [Enum\TypeExceptNoMsg::class, 'メッセージが未定義'],
            'V3 (TT, DDD) 不揃い' => [Enum\TypeExceptMix::class, '(TT, DDD) が揃っていない'],
            'V7 種別が TT と不一致' => [Enum\TypeExceptKind::class, '期待する先頭: AppKind'],
            'V7 内容なし' => [Enum\TypeExceptNoContent::class, 'ケース名は {種別}{領域}{内容} の形'],
            'V7 内容が小文字始まり' => [Enum\TypeExceptLower::class, 'ケース名は {種別}{領域}{内容} の形'],
            'V7 enum 名' => [Enum\Custom::class, 'enum 名は TypeExcept{領域} の形'],
        ];
    }

    #[DataProvider('violations')]
    public function test_違反を検出する(string $enum, string $expected): void
    {
        $r = StlStaExceptCode::check([...self::VALID, $enum]);
        $this->assertNotSame([], $r['violations']);
        $this->assertStringContainsString($expected, implode("\n", $r['violations']));
    }

    public function test_ITypeExcept_を実装しないクラスは違反(): void
    {
        $r = StlStaExceptCode::check([\stdClass::class]);
        $this->assertStringContainsString('ITypeExcept を実装した enum ではない', $r['violations'][0]);
    }
}
