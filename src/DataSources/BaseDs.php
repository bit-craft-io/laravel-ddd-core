<?php

declare(strict_types=1);

namespace BitCraft\Core\DataSources;

use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitUtil;
use Carbon\CarbonImmutable;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseDs
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;

    // @note 副作用を防止する為 Carbon ではなく CarbonImmutable を使用
    protected ?CarbonImmutable $_now = null;
    protected ?Model $_model = null;

    /**
     * @return CarbonImmutable
     */
    final public function _now(): CarbonImmutable
    {
        if ($this->_now === null) {
            $this->_now = CarbonImmutable::now();
        }
        return $this->_now;
    }

    /**
     * @template T
     * @param Model<T> $model
     * @return void
     */
    final public function _model(Model $model): void
    {
        $this->_model = $model;
    }

    /**
     * @param array $conditions
     * @return Model|null
     */
    final public function find(array $conditions = []): ?Model
    {
        return $this->_model
            ->newQuery()
            ->where($conditions)
            ->first();
    }

    /**
     * @param array $conditions
     * @return Model
     * @throws Exception
     */
    final public function findOrFail(array $conditions = []): Model
    {
        $model = $this->_model
            ->newQuery()
            ->where($conditions)
            ->first();

        if (empty($model)) {
            throw $this->_Except::model(TypeExcept::ModelDataNotFound);
        }

        return $model;
    }

    /**
     * @param array $conditions
     * @return Collection<Model>
     */
    final public function get(array $conditions = []): Collection
    {
        return $this->_model
            ->newQuery()
            ->where($conditions)
            ->get();
    }

    /**
     * @param array $values
     * @return void
     */
    final public function create(array $values): void
    {
        $this->_model
            ->newQuery()
            ->create($values);
    }

    /**
     * @param array $values
     * @param array $conditions
     * @return void
     */
    final public function update(array $values, array $conditions): void
    {
        $this->_model
            ->newQuery()
            ->where($conditions)
            ->update($values);
    }

    /**
     * @param array $values
     * @return int
     */
    final public function createGetId(array $values): int
    {
        return (
            $this->_model
            ->newQuery()
            ->create($values)
        )->id ?? 0;
    }

    // @note 責任分離の為
    //       model に builder を記述せず where($this->enable の処理にする
    /**
     * @param string|null $column_begin
     * @param string|null $column_end
     * @return Closure
     */
    final public function enable(?string $column_begin = null, ?string $column_end = null): Closure
    {
        $now = $this->_now();
        return function ($query) use ($now, $column_begin, $column_end) {
            $query->where(function ($query) use ($now, $column_begin, $column_end): void {
                $query->whereNull($column_begin)
                    ->orWhere($column_begin, '<=', $now);
            });
            $query->where(function ($query) use ($now, $column_begin, $column_end): void {
                $query->whereNull($column_end)
                    ->orWhere($column_end, '>=', $now);
            });
        };
    }
}
