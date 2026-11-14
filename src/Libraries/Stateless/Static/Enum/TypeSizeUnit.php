<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static\Enum;

enum TypeSizeUnit: int
{
    case Kb = 1;
    case Mb = 2;

    public function isKb(): bool
    {
        return $this == self::Kb;
    }

    public function isMb(): bool
    {
        return $this == self::Mb;
    }
}
