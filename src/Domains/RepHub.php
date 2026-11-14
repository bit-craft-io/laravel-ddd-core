<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains;

use BitCraft\Core\Domains\Account\RepAccount;
use BitCraft\Core\Domains\User\RepUser;

/**
 * リポジトリハブ
 *
 * core が持つリポジトリのクラス名をまとめる。
 */
final class RepHub
{
    const string REP_ACCOUNT = RepAccount::class;
    const string REP_USER = RepUser::class;
}
