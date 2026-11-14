<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\Account;

use BitCraft\Core\DataSources\DsHub;
use BitCraft\Core\Domains\EntHub;
use BitCraft\Core\Domains\Entity\BaseEnt;
use BitCraft\Core\Domains\Repository\BaseRep;

/**
 * アカウントリポジトリ
 *
 * アカウントの作成と保存を行う。
 */
class RepAccount extends BaseRep
{
    /**
     * @return EntAccount|BaseEnt
     */
    public function makeDraft(): EntAccount|BaseEnt
    {
        $model = $this->_DataSource::make(DsHub::DS_ACCOUNT)->makeDraft();
        return $this->_Domain::ent(EntHub::ENT_ACCOUNT)->init($model);
    }

    /**
     * @param EntAccount|BaseEnt $ent
     * @return void
     */
    public function persist(EntAccount|BaseEnt $ent): void
    {
        $ent->commit();
        $id = $this->_DataSource::make(DsHub::DS_ACCOUNT)->createGetId($ent->getProperties());

        $ent->id($id);
        $ent->commit();
    }
}
