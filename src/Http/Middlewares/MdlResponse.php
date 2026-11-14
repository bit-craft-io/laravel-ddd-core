<?php

declare(strict_types=1);

namespace BitCraft\Core\Http\Middlewares;

use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Http\Requests\BaseReq;
use BitCraft\Core\Http\Responses\ResSuccess;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use BitCraft\Core\Libraries\Traits\TraitDomain;
use BitCraft\Core\Libraries\Traits\TraitInfra;
use BitCraft\Core\Libraries\Traits\TraitCore;
use BitCraft\Core\Libraries\Traits\TraitDevelop;
use BitCraft\Core\Libraries\Traits\TraitResponse;
use BitCraft\Core\Libraries\Traits\TraitUtil;
use Closure;
use Exception;
use Illuminate\Http\JsonResponse;

final class MdlResponse
{
    use TraitCore;
    use TraitUtil;
    use TraitInfra;
    use TraitDomain;
    use TraitResponse;
    use TraitDevelop;

    /**
     * @param $request
     * @param Closure $next
     * @return JsonResponse
     * @throws Exception
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // @note BaseReq を継承したクラスをコンストラクタインジェクションして無い場合
        //       ミドルウェアはクロージャを入れ子の状態にして実行する為
        //       BaseReq の処理が実行されない状態で本処理が実行される
        if (!BaseReq::$_is_merged_user_id) {
            $request->merge(['user_id' => ($request?->user()->id ?? null)]);
        }

        if (200 !== (int) $response->getStatusCode()) {
            throw $this->_Except::app(TypeExcept::AppNotOkStatus);
        }

        $class = $this->_ResponseModify::find();
        if ($class) {
            $class->setParams($this->_ResponseParam::get());
            return $this->_finalize($class->toResponse($request));
        }

        $class = StfStaFactory::singleton(ResSuccess::class);
        $class->setParams($this->_ResponseParam::get());
        return $this->_finalize($class->toResponse($request));
    }

    /**
     * @param JsonResponse $response
     * @return JsonResponse
     */
    private function _finalize(JsonResponse $response): JsonResponse
    {
        if ($this->_Config::app()->debug) {
            $size = $this->_DevTool::getValueSize($response);
            $this->_Log::info("Response Size = $size Kb");
        }
        return $response;
    }
}
