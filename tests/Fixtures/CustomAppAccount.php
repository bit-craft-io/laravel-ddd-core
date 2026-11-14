<?php

declare(strict_types=1);

namespace Fixtures;

use BitCraft\Core\Domains\Account\EntAccount;
use BitCraft\Core\Domains\RepHub;
use BitCraft\Core\Http\Applications\AppAccount;

/**
 * 案件が、登録時に User の項目を足す例(registerUser を差し替える)。
 */
final class CustomAppAccount extends AppAccount
{
    protected function registerUser(EntAccount $ent_account, string $public_id): void
    {
        $rep_user = $this->_Domain::rep(RepHub::REP_USER);
        $ent_user = $rep_user->makeDraft();
        $ent_user->id($ent_account->id);
        $ent_user->public_id($public_id);
        $ent_user->nick_name('custom');
        $ent_user->meta_data(['plan' => 'demo']);
        $rep_user->persist($ent_user);
    }
}
