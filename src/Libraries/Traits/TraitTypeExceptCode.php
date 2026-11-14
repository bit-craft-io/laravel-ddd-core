<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Traits;

/**
 * int を値に持つ enum に、ITypeExcept::code() を与える。
 */
trait TraitTypeExceptCode
{
    public function code(): int
    {
        return $this->value;
    }
}
