<?php

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Libraries\Stateless\Static\Enum\TypeSizeUnit;
use Closure;

final class StlStaDevelopTool
{
    /**
     * 変数のサイズを計算
     *
     * @param mixed $value
     * @param TypeSizeUnit $type_size_unit
     * @param int $depth
     * @return float|null
     */
    public static function getValueSize(mixed $value, TypeSizeUnit $type_size_unit = TypeSizeUnit::Kb, int $depth = 3): float|null
    {
        $closures = function ($data) use (&$closures) {
            if (is_array($data)) {
                return array_map($closures, array_filter($data, fn($v) => !($v instanceof Closure)));
            }
            if (is_object($data)) {
                if ($data instanceof Closure) {
                    return null;
                }
                // @note オブジェクトを配列化して再帰的に処理
                $objectVars = get_object_vars($data);
                $cleanedVars = $closures($objectVars);
                // @note クラス名保持して配列化して返す（オブジェクト化はしない）
                return [
                    '__class__' => get_class($data),
                    '__data__' => $cleanedVars,
                ];
            }
            return $data;
        };

        $size = strlen(serialize($closures($value)));
        if ($type_size_unit->isKb()) {
            return round($size / 1024, $depth);
        }
        if ($type_size_unit->isMb()) {
            return round($size / 1024^2, $depth);
        }
        return null;
    }
}
