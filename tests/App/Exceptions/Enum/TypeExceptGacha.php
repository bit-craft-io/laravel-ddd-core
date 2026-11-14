<?php

declare(strict_types=1);

namespace App\Exceptions\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

enum TypeExceptGacha: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    case AppGachaItemIsEmpty = 20400001;
    case AppGachaCostIsNotEnough = 20400002;
    case AppGachaMasterIsNotValid = 20400003;
    case AppGachaExecCountOver = 20400004;
    case AppGachaStepNotEqual = 20400005;

    public function message(array $except_params = []): string
    {
        return match ($this) {
            self::AppGachaItemIsEmpty => 'Item is empty',
            self::AppGachaCostIsNotEnough => 'Cost is not enough',
            self::AppGachaMasterIsNotValid => strtr('Master is not valid [gacha.id #1]', $except_params),
            self::AppGachaExecCountOver => strtr('Exec count over [gacha.group_no #1]', $except_params),
            self::AppGachaStepNotEqual => strtr('Step is not equal [step m #1 != u #2]', $except_params),
        };
    }
}
