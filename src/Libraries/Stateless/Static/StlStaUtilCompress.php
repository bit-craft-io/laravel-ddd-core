<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

final class StlStaUtilCompress
{
    /**
     * @param string $value
     * @return string
     */
    public static function comp(string $value): string
    {
        return base64_encode(gzcompress($value));
    }

    /**
     * @param string $value
     * @return string
     */
    public static function unComp(string $value): string
    {
        return gzuncompress(base64_decode($value));
    }
}
