<?php

declare(strict_types=1);

namespace App\Exceptions\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

enum TypeExceptItem: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    case AppItemNotHave = 20300001;
    case AppItemNotEnoughUnits = 20300002;

    public function message(array $except_params = []): string
    {
        return match ($this) {
            self::AppItemNotHave => 'Item not have',
            self::AppItemNotEnoughUnits => 'Item not enough units',
        };
    }
}
