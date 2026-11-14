<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications\UseCase\Account;

use BitCraft\Core\Libraries\Traits\TraitInfra;

/**
 * メールアドレス作成
 *
 * 公開用IDから、メールアドレスを作る。
 * ドメインは、利用側の config/core.php の account_mail_domain。
 */
final class UcMakeEmail
{
    use TraitInfra;

    /**
     * メールアドレスを作る。ドメインは、利用側の config/core.php の account_mail_domain。
     *
     * @param string $public_id
     * @return string
     */
    public function execute(string $public_id): string
    {
        return $public_id . '@' . $this->_Config::core()->account_mail_domain;
    }
}
