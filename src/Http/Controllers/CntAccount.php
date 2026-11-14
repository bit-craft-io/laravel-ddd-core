<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Controllers;

use BitCraft\Core\Http\Applications\AppAccount;
use BitCraft\Core\Http\Requests\Account\ReqAccountLogin;
use BitCraft\Core\Http\Responses\Account\ResAccountLogin;
use BitCraft\Core\Http\Responses\Account\ResAccountRegister;
use Exception;

/**
 * アカウントコントローラ
 *
 * アカウントの登録とログインを受け付ける。
 */
class CntAccount extends BaseCnt
{
    /**
     * @param AppAccount $app
     * @return void
     */
    public function register(AppAccount $app): void
    {
        $this->_ResponseModify::set(ResAccountRegister::class);
        $app->register();
    }

    /**
     * @param AppAccount $app
     * @param ReqAccountLogin $req
     * @return void
     * @throws Exception
     */
    public function login(AppAccount $app, ReqAccountLogin $req): void
    {
        $this->_ResponseModify::set(ResAccountLogin::class);
        $app->login($req);
    }
}
