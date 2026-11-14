<?php

declare(strict_types=1);

namespace BitCraft\Core\Domains\User;

use BitCraft\Core\Domains\Entity\BaseEnt;

/**
 * ユーザーエンティティ
 *
 * ユーザ情報を表す。案件の項目は、継承して足す(class EntUser extends CoreEntUser)。
 *
 * @method void id(int $value)
 * @method void public_id(string $value)
 * @method void nick_name(string $value)
 * @method void meta_data(object|array $value)
 * @property-read integer $id
 * @property-read string $public_id
 * @property-read string $nick_name
 * @property-read object|array|null $meta_data クライアントが自由に保存する領域(JSON)。サーバは解釈しない
 */
class EntUser extends BaseEnt
{
    public function initOnce(): void
    {
    }

    public function initAfter(): void
    {
    }

    public function isEmpty(): bool
    {
        return ($this->id ?? 0) == 0;
    }

    public function toArray(): array
    {
        return [
            'public_id' => $this->public_id,
            'nick_name' => $this->nick_name,
            'meta_data' => $this->meta_data,
        ];
    }
}
