<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;

final class StlStaUseCase
{
    /**
     * @template T of object
     * @param class-string<T> $use_case_class
     * @return T
     */
    public static function make(string $use_case_class): object
    {
        return StfStaFactory::singleton($use_case_class);
    }
}
