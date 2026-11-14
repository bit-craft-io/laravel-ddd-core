<?php

declare(strict_types=1);

namespace App\Models;

/**
 * JobAccessInfo が直接参照する、利用側のモデルの偽物(記録するだけ)。
 */
final class LogAccessInfo
{
    public static array $created = [];
    public static ?string $connection = null;

    public static function on(string $connection): static
    {
        self::$connection = $connection;
        return new static();
    }

    public function create(array $values): void
    {
        self::$created[] = $values;
    }
}
