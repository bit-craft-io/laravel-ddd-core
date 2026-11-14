<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Requests\Account;

use BitCraft\Core\Http\Requests\BaseReq;

/**
 * アカウントログインリクエスト
 *
 * ログイン用の文字列(ヘッダ Primary-Code)を検証する。
 *
 * @property-read string $primary_code
 */
final class ReqAccountLogin extends BaseReq
{
    /**
     * @return string[]
     */
    public function rules(): array
    {
        return [
            'primary_code' => 'required|string',
        ];
    }

    /**
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'primary_code' => $this->header('Primary-Code'),
        ]);
    }
}
