<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications\UseCase\Account;

use BitCraft\Core\DataSources\DsHub;
use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use Exception;
use Illuminate\Support\Facades\Hash;

/**
 * APIトークン発行
 *
 * 認証に成功したときだけ、APIトークン(Sanctum)を発行する。失敗したときは、例外にする。
 */
final class UcCreateApiToken
{
    use TraitCore;
    use TraitInfra;

    /**
     * 認証に成功したときだけ、API トークン(Sanctum)を発行する。失敗したときは、例外にする。
     *
     * @param string $email
     * @param string $password
     * @return string
     * @throws Exception
     */
    public function execute(string $email, string $password): string
    {
        $account = $this->_DataSource::make(DsHub::DS_ACCOUNT)->findByEmail($email);
        if (!$account || !Hash::check($password, $account->password)) {
            throw $this->_Except::app(TypeExcept::AppAccountLoginFailed);
        }
        return $account->createToken('api-token')->plainTextToken;
    }
}
