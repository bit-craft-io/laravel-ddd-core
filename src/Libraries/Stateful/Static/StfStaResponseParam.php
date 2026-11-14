<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateful\Static;

final class StfStaResponseParam
{
    protected static array $_param = [];

    /**
     * @param string $key
     * @param $value
     * @return void
     */
    public static function set(string $key, $value): void
    {
        self::$_param[$key] = $value;
    }

    /**
     * @return array
     */
    public static function get(): array
    {
        return self::$_param;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public static function find(string $key)
    {
        return self::$_param[$key] ?? null;
    }
}
