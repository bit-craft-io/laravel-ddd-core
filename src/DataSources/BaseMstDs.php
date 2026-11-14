<?php

declare(strict_types=1);

namespace BitCraft\Core\DataSources;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseMstDs extends BaseDs
{
    /**
     * @return Collection<static>
     */
    public function getEnable(array $conditions = []): Collection
    {
        return $this->_model
            ->newQuery()
            ->where($conditions)
            ->where('is_active', 1)
            ->where($this->enable('begin_at', 'end_at'))
            ->get();
    }

    /**
     * @param int $id
     * @return Model<static>|null
     */
    public function findEnable(int $id): ?Model
    {
        return $this->_model
            ->newQuery()
            ->where('id', $id)
            ->where('is_active', 1)
            ->where($this->enable('begin_at', 'end_at'))
            ->first();
    }
}
