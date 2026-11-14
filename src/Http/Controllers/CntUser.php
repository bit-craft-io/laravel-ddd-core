<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Controllers;

use BitCraft\Core\Http\Applications\AppUser;
use BitCraft\Core\Http\Requests\ReqNone;
use BitCraft\Core\Http\Responses\ResSuccess;

/**
 * ユーザーコントローラ
 *
 * ユーザ情報の取得を受け付ける。
 */
class CntUser extends BaseCnt
{
    /**
     * @param AppUser $app
     * @param ReqNone $req
     * @return void
     */
    public function info(AppUser $app, ReqNone $req): void
    {
        $this->_ResponseModify::set(ResSuccess::class);
        $app->info($req);
    }
}
