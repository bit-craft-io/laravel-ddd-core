<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\Account;

use BitCraft\Core\Domains\Entity\BaseEnt;

/**
 * アカウントエンティティ
 *
 * アカウントを表す。
 *
 * @method void id(integer $value)
 * @method void name(string $value)
 * @method void email(string $value)
 * @method void password(string $value)
 * @property-read integer $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string $password
 */
class EntAccount extends BaseEnt
{
    /**
     * @return void
     */
    public function initOnce(): void
    {
    }

    /**
     * @return void
     */
    public function initAfter(): void
    {
    }
}
