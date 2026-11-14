<?php

declare(strict_types=1);

namespace BitCraft\Core\Exceptions;

/**
 * 例外コードの enum の契約。
 * core の TypeExcept と、利用側の TypeExcept{領域} が実装する。
 */
interface ITypeExcept
{
    /**
     * @return int 8桁のコード(TT DDD NNN)
     */
    public function code(): int;

    /**
     * @param array $except_params
     * @return string
     */
    public function message(array $except_params = []): string;
}
