<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications;

use BitCraft\Core\Domains\Account\EntAccount;
use BitCraft\Core\Domains\RepHub;
use BitCraft\Core\Http\Applications\UseCase\UcHub;
use BitCraft\Core\Http\Requests\Account\ReqAccountLogin;
use Exception;

/**
 * アカウントユースケース
 *
 * アカウントの登録とログインを行う。
 * 案件のユーザの項目が要るときは、継承して registerUser() を差し替える(案件側の Cnt から使う)。
 */
class AppAccount extends BaseApp
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->_Transaction::begin();

        $public_id = $this->_UtilNanoId::base32();
        $email = $this->_UseCase::make(UcHub::UC_ACCOUNT_MAKE_EMAIL)->execute($public_id);
        $password = $this->_UtilNanoId::base32(8);

        $primary_code = $this->_UseCase::make(UcHub::UC_ACCOUNT_MAKE_PRIMARY_CODE)->execute($email, $password);
        $this->_ResponseParam::set('primary_code', $primary_code);

        $rep_account = $this->_Domain::rep(RepHub::REP_ACCOUNT);
        $ent_account = $rep_account->makeDraft();
        $ent_account->name('none');
        $ent_account->email($email);
        $ent_account->password($password);
        $rep_account->persist($ent_account);

        $this->registerUser($ent_account, $public_id);
    }

    /**
     * アカウントに紐づくユーザを作る。案件の項目は、継承して、ここで設定する。
     *
     * @param EntAccount $ent_account
     * @param string $public_id
     * @return void
     */
    protected function registerUser(EntAccount $ent_account, string $public_id): void
    {
        $rep_user = $this->_Domain::rep(RepHub::REP_USER);
        $ent_user = $rep_user->makeDraft();
        $ent_user->id($ent_account->id);
        $ent_user->public_id($public_id);
        $ent_user->nick_name('none');
        $rep_user->persist($ent_user);
    }

    /**
     * @param ReqAccountLogin $req
     * @return void
     * @throws Exception
     */
    public function login(ReqAccountLogin $req): void
    {
        [$email, $password] = explode(':', $this->_UtilCompress::unComp($req->primary_code));
        $bearer_token = $this->_UseCase::make(UcHub::UC_ACCOUNT_CREATE_API_TOKEN)->execute($email, $password);
        $this->_ResponseParam::set('bearer_token', $bearer_token);
    }
}
