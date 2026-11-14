<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications;

use BitCraft\Core\Domains\RepHub;
use BitCraft\Core\Http\Requests\ReqNone;

/**
 * ユーザーユースケース
 *
 * ユーザ情報を取得する。
 */
class AppUser extends BaseApp
{
    /**
     * @param ReqNone $req
     * @return void
     */
    public function info(ReqNone $req): void
    {
        $rep_user = $this->_Domain::rep(RepHub::REP_USER);
        $ent_user = $rep_user->findByUserId($req->user_id);
        $this->_ResponseParam::set('user_info', $ent_user->toArray());
    }
}
