<?php

declare(strict_types=1);

namespace Fixtures;

use BitCraft\Core\Http\Controllers\BaseCnt;
use BitCraft\Core\Http\Responses\Account\ResAccountRegister;

/**
 * 案件が、自分の AppAccount を使う Cnt の例(core の CntAccount は、core の AppAccount を使うため)。
 */
final class CustomCntAccount extends BaseCnt
{
    public function register(CustomAppAccount $app): void
    {
        $this->_ResponseModify::set(ResAccountRegister::class);
        $app->register();
    }
}
