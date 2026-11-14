<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Libraries\Stateless\Static\Interface\IStlStaConfigApp;
use BitCraft\Core\Libraries\Stateless\Static\Interface\IStlStaConfigCore;
use BitCraft\Core\Libraries\Stateless\Static\Interface\IStlStaConfigDevelop;

final class StlStaConfig
{
    /**
     * @return IStlStaConfigApp|object
     */
    public static function app(): object
    {
        return (object)config('app');
    }

    /**
     * @return IStlStaConfigCore|object
     */
    public static function core(): object
    {
        return (object)config('core');
    }

    /**
     * @return IStlStaConfigDevelop|object
     */
    public static function develop(): object
    {
        return (object)config('develop');
    }
}
