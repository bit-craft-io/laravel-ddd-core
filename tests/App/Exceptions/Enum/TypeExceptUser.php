<?php

declare(strict_types=1);

namespace App\Exceptions\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

enum TypeExceptUser: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    case AppUserNotFound = 20200001;

    public function message(array $except_params = []): string
    {
        return match ($this) {
            self::AppUserNotFound => 'User not found',
        };
    }
}
