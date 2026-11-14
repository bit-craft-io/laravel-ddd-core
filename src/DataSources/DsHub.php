<?php

declare(strict_types=1);

namespace BitCraft\Core\DataSources;

/**
 * データソースハブ
 *
 * core が持つデータソースのクラス名をまとめる。
 * 利用側の DsHub(App\DataSources\DsHub)とは別。ルール R1・R3 が参照するのは、利用側の DsHub。
 */
final class DsHub
{
    const string DS_ACCOUNT = DsAccount::class;
    const string DS_U_USER = DsUUser::class;
}
