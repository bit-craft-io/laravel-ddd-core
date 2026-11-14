<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateful\Static;

use BitCraft\Core\DataSources\BaseDs;
use Illuminate\Database\Eloquent\Model;

final class StfStaDataSource
{
    private static ?array $_instances = null;

    /**
     * @template T
     * @param T $data_source_class
     * @return T
     */
    public static function make(string $data_source_class)
    {
        $instance = self::$_instances[$data_source_class] ?? null;
        if ($instance) {
            return $instance;
        }

        [$app_dir, $model_class_name] = explode('\\DataSources\\Ds', $data_source_class);
        $model_name = "$app_dir\\Models\\$model_class_name";

        /** @var BaseDs $instance */
        $instance = StfStaFactory::singleton($data_source_class);

        /** @var Model $model */
        $model = StfStaFactory::singleton($model_name);

        $instance->_model($model);

        self::$_instances[$data_source_class] = $instance;

        return $instance;
    }
}
