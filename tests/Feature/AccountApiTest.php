<?php

declare(strict_types=1);

namespace BitCraft\Core\Tests\Feature;

use BitCraft\Core\Exceptions\ExceptApp;
use BitCraft\Core\Http\Controllers\CntAccount;
use BitCraft\Core\Http\Controllers\CntUser;
use BitCraft\Core\Http\Requests\BaseReq;
use BitCraft\Core\Libraries\Stateful\Static\StfStaDataSource;
use BitCraft\Core\Libraries\Stateful\Static\StfStaFactory;
use BitCraft\Core\Libraries\Stateful\Static\StfStaResponseModify;
use BitCraft\Core\Libraries\Stateful\Static\StfStaResponseParam;
use BitCraft\Core\Libraries\Stateful\Static\StfStaTransaction;
use BitCraft\Core\Libraries\Stateless\Static\StlStaBootstrap;
use BitCraft\Core\Models\Account;
use BitCraft\Core\Tests\TestCase;
use Fixtures\CustomCntAccount;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\SanctumServiceProvider;

/**
 * Account と User の API(register、login、user/info)を、実際の Laravel で確認する。
 */
final class AccountApiTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        return [SanctumServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);
        $app['config']->set('auth.providers.users.model', Account::class);
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadMigrationsFrom(__DIR__ . '/../../vendor/laravel/sanctum/database/migrations');
    }

    protected function defineRoutes($router): void
    {
        foreach (StlStaBootstrap::aliases() as $alias => $class) {
            $router->aliasMiddleware($alias, $class);
        }
        $router->middleware(['mdl.response', 'mdl.transaction'])->group(function () use ($router) {
            $router->post('account/register', [CntAccount::class, 'register']);
            $router->post('account/login', [CntAccount::class, 'login']);
            $router->post('custom/register', [CustomCntAccount::class, 'register']);
        });
        $router->middleware(['auth:sanctum', 'mdl.response', 'mdl.transaction'])->group(function () use ($router) {
            $router->get('user/info', [CntUser::class, 'info']);
        });
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->resetContainerStatics();
        $this->resetStatics();
    }

    protected function tearDown(): void
    {
        $this->resetContainerStatics();
        $this->resetStatics();
        parent::tearDown();
    }

    /**
     * @note StfStaFactory の静的な配列は、Laravel のコンテナの登録と対になっている。テストごとに、コンテナは作り直される為、
     *       テストの前後(コンテナの作り直しと同時)にだけ戻す。テストの途中で戻すと、不整合になる
     */
    private function resetContainerStatics(): void
    {
        foreach ([
            [StfStaFactory::class, '_singleton', null],
            [StfStaFactory::class, '_prototype', null],
            [StfStaDataSource::class, '_instances', null],
        ] as [$class, $name, $value]) {
            (new \ReflectionProperty($class, $name))->setValue(null, $value);
        }
    }

    /**
     * @note 本番(PHP-FPM)は、リクエストごとに静的な状態が破棄される。テストは、同じプロセスで、複数のリクエストを送る為、戻す
     */
    private function resetStatics(): void
    {
        foreach ([
            [StfStaResponseParam::class, '_param', []],
            [StfStaResponseModify::class, '_response', null],
            [StfStaTransaction::class, 'is_rollback', false],
        ] as [$class, $name, $value]) {
            (new \ReflectionProperty($class, $name))->setValue(null, $value);
        }
        BaseReq::$_is_merged_user_id = false;
    }

    private function register(string $uri = 'account/register'): string
    {
        $this->resetStatics();
        $response = $this->postJson($uri);
        $response->assertOk()->assertJson(['success' => 1]);
        $code = $response->headers->get('Primary-Code');
        $this->assertNotEmpty($code);
        return $code;
    }

    private function login(string $primary_code): string
    {
        $this->resetStatics();
        $response = $this->postJson('account/login', [], ['Primary-Code' => $primary_code]);
        $response->assertOk()->assertJson(['success' => 1]);
        $auth = $response->headers->get('WWW-Authenticate');
        $this->assertStringStartsWith('Bearer ', $auth);
        return substr($auth, strlen('Bearer '));
    }

    private function userInfo(string $token): array
    {
        $this->resetStatics();
        $response = $this->getJson('user/info', ['Authorization' => "Bearer $token"]);
        $response->assertOk()->assertJson(['success' => 1]);
        return $response->json('result.user_info');
    }

    public function test_登録するとアカウントとユーザが作られる(): void
    {
        $this->register();

        $this->assertSame(1, DB::table('accounts')->count());
        $this->assertSame(1, DB::table('u_users')->count());
        $user = DB::table('u_users')->first();
        $account = DB::table('accounts')->first();
        $this->assertSame($account->id, $user->id);
        $this->assertSame('none', $user->nick_name);
        $this->assertSame($user->public_id . '@account.invalid', $account->email);
    }

    public function test_メールのドメインは_設定で変えられる(): void
    {
        config(['core.account_mail_domain' => 'example.test']);
        $this->register();

        $this->assertStringEndsWith('@example.test', DB::table('accounts')->value('email'));
    }

    public function test_ログインするとトークンが発行され_ユーザの情報を取得できる(): void
    {
        $token = $this->login($this->register());

        $info = $this->userInfo($token);
        $this->assertSame(DB::table('u_users')->value('public_id'), $info['public_id']);
        $this->assertSame('none', $info['nick_name']);
        $this->assertSame([], $info['meta_data']);
        $this->assertArrayNotHasKey('energy', $info);
        $this->assertArrayNotHasKey('icon_no', $info);
    }

    public function test_パスワードが違うと_ログインに失敗する(): void
    {
        $this->register();
        $email = DB::table('accounts')->value('email');
        $wrong = (new \BitCraft\Core\Libraries\Stateless\Static\StlStaUtilCompress())::comp("$email:wrong-password");

        $this->resetStatics();
        $this->withoutExceptionHandling();
        try {
            $this->postJson('account/login', [], ['Primary-Code' => $wrong]);
            $this->fail('例外にならなかった');
        } catch (ExceptApp $e) {
            $this->assertSame(21200001, $e->getCode());
        }
        $this->assertSame(0, DB::table('personal_access_tokens')->count());
    }

    public function test_トークンが無いと_ユーザの情報は取得できない(): void
    {
        $this->resetStatics();
        $this->getJson('user/info')->assertUnauthorized();
    }

    public function test_案件は_registerUser_を差し替えて_ユーザの項目を足せる(): void
    {
        $token = $this->login($this->register('custom/register'));

        $info = $this->userInfo($token);
        $this->assertSame('custom', $info['nick_name']);
        $this->assertSame(['plan' => 'demo'], $info['meta_data']);
    }
}
