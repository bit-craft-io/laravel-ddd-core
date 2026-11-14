<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\ValueProxy;

abstract class BaseVp
{
    protected object|null $_proxy_object = null;
    protected array|null $_proxy_array = null;
    protected array $_props = [];

    /**
     * @param array|object $proxy_values
     * @return $this
     */
    public function init(array|object &$proxy_values): self
    {
        if (is_array($proxy_values)) {
            $this->_proxy_array = &$proxy_values;
            $this->_props = $proxy_values;
            return $this;
        }

        $this->_proxy_object = &$proxy_values;
        $this->_props = $proxy_values->getProperties();
        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name)
    {
        if ($this->_proxy_array) {
            return $this->_proxy_array[$name] ?? null;
        }
        return $this?->_proxy_object?->$name;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return void
     */
    public function __call(string $name, array $arguments = [])
    {
        if (!array_key_exists($name, $this->_props)) {
            return;
        }
        if ($this->_proxy_array) {
            $this->_proxy_array[$name] = $arguments[0];
            return;
        }
        $this->_proxy_object->$name($arguments[0]);
    }
}
