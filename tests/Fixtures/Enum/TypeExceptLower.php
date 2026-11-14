<?php

declare(strict_types=1);

namespace Fixtures\Enum;

use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Traits\TraitTypeExceptCode;

/** 検査が違反を検出することを確かめるための、違反する enum。 */
enum TypeExceptLower: int implements ITypeExcept
{
    use TraitTypeExceptCode;

    case AppLowerx = 21300001;

    public function message(array $except_params = []): string
    {
        return 'x';
    }
}
