<?php

declare(strict_types=1);

namespace App\Exceptions\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

enum TypeExceptGachaDraw: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    case AppGachaDrawLotFailed = 20500001;

    public function message(array $except_params = []): string
    {
        return match ($this) {
            self::AppGachaDrawLotFailed => 'Draw lot failed',
        };
    }
}
