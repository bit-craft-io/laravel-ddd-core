<?php

declare(strict_types=1);

namespace BitCraft\Core\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * アカウントモデル
 *
 * アカウント(認証の主体)を管理する。トークンは Sanctum で発行する。
 * 利用側は、環境変数 AUTH_MODEL に、このクラスを指定する。
 */
class Account extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime:Y-m-d H:i:s',
        'password' => 'hashed',
    ];
}
