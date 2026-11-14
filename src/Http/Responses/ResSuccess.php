<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Responses;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ResSuccess extends BaseRes
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
                'result' => (object)$this->_result
            ]);
    }
}
