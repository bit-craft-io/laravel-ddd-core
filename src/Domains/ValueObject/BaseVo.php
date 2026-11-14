<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\ValueObject;

use BitCraft\Core\Libraries\Stateful\Instance\StfInsIterator;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseVo
{
    protected Model|array|null $_props = null;

    /**
     * @param Model|array $props
     * @return $this
     */
    public function init(Model|array $props): self
    {
        $this->_props = $props;
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        return $this?->_props[$name] ?? null;
    }

    // @note ValueObject は immutable の為、setter は存在しない
    //public function __call(string $name, array $arguments = [])

    /**
     * @param Collection $collect
     * @param string $key_name
     * @return StfInsIterator
     */
    public function iterator(Collection $collect, string $key_name = 'id'): StfInsIterator
    {
        $callable = function ($model) {
            if ($model && method_exists($this, 'init')) {
                $this->init($model);
            }
            return $this;
        };
        $class = StfStaFactory::prototype(StfInsIterator::class);
        $class->init($callable, $collect, $key_name);
        return $class;
    }
}
