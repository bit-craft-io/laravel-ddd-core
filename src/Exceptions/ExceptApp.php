<?php

declare(strict_types=1);

namespace BitCraft\Core\Exceptions;

use Exception;

final class ExceptApp extends Exception
{
    /**
     * @param ITypeExcept $type_except
     * @param array $except_params
     * @return $this
     */
    public function init(ITypeExcept $type_except, array $except_params = []): self
    {
        $this->code = $type_except->code();
        $this->message = $type_except->message($except_params);
        return $this;
    }
}
