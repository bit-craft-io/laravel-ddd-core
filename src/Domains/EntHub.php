<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains;

use BitCraft\Core\Domains\Account\EntAccount;
use BitCraft\Core\Domains\User\EntUser;

/**
 * エンティティハブ
 *
 * core が持つエンティティのクラス名をまとめる。
 */
final class EntHub
{
    const string ENT_ACCOUNT = EntAccount::class;
    const string ENT_USER = EntUser::class;
}
