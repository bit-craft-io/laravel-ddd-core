<?php

declare(strict_types=1);

namespace BitCraft\Core\Exceptions\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

/**
 * core が管理する例外コード(TT=10 モデル、TT=21 アプリ基盤、TT=90 開発)。
 * 案件固有のコードは、利用側の TypeExcept{領域} に置く。
 */
enum TypeExcept: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    // @note TT=10 モデル
    case ModelDataNotFound = 10100001;

    // @note TT=21 アプリ基盤
    case AppNotOkStatus = 21100001;

    // @note TT=21 アプリ基盤(領域 DDD=200 アカウント)
    case AppAccountLoginFailed = 21200001;

    // @note TT=90 開発
    case DevelopGeneralError = 90900001;

    public function message(array $except_params = []): string
    {
        return match ($this) {
            self::ModelDataNotFound => 'Data not found',
            self::AppNotOkStatus => 'Not Ok Status',
            self::AppAccountLoginFailed => 'Login failed',
            self::DevelopGeneralError => 'develop error',
        };
    }
}
