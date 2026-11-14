<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use Hidehalo\Nanoid\Client;

final class StlStaUtilNanoId
{
    private const string BASE32 = '23456789abcdefghijkmnpqrstuvwxyz';
    private const string BASE54 = '23456789abcdefghijkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';

    /**
     * @param int $digits
     * @return string
     */
    public static function base32(int $digits = 12): string
    {
        return StfStaFactory::singleton(Client::class)
            ->formattedId(self::BASE32, $digits);
    }

    public static function base54(int $digits = 12): string
    {
        return StfStaFactory::singleton(Client::class)
            ->formattedId(self::BASE54, $digits);
    }
}
