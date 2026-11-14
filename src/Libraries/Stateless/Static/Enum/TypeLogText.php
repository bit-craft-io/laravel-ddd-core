<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static\Enum;

enum TypeLogText: int
{
    case Api = 1;
    case Cls = 2;

    public function isApi(): bool
    {
        return $this == self::Api;
    }

    public function isClass(): bool
    {
        return $this == self::Cls;
    }
}
