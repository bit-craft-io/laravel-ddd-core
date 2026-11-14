# laravel-ddd-core

（作業中）DDD の基盤クラス群(Base*、Middleware、Libraries)を提供する、Laravel 向けの Composer パッケージ。

参考実装は [laravel-ddd-template](https://github.com/bit-craft-io/laravel-ddd-template)。このリポジトリには、基盤クラスだけを置く。

## 要件

- PHP 8.5.12 以上
- Laravel 13 系(`^13.0`)、Laravel Sanctum 4 系

## 導入

タグを打つまでは、ブランチを指定する。

```bash
composer config repositories.laravel-ddd-core vcs https://github.com/bit-craft-io/laravel-ddd-core
composer require bit-craft-io/laravel-ddd-core:dev-develop
```

名前空間は `BitCraft\Core\`(`src/` に対応)。

## 構成

| 層 | 内容 |
|---|---|
| `DataSources/` | 1 テーブルの検索と保存の基底(`BaseDs`、`BaseMstDs`) |
| `Domains/` | `Entity`、`Repository`、`ValueObject`、`ValueProxy` の基底 |
| `Exceptions/` | `ExceptApp`、`ExceptModel`、汎用の例外コード |
| `Http/` | Middleware、Application、Controller、Request、Response の共通処理 |
| `Libraries/` | 静的クラス(`Stateful`、`Stateless`)と Trait |
| `Models/` | アカウントとユーザ(`Account`、`UUser`) |

案件固有のもの(DS、モデル、ドメイン、例外コードなど)は、利用側の同名の層に置く。

## 利用側(core を使うアプリ)がやること

core は、`ServiceProvider` への登録ではなく、「名前のそろえ方」で、利用側とつながる。利用側は、次の 3 つを守る。

### 1. 名前をそろえる

| 対象 | 決まり | 例 |
|---|---|---|
| マスタ用の値(`VoM`)と `DsHub` | `VoM{名前}` に対して、`App\DataSources\DsHub` に `DS_M_{名前}`(大文字のスネークケース)を作る | `VoMGachaDrawEntity` と `DS_M_GACHA_DRAW_ENTITY` |
| データソース(`Ds`)とモデル | `App\DataSources\Ds{名前}` は、`App\Models\{名前}` を使う | `DsMGacha` と `MGacha` |
| `DsHub` への登録 | データソースのクラス名を、`App\DataSources\DsHub` の定数にする | `const string DS_M_GACHA = DsMGacha::class;` |

### 2. 設定を書く

| 対象 | 内容 |
|---|---|
| `config/core.php` | core は、設定の既定値を持たない。`cache_enable`、`cache_default_ttl_sec`、`account_mail_domain` を、利用側が書く(必須) |
| `config/auth.php` | `providers.users.model` に `BitCraft\Core\Models\Account` を指定する |

### 3. つなぐ

| 対象 | 内容 |
|---|---|
| `bootstrap/app.php` | core の `StlStaBootstrap` を呼ぶ(ミドルウェアの別名と、例外の描画) |
| コンテナへの登録 | `StfStaFactory` だけが行う。`ServiceProvider` では登録しない |
| マイグレーション | core の `accounts`、`u_users` と、Sanctum の `personal_access_tokens` を、利用側が実行する |
| `routes/api.php` | アカウントとユーザの route を書き、core の `CntAccount`、`CntUser` を参照する |

そのほか、利用側が用意するもの: `App\Models\LogAccessInfo`(ログ用モデル)、例外コードの enum(`TypeExcept{領域}`)。

詳細は [CLAUDE.md](CLAUDE.md) を参照。

## 開発

```bash
composer test
composer analyse
composer cs
```

単一のテストは `composer test -- --filter=テスト名`。

## ライセンス

[MIT](LICENSE)
