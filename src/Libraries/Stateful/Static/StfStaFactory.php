<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateful\Static;

final class StfStaFactory
{
    /** @var array<object>|null  */
    private static ?array $_prototype = null;

    /** @var array<object>|null  */
    private static ?array $_singleton = null;

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function new(string $class): object
    {
        return app($class);
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function prototype(string $class, bool $is_singleton = false): object
    {
        if (!app()->has($class)) {
            app()->singleton($class);
            self::$_singleton[$class] = app($class);
            if (method_exists(self::$_singleton[$class], 'initOnce')) {
                self::$_singleton[$class]->initOnce();
            }
            self::$_prototype[$class] = clone self::$_singleton[$class];
        }
        if ($is_singleton) {
            return self::$_singleton[$class];
        }
        return clone self::$_prototype[$class];
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    public static function singleton(string $class): object
    {
        return self::prototype($class, true);
    }
}
