# CLAUDE.md

このファイルは、このリポジトリ(laravel-ddd-core)で作業する AI への指針を定める。

## このリポジトリの役割

- DDD の基盤クラス群(Base*、Middleware、Libraries)を提供する Composer パッケージ。
- Laravel 前提のパッケージとする(Eloquent、Queue、Http に依存する)。PHP 単体では動かない。
- 参考実装は `laravel-ddd-template`、利用側は各プロジェクトに置く。このリポジトリには、それらを置かない。

## 対応バージョン

- PHP は 8.5.12 以上(必須)。
  - 根拠: GHSA-w8hw-w9fx-j79w(CVE-2026-109618)、GHSA-6wmf-4rv5-p3rp(CVE-2026-109619)
  - php.net の ChangeLog と NVD で確認した: 2件の GHSA は、CVE-2026-109618(CVSS 9.8 CRITICAL)と CVE-2026-109619(CVSS 8.1 HIGH)に対応し、8.5.12 で修正された(影響範囲は、8.5 系が 8.5.12 未満。8.4 系は 8.4.27 未満、8.3 系は 8.3.36 未満、8.2 系は 8.2.35 未満)。
  - PHP 8.5 系を使う方針は、依頼者の指定による。
- Laravel は 13 系(`^13.0`)。
- 公式で確認できない仕様は断定せず、「未確認」と書いて報告する。

## 名前空間

- ルートの名前空間は `BitCraft\Core`(PSR-4 で `src/` に対応させる)。
- 例: `BitCraft\Core\Domains\ValueObject\BaseVo`(`src/Domains/ValueObject/BaseVo.php`)。
- 利用側の名前空間(`App\` など)は、「依存の向き」に書いた例外を除いて、core の中で参照しない。

## 依存の向き(最重要)

- 依存は、「利用側 → core」の一方向を基本とする。core は利用側の名前空間を参照しない。
  ただし、「ルール」で定めた次の参照は、意図した例外とする: `BaseMstVo` の `App\DataSources\DsHub`、`JobAccessInfo` の `App\Models\LogAccessInfo`。
- 利用側との結びつきは、登録ではなく「ルール」(命名規約)で行う(下の「ルール」)。
- 型で受け渡せるもの(例: 例外コードの enum)は、core に interface を置き、利用側が実装する。
- 次のクラスは core に置かない。
  - 特定のゲームやドメインの知識を持つクラス(アイテム、通貨、エネルギーなど)
  - 特定のインフラ(pod 転送、gRPC、protobuf)に依存するクラス
- `google/protobuf` を、core の require に入れない。

### Go サーバーとの protobuf 通信(core から除外する)

`MdlForwardPod` は、Go サーバーと protobuf で通信するために作ったもので、DDD 基盤の責務ではない。core に含めない。
次の関連箇所も、core に含めない。

| 対象 | 内容 |
|---|---|
| `Http/Middlewares/MdlForwardPod` | 本体 |
| `Exceptions/Enum/TypeExcept` の `DevelopNoneProtoBuf`(99011)、`DevelopFailedForwardGameServer`(99012) | 転送専用の例外コード。core の列挙から外す |
| 設定 `develop.api_endpoint` と `IStlStaConfigDevelop::$api_endpoint` | 転送先の設定 |
| `Interface/IStlStaConfigGameServer` | 空で未使用 |
| `google/protobuf`、`autoload.psr-4` の `Proto\` | protobuf の依存と生成コード |

転送が必要な案件は、利用側に置く。core の基底クラスや Trait(`TraitCore` など)は、利用側から使ってよい(依存の向きは守られる)。

## core と利用側の分担(決定)

- core は、各層の基底クラスと汎用部品だけを持つ。
- 案件固有のもの(DS、モデル、ドメイン、例外コードなど)は、利用側の**同名の層**に置く。
- ディレクトリ構成は、層ごとに core と利用側で同名にそろえる。

| 層 | core(`BitCraft\Core\`) | 利用側(`App\` など) |
|---|---|---|
| DataSources | `DataSources/`(`BaseDs`、`BaseMstDs`) | `DataSources/`(各 `Ds*`、`DsHub`) |
| Domains | `Domains/`(`Entity`、`Repository`、`ValueObject`、`ValueProxy` の `Base*`) | `Domains/`(各 `Ent*`、`Rep*`、`Vo*`、`Vp*`) |
| Exceptions | `Exceptions/`(`ExceptApp`、`ExceptModel`、`Enum/` の汎用コード) | `Exceptions/Enum/`(案件固有の例外コード) |
| Http | `Http/`(`Middlewares`、`Applications`、`Controllers`、`Requests`、`Responses` の共通処理。`Services/` の `BaseSvc` は未作成) | `Http/`(各ユースケース、コントローラ、リクエスト、レスポンス、`Services/` の各 `Svc*`。Svc はドメインに属さない、業務単位) |
| Libraries | `Libraries/`(`Stateful`、`Stateless`、`Traits`) | `Libraries/`(案件固有のライブラリを作成・配置してよい。core のものも使ってよい) |
| Models | `Models/`(`Account`、`UUser` のみ。アカウントとユーザ) | `Models/`(`Enum/` を含む) |
| Providers | 置かない | `Providers/` |

- 例外として、**アカウントとユーザ**(認証の主体とプロフィール)は、どの案件でも必要なため、core が持つ(下の「アカウントとユーザ」)。

- 例外コードのように、core が具体的な値を持っていたもの(`TypeExcept`)は、この分担に合わせて、core が管理するものだけを core に残す。
  利用側の enum は、`ITypeExcept` を実装して、core の例外クラス(`ExceptApp`、`ExceptModel`)に渡す。

## 依存の受け渡し

- コンストラクタを使わない(注入に限らない)。クラスはコンテナ(`StfStaFactory`)で生成し、初期化は `init()` で行う(例: `BaseVo::init()`、`BaseVp::init()`、`BaseEnt::init()`、`StfInsIterator::init()`、`ExceptApp::init()`、`ExceptModel::init()`、`BaseDs::_model()`)。
  - 例外(Laravel の仕組み上、または値の入れ物として必要なもの): `JobAccessInfo`(キューのジョブ)、`CtxAccessInfo`(値の入れ物)。
- 依存は、Trait のプロパティに静的クラス名を持たせて参照する(例: `$this->_DataSource::make(...)`)。
- コンテナへの登録は、`StfStaFactory`(`prototype()` / `singleton()`)だけが行う。ServiceProvider では登録しない(下の「ルール」R4)。

## 既存リスク(調査予定)

- `StfStaFactory::prototype()` / `singleton()` は、「登録は `StfStaFactory` だけが行う」ことを前提にしている(意図した設計。R4)。
  他の経路でコンテナに登録済みのクラスを渡すと、静的配列が空のまま null を返し、TypeError になる。
  擬似コンテナでの再現であり、本物の Laravel のコンテナ(`has()` の意味など)での挙動は未確認。後で調査する。

- `BaseDs` は、現在時刻(`_now()`)をインスタンスに保持し、`StfStaDataSource` はそのインスタンスを静的に保持する。長寿命のプロセス(Octane、キューワーカー、テスト)では、有効期間の判定が古い時刻になる(実行で確認。時刻を進めても `_now()` が変わらなかった)。PHP-FPM はリクエストごとに状態が破棄されるため、影響しない見込み(未確認)。後で対応する。

## 例外コードの体系(決定)

- コードは数値(int)とする。`Exception::$code` に `init()` で代入する現行の処理を変えない。
- 形式は8桁 `TT DDD NNN`。
  - `TT`: 種別。10〜99(先頭に 0 を使わない)
  - `DDD`: 領域。100〜999(先頭に 0 を使わない)。**種別(`TT`)ごとに** 100〜999 を持つ。同じ `DDD` でも、`TT` が違えば、別の領域
  - `NNN`: 連番。001〜999
  - 例(形式の説明): `TT=20`、`DDD=400`、`NNN=004` のコードは `20400004`。
- 領域を3桁にしたのは、利用側が、自分の領域の範囲の内側で、固有のルール(機能の付け方)を持てるようにするため。
  core は、`TT` の所有者、利用側 enum の `(TT, DDD)` の整合、コードの重複などを検査する(下の「検査」)。領域の内側の付け方は、検査しない。
- 階層は、`TT`(種別)→ `DDD`(領域)→ `NNN`(連番)。`TT` ごとに `DDD` があり、`DDD` ごとに `NNN` がある。
- `TT` の値: モデル10〜19、アプリ20〜29、開発90〜99、予約30〜89。使用中の `TT`: 10(モデル、core 管理)、20(アプリ、利用側が管理)、21(アプリ基盤、core 管理)、90(開発、core 管理)。
- 所有:
  - `TT` は、core が持つ(利用側は定義しない)。
  - `DDD` は、core と利用側の両方が持つ。所有者は `TT` ごとに決め、その `TT` の `DDD` と `NNN` は、所有者がすべて管理する(1つの `TT` の中で、範囲を core と利用側で分けない)。
  - `TT=20`(アプリ)は、利用側が管理する。core のコードは置かない。
  - `NNN` は、`DDD` を持つ側が、その `DDD` の中で採番する。
- 既存の5桁のコードからの移行: 種別の旧1桁を十の位へ、領域の旧1桁を百の位へ引き継ぐ(例: `24004` は `20400004`)。
  - core の `TypeExcept` は移行済み(`ModelDataNotFound=10100001`、`AppNotOkStatus=21100001`、`AppAccountLoginFailed=21200001`、`DevelopGeneralError=90900001`)。`TT=21` の領域: `DDD=100` 共通、`DDD=200` アカウント。`AppNotOkStatus` は、`TT=20` が利用側管理のため、core が管理する `TT=21`(アプリ基盤)に置く。
  - 利用側の8件(ユーザー1、アイテム2、ガチャ5)は、core から外した。利用側の enum への移行は、利用側の作業。

### 検査(`StlStaExceptCode`、`bin/except-code-check`)

- 利用側の enum は、`App\Exceptions\Enum\TypeExcept{領域}`(ファイル名 `TypeExcept*.php`)に置く。1つの enum は、1つの `(TT, DDD)` だけを持つ(強制)。core の `TypeExcept` は、core が管理する複数の `TT` を持つため、この規則の対象外。
- V1: 8桁(`DDD` は 100〜999、`NNN` は 001〜999)。V2: `TT` が定義済みの範囲にあり、割当済み。V3: 利用側の enum が1つの `(TT, DDD)`。V4: `TT` の所有者と enum の所有者が一致。V5: enum をまたいで重複なし。V6: 全ケースにメッセージあり。V7: ケース名が `{種別}{領域}{内容}` の形(種別は `TT` から、領域は enum 名の `TypeExcept` に続く部分から決まる。内容は大文字で始まる)。enum 名は `TypeExcept{領域}`(core の `TypeExcept` を除く)。
- コマンド: `php vendor/bin/except-code-check app/Exceptions/Enum 'App\Exceptions\Enum'`。core の enum は、常に検査する。違反があれば終了コード 1、引数の誤りは 2。
- GNU Make からの呼び出しは、利用側(template)に置く。例: ターゲット `except-code-check` で、上のコマンドを実行する。
- 割当済みの `TT` を増やすときは、`StlStaExceptCode` の `TT_OWNER` を変更する(破壊的変更として扱う)。
- 動作は、擬似的な環境で確認した。Laravel アプリ上、`composer install` 後の実行は未確認。

## 命名

- 接頭辞を守る: Ent、Vo、Vp、Rep、Ds、Svc、Uc、App、Cnt、Req、Res、Mdl、Stl*、Stf*、Trait*。
- Hub(`{層}Hub`)は、その層のクラスを参照するために、クラス名を定数でまとめるクラス。新しいクラスを足したら、Hub に登録する。
  - 例: `RepHub::REP_USER`(`RepUser::class`)を、`$this->_Domain::rep(RepHub::REP_USER)` のように使う。
- クラスの先頭のコメントは、1行目に名前、空行、続けて短い説明、の形にする(`@method` や `@property` は、その後ろ)。

  ```php
  /**
   * ユーザーモデル
   *
   * ユーザ情報を管理する。
   */
  ```
- 基底クラスは `Base` で始める。
- interface は `I` を接頭辞にする(例: `IStlStaConfigApp`、`ITypeExcept`)。trait は `Trait` を接頭辞にする(例: `TraitCore`)。
- 例外コードの enum は `TypeExcept` を接頭辞にする(core の `TypeExcept`、利用側の `TypeExceptGacha` など)。
- 例外コードの enum のケース名は、`{種別}{領域}{内容}`(コードの `TT`、`DDD`、`NNN` に対応させる)。短縮しない。
  - 例: `AppGachaExecCountOver` は、種別 `App`(`TT=20`)、領域 `Gacha`(`DDD=400`)、内容 `ExecCountOver`(`NNN`)。
  - 種別は `Model`(`TT=10〜19`)、`App`(`TT=20〜29`)、`Develop`(`TT=90〜99`)。enum 名に種別は入らないため、ケース名に残す。
  - 領域が共通(core が管理する `TT`)のときは、領域を省略する(例: `ModelDataNotFound`、`AppNotOkStatus`、`DevelopGeneralError`)。

## ルール(利用側との約束)

このプロジェクトは、登録(ServiceProvider への bind など)ではなく、ルール(命名規約)で、core と利用側を結びつける。
ルールを変えるときは、破壊的変更として扱う。

| No | ルール | 内容 | 出所 |
|---|---|---|---|
| R1 | `VoM{名前}` と `DsHub` の定数 | マスタ用 ValueObject `VoM{名前}`(例: `VoMGachaDrawEntity`)は、`App\DataSources\DsHub` の定数 `DS_M_{名前}`(大文字・スネークケース。例: `DS_M_GACHA_DRAW_ENTITY`)に対応する | `BaseMstVo` |
| R2 | `Ds{名前}` と `{名前}` | `{名前空間}\DataSources\Ds{名前}` は、モデル `{名前空間}\Models\{名前}` を使う | `StfStaDataSource::make()` |
| R3 | `DsHub` への登録 | データソースのクラス名は、`App\DataSources\DsHub` の定数(`DS_M_*`、`DS_U_*` など)に登録する。R1・R3 で core が参照する Hub は、利用側の `DsHub` だけ(core 自身の `DsHub` は別) | `BaseMstVo` |
| R4 | コンテナへの登録 | コンテナへの登録は、`StfStaFactory` だけが行う。ServiceProvider では登録しない | `StfStaFactory` |
| R5 | 設定 `core` | core は、既定値を持たない。利用側の `config/core.php` に、core が使うキーを書く(必須)。キーが無いと、未定義になる。キーの一覧は、`IStlStaConfigCore` | `StlStaConfig::core()` |
| R6 | `bootstrap/app.php` の配線 | `bootstrap/app.php` は利用側が持つ。core は `StlStaBootstrap` を提供し、利用側が呼ぶ。別名は `...StlStaBootstrap::aliases()` を展開して固有を足す(`Middleware::alias()` は置換のため、2回呼ばない)。固有の例外描画は、`StlStaBootstrap::exceptions()` より先に登録する(`Exceptions::render()` は登録順で、最初に null 以外を返したものが勝つ) | `StlStaBootstrap` |
| R7 | 認証のモデル | `config/auth.php` の `providers.users.model`(環境変数 `AUTH_MODEL`)に、`BitCraft\Core\Models\Account` を指定する | `Account` |
| R8 | core のマイグレーション | core の `database/migrations`(`accounts`、`u_users`)と、Sanctum の `personal_access_tokens` のマイグレーションを、利用側が実行する(`migrate --path`)。ServiceProvider では読み込まない | `Account`、`UUser` |
| R9 | route | アカウントとユーザの route は、利用側の `routes/api.php` に書く。core の `CntAccount`、`CntUser` を参照する | `CntAccount`、`CntUser` |

- 利用側が用意するもの: `DsHub`、ログ用モデル(`App\Models\LogAccessInfo`)、例外コードの enum(`TypeExcept{領域}`)。設定 `core`(`config/core.php`。R5)。一覧は README に書く。

## アカウントとユーザ(決定)

- アカウントとユーザは、別のドメインとして、core が持つ(draft の `Account`、`User` を、案件固有の項目を除いて移した)。

| ドメイン | core | 内容 |
|---|---|---|
| Account | `Models/Account`、`DataSources/DsAccount`、`Domains/Account/{EntAccount,RepAccount}`、`Http/Applications/UseCase/Account/*`、`Http/Applications/AppAccount`、`Http/Controllers/CntAccount`、`Http/Requests/Account/ReqAccountLogin`、`Http/Responses/Account/*` | 匿名の登録(`primary_code` を返す)、ログイン(`primary_code` から Sanctum のトークンを返す) |
| User | `Models/UUser`、`DataSources/DsUUser`、`Domains/User/{EntUser,RepUser}`、`Http/Applications/AppUser`、`Http/Controllers/CntUser` | プロフィール(`id`、`public_id`、`nick_name`、`meta_data`)と、`user/info` |

- `meta_data` は、クライアントが自由に保存する JSON。サーバは解釈しない(検証とサイズの上限は、未対応)。
- API: `POST account/register`(ヘッダ `Primary-Code` を返す)、`POST account/login`(ヘッダ `Primary-Code` を受け取り、`WWW-Authenticate: Bearer <token>` を返す)、`GET user/info`(`auth:sanctum`)。
- ログインに失敗したときは、`ExceptApp`(`TypeExcept::AppAccountLoginFailed` = `21200001`)にする(draft は、空のトークンを返していた)。
- メールアドレスのドメインは、設定 `core.account_mail_domain`。利用側の `config/core.php` に書く(R5)。
- **案件の項目を足す**(例: ゲームのエナジー)ときは、次のように、利用側で継承する。
  - core の `EntUser` を継承して、項目を足す(`use BitCraft\Core\Domains\User\EntUser as CoreEntUser;` `class EntUser extends CoreEntUser`)。`RepUser extends BitCraft\Core\Domains\User\RepUser` で、`ENT_CLASS` を差し替える。
  - `AppAccount extends BitCraft\Core\Http\Applications\AppAccount` で、`registerUser()` を差し替える。この `AppAccount` を使う `Cnt` は、利用側が持つ(core の `CntAccount` は、core の `AppAccount` を使うため)。テストの `tests/Fixtures/CustomAppAccount.php`、`CustomCntAccount.php` が例。
- 既存の DB(draft 由来)は、マイグレーションの名前が同じ(`0001_01_01_000000_create_accounts_table`、`2025_09_17_065307_create_u_users_table`)ため、再実行されない。`u_users` に、旧い列(`icon_id`、`energy*`)が残る場合は、`fresh` で作り直すか、列を削除する。

## バージョニング

- セマンティックバージョニングで、タグを打つ。最初は `0.x`。
- 次は破壊的変更として、メジャー(`0.x` の間はマイナー)を上げる。
  - 公開クラス・メソッドの名前や引数の変更
  - 上記「利用側との約束」の変更
  - 対応する PHP / Laravel の下限を上げること
- 変更は `CHANGELOG.md` に書く。

## テスト・開発

- テストは `orchestra/testbench` を使う(Laravel アプリなしで、パッケージ単体をテストするため)。
- コマンドは `composer.json` の `scripts` に定義してある。

| 用途 | コマンド | 内容 |
|---|---|---|
| テスト | `composer test` | `phpunit --bootstrap vendor/autoload.php tests` |
| 単一テスト | `composer test -- --filter=テスト名` | |
| 静的解析 | `composer analyse` | `phpstan analyse src tests --level=5` |
| 整形の確認 | `composer cs` | PSR-12。差分の表示のみ |
| 整形の適用 | `composer cs-fix` | |

- `tests/` に、テスト(PHPUnit + Testbench)を追加した。利用側を模した fixture は `tests/App/`(`App\`)、違反する enum は `tests/Fixtures/`(`Fixtures\`)にある。
  - **PHP 8.3.6 + Laravel 13.40 + Symfony 7.4 で、44件(122 assertions)が成功した(アカウントとユーザの API の6件を含む)。** PHP 8.5.12 + Symfony 8 での実行は未確認(PHP 8.3 は Symfony 8 を読み込めない)。PHP 8.5.12 の環境で実行して確かめ、この注記を直す。
  - 実際の Laravel で確認した内容: 設定の既定値と上書き、`Middleware::alias()` が置換であること、`Exceptions::render()` が登録順で先勝ちであること、マスタ Vo のルール R1〜R3(sqlite、キャッシュを含む)、`JobAccessInfo`。
- `composer analyse`(PHPStan level 5)は、PHP 8.3 で実行できた(18件の指摘。`StlStaDate` の `LARAVEL_START` の未定義、`JobAccessInfo` の `App\Models\LogAccessInfo` の未定義などを含む。未対応)。
- `composer.lock` はコミットしない(ライブラリのため)。
- 利用側から使うときは、Composer の path repository(`symlink: true`)で参照する。
  本番・CI は、タグを指定した vcs に切り替える。

## 未決事項

- 例外コードを DB で管理する案は、見送りを提案中(enum を正とする。未回答)。
- 例外コードの検査(`StlStaExceptCode::check()`)の、PHPUnit からの呼び出し。core の動作確認の後に整備する。
- 設定 `develop`(core 内に読み手なし)と `IStlStaConfigDevelop` の扱い。開発補助クラスを core に含めるかの判断と連動する。
- 開発補助のクラスのうち、`MdlFakeNow` は core に含める(決定)。`StlStaDevelopTool`、`TraitDevelop` を含めるかは未決。

## 後で検討する課題(今は対応しない)

- `JobAccessInfo` が、利用側の `App\Models\LogAccessInfo`、接続 `log_db`、8個の列(`user_id`、`public_id`、`level`、`api`、`param`、`file`、`line`、`option_info`)を、直接参照している。
  現時点では、`log_db` は固定、名前空間は `App\` 固定(テンプレートを使い、core をルールどおりに配置する)のため、対応しない。これは、「利用側の名前空間を参照しない」原則の、意図した例外とする。
  core 単体でテストするときは、テスト側で同名の偽のモデルを用意する。
- 利用側の名前空間が `App\` 固定であること(上記と同じ前提)。
- ルール R1 に違反したとき(`DsHub` に対応する定数がないとき)のエラーメッセージ改善。期待する定数名を示す。

## 共通の方針

- 認証情報(API キー、パスワード、秘密鍵、`.env` の実値)を、リポジトリ、issue、PR、ログ、プロンプトに入れない。入れてしまったら、作業を止めて報告する。
- 課金が発生する API の呼び出しなど、確認が要る操作は、実行前に人に確認する。
- Git / GitHub の運用は `.claude/rules/git.md` に従う。操作は `/git` スキル(`.claude/skills/git/`)、issue の実装は `issue-to-pr` スキル(`.claude/skills/issue-to-pr/`)で行う。`issue-to-pr/output.md` の実行例は `composer test` を使う。
