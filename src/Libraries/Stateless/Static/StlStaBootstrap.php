<?php

declare(strict_types=1);

namespace BitCraft\Core\Libraries\Stateless\Static;

use BitCraft\Core\Exceptions\ExceptApp;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Http\Middlewares\MdlFakeNow;
use BitCraft\Core\Http\Middlewares\MdlResponse;
use BitCraft\Core\Http\Middlewares\MdlTransaction;
use BitCraft\Core\Http\Responses\ResFailed;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use BitCraft\Core\Libraries\Stateful\Static\StfStaTransaction;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

/**
 * 利用側の bootstrap/app.php から呼ぶ、core の基底の配線。
 * 利用側は、固有の配線を追加または上書きする。
 */
final class StlStaBootstrap
{
    /**
     * core のミドルウェア別名。
     *
     * @note Middleware::alias() は、追加ではなく置換の為、配列で返す。利用側が展開して、固有の別名を足す
     * @return array<string, class-string>
     */
    public static function aliases(): array
    {
        return [
            'mdl.transaction' => MdlTransaction::class,
            'mdl.response' => MdlResponse::class,
            'mdl.fake_now' => MdlFakeNow::class,
        ];
    }

    /**
     * core の例外の描画を登録する(全ての Throwable を受ける)。
     *
     * @note Exceptions::render() は、登録順に評価され、最初に null 以外を返したものが勝つ。
     *       利用側の固有の描画は、この呼び出しより先に登録する
     * @param Exceptions $exceptions
     * @return void
     */
    public static function exceptions(Exceptions $exceptions): void
    {
        $exceptions->render(function (Throwable $e, Request $request): JsonResponse {

            // @note transaction rollback enable
            StfStaTransaction::enableRollback();

            $class = StfStaFactory::singleton(ResFailed::class);
            $class->setParams([
                'error_info' => [
                    'code' => $e->getCode(),
                    'message' => $e->getMessage(),
                ],
            ]);
            if ($e instanceof ExceptApp) {
                return $class->toResponse($request)
                    ->setStatusCode(422);
            }
            if ($e instanceof ExceptModel) {
                return $class->toResponse($request)
                    ->setStatusCode(404);
            }
            return $class->toResponse($request)
                ->setStatusCode(401);
        });
    }
}
