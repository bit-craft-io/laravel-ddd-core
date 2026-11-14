<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateful\Static;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

final class StfStaCache
{
    /**
     * @return Repository
     */
    public static function array(): Repository
    {
        return Cache::store('array');
    }

    /**
     * @return Repository
     */
    public static function redis(): Repository
    {
        return Cache::store('redis');
    }
}
