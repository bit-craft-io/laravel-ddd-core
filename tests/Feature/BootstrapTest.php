<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Feature;

use BitCraft\Core\Exceptions\Enum\TypeExcept;
use BitCraft\Core\Exceptions\ExceptApp;
use BitCraft\Core\Exceptions\ExceptModel;
use BitCraft\Core\Http\Middlewares\MdlFakeNow;
use BitCraft\Core\Http\Middlewares\MdlResponse;
use BitCraft\Core\Http\Middlewares\MdlTransaction;
use BitCraft\Core\Libraries\Stateful\Static\StfStaTransaction;
use BitCraft\Core\Libraries\Stateless\Static\StlStaBootstrap;
use BitCraft\Core\Libraries\Stateless\Static\StlStaExcept;
use BitCraft\Core\Tests\TestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class MyTestException extends \Exception
{
}

final class BootstrapTest extends TestCase
{
    protected function tearDown(): void
    {
        // @note StfStaTransaction は静的な状態を持つ為、テスト間で持ち越されないよう戻す
        (new \ReflectionProperty(StfStaTransaction::class, 'is_rollback'))->setValue(null, false);
        parent::tearDown();
    }

    private function handler(): \Illuminate\Foundation\Exceptions\Handler
    {
        $handler = $this->app->make(ExceptionHandler::class);
        $this->assertInstanceOf(\Illuminate\Foundation\Exceptions\Handler::class, $handler);
        return $handler;
    }

    public function test_別名は_core_の3つ(): void
    {
        $this->assertSame(
            ['mdl.transaction' => MdlTransaction::class, 'mdl.response' => MdlResponse::class, 'mdl.fake_now' => MdlFakeNow::class],
            StlStaBootstrap::aliases()
        );
    }

    public function test_alias_を2回呼ぶと置換される_展開すれば追加と上書きができる(): void
    {
        $m = new Middleware();
        $m->alias(StlStaBootstrap::aliases());
        $m->alias(['mdl.forward_pod' => 'X']);
        $this->assertArrayNotHasKey('mdl.transaction', $m->getMiddlewareAliases());   // 置換で消える

        $m = new Middleware();
        $m->alias([...StlStaBootstrap::aliases(), 'mdl.forward_pod' => 'X', 'mdl.response' => 'Custom']);
        $aliases = $m->getMiddlewareAliases();
        $this->assertArrayHasKey('mdl.transaction', $aliases);                        // core が残る
        $this->assertSame('X', $aliases['mdl.forward_pod']);                          // 追加
        $this->assertSame('Custom', $aliases['mdl.response']);                        // 上書き
    }

    public function test_例外の描画_ExceptApp_は422(): void
    {
        $handler = $this->handler();
        StlStaBootstrap::exceptions(new Exceptions($handler));

        $res = $handler->render(Request::create('/x'), StlStaExcept::app(TypeExcept::AppNotOkStatus));

        $this->assertInstanceOf(JsonResponse::class, $res);
        $this->assertSame(422, $res->getStatusCode());
        $this->assertSame(21100001, $res->getData(true)['result']['error_info']['code']);
        $this->assertTrue(StfStaTransaction::isRollback());
    }

    public function test_例外の描画_ExceptModel_は404_その他は401(): void
    {
        $handler = $this->handler();
        StlStaBootstrap::exceptions(new Exceptions($handler));

        $this->assertSame(404, $handler->render(Request::create('/x'), StlStaExcept::model(TypeExcept::ModelDataNotFound))->getStatusCode());
        $this->assertSame(401, $handler->render(Request::create('/x'), new \RuntimeException('x', 5))->getStatusCode());
    }

    public function test_固有の描画を先に登録すると勝つ(): void
    {
        $handler = $this->handler();
        $exceptions = new Exceptions($handler);
        $exceptions->render(fn (MyTestException $e, Request $r): JsonResponse => response()->json(['custom' => true], 418));
        StlStaBootstrap::exceptions($exceptions);

        $this->assertSame(418, $handler->render(Request::create('/x'), new MyTestException('m'))->getStatusCode());
        $this->assertSame(401, $handler->render(Request::create('/x'), new \RuntimeException('m'))->getStatusCode());
    }

    public function test_固有の描画を後に登録すると_core_が先に受ける(): void
    {
        $handler = $this->handler();
        $exceptions = new Exceptions($handler);
        StlStaBootstrap::exceptions($exceptions);
        $exceptions->render(fn (MyTestException $e, Request $r): JsonResponse => response()->json(['custom' => true], 418));

        $this->assertSame(401, $handler->render(Request::create('/x'), new MyTestException('m'))->getStatusCode());
    }
}
