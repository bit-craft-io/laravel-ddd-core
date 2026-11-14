<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\ValueObject;

use BitCraft\Core\DataSources\BaseMstDs;
use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Libraries\Stateful\Instance\StfInsIterator;
use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitUtil;
use App\DataSources\DsHub;

abstract class BaseMstVo extends BaseVo
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;

    /**
     * @return string
     */
    private function _dsConstName(): string
    {
        // @note VoM[Model] から DS_M_[Model] を作成
        $model_name = preg_replace('/^VoM/', '', class_basename(static::class));
        $model_snake = strtoupper(preg_replace('/([a-z])([A-Z])/', '$1_$2', $model_name));
        return 'DS_M_' . strtoupper($model_snake);
    }

    /**
     * @param int $id
     * @return self
     * @throws ExceptModel
     */
    public function findOrFail(int $id): static
    {
        $const_name = $this->_dsConstName();

        $callback = function () use ($const_name, $id) {
            // @note 動的に取得する為 @var で型を指定
            /** @var BaseMstDs $data_source */
            $data_source = $this->_DataSource::make(DsHub::{$const_name});
            $model = $data_source->findEnable($id);
            if (empty($model)) {
                throw $this->_Except::model(TypeExcept::ModelDataNotFound);
            }
            return $this->init($model);
        };

        $config = $this->_Config::core();
        if (!$config->cache_enable) {
            return $callback();
        }

        $key = "{$const_name}_{$id}";
        return $this->_Cache::array()->remember($key, $config->cache_default_ttl_sec, $callback);
    }

    /**
     * @return array<static>|StfInsIterator<static>
     */
    public function get(array $conditions = [], string $key_name = 'id'): array|StfInsIterator
    {
        $const_name = $this->_dsConstName();

        $callback = function () use ($const_name, $conditions, $key_name) {
            // @note 動的に取得する為 @var で型を指定
            /** @var BaseMstDs $data_source */
            $data_source = $this->_DataSource::make(DsHub::{$const_name});
            $models = $data_source->getEnable($conditions);
            return $this->iterator($models, $key_name);
        };

        $config = $this->_Config::core();
        if (!$config->cache_enable) {
            return $callback();
        }

        $key = "{$const_name}_{$key_name}" . serialize($conditions);
        return $this->_Cache::array()->remember($key, $config->cache_default_ttl_sec, $callback);
    }
}
