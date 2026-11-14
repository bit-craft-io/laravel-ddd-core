<?php

declare(strict_types=1);

namespace BitCraft\Core\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ユーザーモデル
 *
 * ユーザ情報を管理する。テーブルは u_users。
 */
class UUser extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'id',
        'public_id',
        'nick_name',
        'meta_data',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        'meta_data' => 'json',
    ];
}
