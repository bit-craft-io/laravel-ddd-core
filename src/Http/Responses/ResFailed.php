<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @property-read integer $code
 * @property-read string $message
 */
final class ResFailed extends BaseRes
{
    /**
     * @param Request $req
     * @return JsonResponse
     */
    public function toResponse(Request $req): JsonResponse
    {
        return response()
            ->json([
                'success' => 0,
                'result' => (object)$this->_result,
                // @note MdlResponse で設定
                //'result' => [
                //    'error_info' => [
                //        'code' => $this->code,
                //        'message' => $this->message
                //    ],
                //]
            ]);
    }
}
