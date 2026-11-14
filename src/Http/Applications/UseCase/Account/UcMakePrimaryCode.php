<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications\UseCase\Account;

use BitCraft\Core\Libraries\Traits\TraitUtil;

/**
 * ログインコード作成
 *
 * メールアドレスとパスワードから、ログイン用の文字列(primary_code)を作る。
 */
final class UcMakePrimaryCode
{
    use TraitUtil;

    /**
     * ログインに使う文字列(メールアドレスとパスワードを圧縮したもの)を作る。
     *
     * @param string $email
     * @param string $password
     * @return string
     */
    public function execute(string $email, string $password): string
    {
        return $this->_UtilCompress::comp("$email:$password");
    }
}
