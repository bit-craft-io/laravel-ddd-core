<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Responses\Account;

use BitCraft\Core\Http\Responses\BaseRes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * アカウント登録レスポンス
 *
 * ログイン用の文字列を、ヘッダ Primary-Code に設定して返す。
 *
 * @property-read string $primary_code
 */
final class ResAccountRegister extends BaseRes
{
    /**
     * @param Request $req
     * @return JsonResponse
     */
    public function toResponse(Request $req): JsonResponse
    {
        return response()
            ->json([
                'success' => 1,
                // @note Header に設定する為、空オブジェクトを返却
                'result' => (object)[],
            ])
            ->header('Content-Type', 'application/json')
            ->header('Primary-Code', $this->primary_code);
    }
}
