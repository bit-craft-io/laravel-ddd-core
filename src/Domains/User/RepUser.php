<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\User;

use BitCraft\Core\DataSources\DsHub;
use BitCraft\Core\Domains\EntHub;
use BitCraft\Core\Domains\Entity\BaseEnt;
use BitCraft\Core\Domains\Repository\BaseRep;

/**
 * ユーザーリポジトリ
 *
 * ユーザ情報の取得と保存を行う。
 * エンティティを差し替えるときは、継承して ENT_CLASS を指定する。
 */
class RepUser extends BaseRep
{
    protected const string ENT_CLASS = EntHub::ENT_USER;

    /**
     * @return EntUser|BaseEnt
     */
    public function makeDraft(): EntUser|BaseEnt
    {
        $model = $this->_DataSource::make(DsHub::DS_U_USER)->makeDraft();
        return $this->_Domain::ent(static::ENT_CLASS)->init($model);
    }

    /**
     * @param string $public_id
     * @return EntUser|BaseEnt
     */
    public function findByPublicId(string $public_id): EntUser|BaseEnt
    {
        $model = $this->_DataSource::make(DsHub::DS_U_USER)->findByPublicId($public_id);
        return $this->_Domain::ent(static::ENT_CLASS)->init($model);
    }

    /**
     * @param int $user_id
     * @return EntUser|BaseEnt
     */
    public function findByUserId(int $user_id): EntUser|BaseEnt
    {
        $model = $this->_DataSource::make(DsHub::DS_U_USER)->findByUserId($user_id);
        return $this->_Domain::ent(static::ENT_CLASS)->init($model);
    }

    /**
     * @param EntUser|BaseEnt $ent
     * @return void
     */
    public function persist(EntUser|BaseEnt $ent): void
    {
        $ent->commit();
        if ($ent->isNew()) {
            $this->_DataSource::make(DsHub::DS_U_USER)->create($ent->getProperties());
        } else {
            $this->_DataSource::make(DsHub::DS_U_USER)->update($ent->getProperties(), ['id' => $ent->id]);
        }
    }
}
