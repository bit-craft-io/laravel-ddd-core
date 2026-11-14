<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Exceptions\ExceptApp;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Exceptions\ITypeExcept;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;

final class StlStaExcept
{
    /**
     * @param ITypeExcept $type_except
     * @param array $except_params
     * @return ExceptApp
     */
    public static function app(ITypeExcept $type_except, array $except_params = []): ExceptApp
    {
        $class = StfStaFactory::new(ExceptApp::class);
        $class->init($type_except, $except_params);
        return $class;
    }

    /**
     * @param ITypeExcept $type_except
     * @param array $except_params
     * @return ExceptModel
     */
    public static function model(ITypeExcept $type_except, array $except_params = []): ExceptModel
    {
        $class = StfStaFactory::new(ExceptModel::class);
        $class->init($type_except, $except_params);
        return $class;
    }
}
