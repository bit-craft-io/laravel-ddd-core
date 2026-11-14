<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Responses\Account;

use BitCraft\Core\Http\Responses\BaseRes;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * アカウントログインレスポンス
 *
 * APIトークンを、ヘッダ WWW-Authenticate に設定して返す。
 *
 * @property-read string $bearer_token
 */
final class ResAccountLogin extends BaseRes
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
            ->header('WWW-Authenticate', 'Bearer ' . $this->bearer_token);
    }
}
