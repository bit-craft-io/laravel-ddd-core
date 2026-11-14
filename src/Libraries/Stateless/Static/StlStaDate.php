<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Cache;

final class StlStaDate
{
    private static ?CarbonInterface $_base_now = null;
    private static int $_fake_now_expire_sec = 60;

    /**
     * @param int $user_id
     * @param string $fake_at
     * @return void
     */
    public static function setFakeNow(int $user_id, string $fake_at): void
    {
        if (!CarbonImmutable::canBeCreatedFromFormat($fake_at, 'Y-m-d H:i:s')) {
            return;
        }

        // @note リセット
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        $fake_date_time = CarbonImmutable::parse($fake_at);
        $offset_sec = $fake_date_time->timestamp - CarbonImmutable::now()->timestamp;
        Cache::set(self::_fakeNowKey($user_id), $offset_sec, self::$_fake_now_expire_sec);
    }

    /**
     * @param int $user_id
     * @return void
     */
    public static function unsetFakeNow(int $user_id): void
    {
        Cache::forget(self::_fakeNowKey($user_id));
    }

    /**
     * @return CarbonInterface
     */
    public static function getFakeNow(): CarbonInterface
    {
        return CarbonImmutable::now();
    }

    /**
     * @param int $user_id
     * @return void
     */
    public static function applyFakeNow(int $user_id): void
    {
        $offset = intval(Cache::get(self::_fakeNowKey($user_id)) ?? 0);
        if ($offset === 0) {
            return;
        }

        // @note リセット
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();

        // @note 設定
        $fakeNow = CarbonImmutable::now()->addSeconds($offset);
        Carbon::setTestNow($fakeNow);
        CarbonImmutable::setTestNow($fakeNow);
    }

    /**
     * @param int $user_id
     * @return string
     */
    private static function _fakeNowKey(int $user_id): string
    {
        return "user_fake_offset_sec::$user_id";
    }

    /**
     * @return CarbonInterface
     */
    public static function baseNow(): CarbonInterface
    {
        if (empty(self::$_base_now)) {
            self::$_base_now = CarbonImmutable::createFromTimestamp(LARAVEL_START)
                ->timezone(date_default_timezone_get());
        }
        return self::$_base_now;
    }
}
