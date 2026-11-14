<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Applications\UseCase;

use BitCraft\Core\Http\Applications\UseCase\Account\UcCreateApiToken;
use BitCraft\Core\Http\Applications\UseCase\Account\UcMakeEmail;
use BitCraft\Core\Http\Applications\UseCase\Account\UcMakePrimaryCode;

/**
 * ユースケースハブ
 *
 * core が持つユースケースのクラス名をまとめる。
 */
final class UcHub
{
    const string UC_ACCOUNT_MAKE_EMAIL = UcMakeEmail::class;
    const string UC_ACCOUNT_MAKE_PRIMARY_CODE = UcMakePrimaryCode::class;
    const string UC_ACCOUNT_CREATE_API_TOKEN = UcCreateApiToken::class;
}
