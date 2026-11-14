<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateful\Static;

use Illuminate\Support\Facades\DB;

final class StfStaTransaction
{
    private static bool $is_rollback = false;

    /**
     * @return void
     */
    public static function begin(): void
    {
        DB::beginTransaction();
    }

    /**
     * @return void
     */
    public static function commit(): void
    {
        DB::commit();
    }

    /**
     * @return void
     */
    public static function rollback(): void
    {
        DB::rollBack();
    }

    /**
     * @return int
     */
    public static function getLevel(): int
    {
        return DB::transactionLevel();
    }

    /**
     * @return bool
     */
    public static function isRollback(): bool
    {
        return self::$is_rollback;
    }

    /**
     * @return void
     */
    public static function enableRollback(): void
    {
        self::$is_rollback = true;
    }
}
