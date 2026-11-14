# コーディング規約

このファイルは、core と利用側のコードの、配置・名前・役割を定める。

- 設計の根幹(依存の向き、R1〜R9、例外コードの体系、コンストラクタを使わないこと)は、`CLAUDE.md` を正とする。ここには書かない。
- 接頭辞の一覧は、`CLAUDE.md` の「命名」を正とする。
- `※要判断` は、未確定。確定したら、印を外す。`※未確認` は、コードを読んでいない、または実行で確かめていない。

## 基本方針

- 凝集度を高く、結合度を低く。
- どのクラスに、何を記述するか、責任を明確に。

この 2 つは、次の形で守る。※要判断(守り方の案)

- 部品ごとに、責任(持つもの)と、持たないものを、書く。
- 呼んでよい先を、限定する。「呼び出しの向き」の表に書く。表にない呼び出しは、決めてから使う。
- 迷ったときの判別を、書く。

## 部品と役割

| 部品 | 接頭辞 | 層 | 役割 |
|---|---|---|---|
| Model | なし | インフラ | Eloquent モデル。`Enum/` に、モデルの型(タイプ)を置く。※範囲は未整理 |
| DataSource | `Ds` | インフラ | 1テーブルの検索と保存(クエリ) |
| Repository | `Rep` | ドメイン | Ds から取得して、Ent を作る。Ent を保存する |
| Entity | `Ent` | ドメイン | 1レコードを表す。変更は仮変更で、`commit` で反映する。そのレコードの業務ルールを持つ |
| ValueObject(マスタ) | `VoM` | ドメイン | 読み取り専用のマスタ。`DsHub` の `DS_M_*` に対応(R1) |
| ValueObject(値) | `Vo` | ドメイン | 同一性を持たない不変の値。※要判断(下の「Vo と Vp」) |
| ValueProxy | `Vp` | ドメイン | Ent を包む、変更できる値 |
| Service | `Svc` | Http | 一連の業務ルール。複数ドメインの Ent / Vo を、引数で受け取ってよい。ドメインに属さない |
| UseCase | `Uc` | Http | 値を受け取り、設定・ユーティリティ・外部ライブラリで、処理を実行する |
| Application | `App` | Http | Controller から呼ばれる入口。Rep で取得した Ent を Svc / Uc に渡し、保存する。トランザクションの開始と、レスポンスの値の設定も行う |
| Controller | `Cnt` | Http | 薄く保つ。`App*` を呼ぶ。※未確認 |
| Request | `Req` | Http | バリデーション定義。※未確認 |
| Response | `Res` | Http | API レスポンスの型。※未確認 |
| Middleware | `Mdl` | Http | トランザクションの `commit` / `rollback`、レスポンスの制御。※未確認 |
| Exception | `Except*` | 共通 | `ExceptApp`(422)、`ExceptModel`(404)。コードは、`ITypeExcept` を実装した enum |
| 静的クラス | `Stl*` `Stf*` | Libraries | 下の「Stl と Stf」 |
| Trait | `Trait*` | Libraries | 静的クラスへの入口。`use` して、静的クラスのメソッドを呼ぶ |
| Hub | `{層}Hub` | 各層 | クラス名の定数をまとめる |
| Job | `Job*` `Ctx*` | Jobs | 非同期処理(キュー)。※規則は未整理 |

## 呼び出しの向き

呼び出し元ごとに、呼んでよい先を、限定する(結合度を低く保つため)。`CLAUDE.md` の「依存の向き」(core と利用側の関係)とは、別の話。

| 呼び出し元 | 呼んでよい先 | 呼ばない先 |
|---|---|---|
| Cnt | App | ※未確認 |
| App | Rep、Svc、Uc、VoM の取得 | 他の App。Ds の直接呼び出し(※要確認) |
| Svc | 引数の Ent / Vo、VoM の取得 | Rep、他の Svc |
| Uc | Ds の取得のみ、設定・ユーティリティ・外部ライブラリ | Rep、他の Uc、Svc |
| Rep | Ds、Ent の作成 | Svc |
| Ent | Vp(内包) | Ds、Rep、Svc、VoM の取得 |
| Vp | 包んだ Ent(または配列) | ※未確認 |
| VoM | Ds の取得 | ※未確認 |
| Ds | Model | Ent、Vo を返さない |

- `Domains/` は、`Http/` を参照しない。依存は、`Http/Services → Domains` の一方向。
- 上の表は、これまでに決めた規則と、現行のコードから整理した。

未決の組み合わせ(※要判断): 決めたら、表に入れる。

- Svc → Uc、Svc → Ds
- Rep → Rep、Rep → Uc
- Ent → Ent、Ent → VoM(「取得しない」は合意済み。引数で受け取るのは可か)
- Cnt、Req、Res、Mdl の呼び出し(`Http/` のコードを読んでから)

## 層ごとの規則

### Ds

- `DataSources/` に置く。名前は `Ds{モデル名}`(R2)。`BaseDs`(マスタは `BaseMstDs`)を継承する。`DsHub` に登録する(R3)。
- `BaseDs` の `find` `findOrFail` `get` `create` `update` `createGetId` は `final`。上書きしない。
- 担当は、クエリ(検索と保存)だけ。業務ルールを書かない。
- Sanctum の `createToken` など、クエリでない処理を置かない(Model を要する外部ライブラリの呼び出しは、Uc)。
- 条件は引数で受け取る。モデルに scope を書かず、Ds の `where` で組み立てる。
- 有効期間は `enable()`、現在時刻は `_now()`(`CarbonImmutable`)。`Carbon` を使わない。
- 返すのは、`Model`、`Collection`、スカラ値。`Ent` や `Vo` を返さない。

### Ent

- `Domains/{ドメイン}/` に置く。名前は `Ent{名前}`。`BaseEnt` を継承する。`EntHub` に登録する。
- 値の取得は `__get`、変更は `__call`(仮変更)。`commit()` で Model に反映する。保存しない(保存は Rep)。
- 項目は、クラスの先頭コメントに、`@property-read`(取得)と `@method void 項目名(型 $value)`(変更)で書く。
- `initOnce()` は、クラス生成時に1回。`initAfter()` は、`init()` の後。空でも実装する(abstract のため)。
- そのレコードの業務ルールは、Ent のメソッドに書く。※実例なし
- Vp を持つときは、`initAfter()` で生成して、`init($this)` する。※生成の方法(`vp()` と `vo()`)が不揃い。検証待ち

### Rep

- `Domains/{ドメイン}/` に置く。名前は `Rep{名前}`。`BaseRep` を継承する。`RepHub` に登録する。
- Ent の作成と保存を行う。Ds は `$this->_DataSource::make(DsHub::DS_X)`、Ent は `$this->_Domain::ent(EntHub::ENT_X)->init($model)` で取得・作成する。
- `persist()` は、`commit()` → Ds で保存 → 採番した `id` を Ent に戻す、の順。
- 取得できないときは、空の Ent を返す(`isEmpty()` で判定する)。※Rep ごとに揃っているかは未確認
- Ds の `Model` を、Rep の外へ返さない。返すのは Ent。
- メソッド名が、`makeDraft()` と `draft($user_id)`、`find` で不揃い。※要判断

### Vo と Vp

- `VoM*` は、単体で存在する。Ent の中に持たない。id で取得する、読み取り専用の参照データ(厳密な値オブジェクトではない)。
- `Vo*`(値)は、同一性を持たない不変の値。単体で存在してよい。Ent の属性、Svc の引数と戻り値に使ってよい。取得はしない(取得するのは `VoM*`)。※要判断。template に実例なし
- `Vp*` は、Ent の中にだけ存在する。Ent が `init($this)` して生成し、`_vp_*` に保持する。Ent の外へ渡さない。※要判断

### VoM の取得

- Ent、Vo、Rep、Ds から、VoM を取得しない。
- 取得してよい層と回数は、次の原則を案とする。※要判断(`mstVo()` の共有の検証待ち)
  - 取得は、最初に必要になった層が、1回だけ行う。以降は、引数で渡す。
  - Svc の内部でだけ必要な従属マスタ(例: 抽選テーブル)は、Svc が取得してよい。

### Svc

- `Http/Services/` に置く(ドメインに属さない)。名前は `Svc{業務名}`(例: `SvcGacha`、`SvcLogin`)。`SvcHub` に登録する。取得は `$this->_Domain::svc(SvcHub::SVC_X)`(毎回、新しいインスタンス)。`BaseSvc` は未作成。※`_Domain` 経由の呼び名と、`SvcHub` の置き場所は、要判断
- 一連の業務ルール(判定、計算、抽選)を持つ。共通化のための部品ではない。1つの Ent / Vo で完結するルールは、その Ent / Vo のメソッドに書く。
- 参加するドメインは、引数の Ent / Vo で受け取る(型で明示する)。
- Svc は、Rep を使わない。Ent は、App が Rep で取得して Svc に渡し、Svc が変更した Ent は、App が必ず `persist` する(保存忘れに注意)。
- Svc は、渡された Ent を変更してよい。保存しない。変更する Ent は、PHPDoc に書く。結果は戻り値で返し、Ent は返さない(オブジェクトは参照で渡るため)。※戻り値は要確認
- 業務ルールを持たない手順は、Svc でなく Uc。
- 状態を持たない。
- 呼び出し元は、App のみ。App は、どの Svc も呼んでよい。Svc、Uc、Ent、Rep から Svc を呼ばない(Svc → Svc の禁止)。
- 依存の向きは、`Http/Services → Domains` の一方向。`Domains/` は、`Http/` を参照しない。

### App

- App は、他の App を呼ばない(スパゲッティの防止)。
- App は、Ds を直接呼ばない。Ds を要する処理は、Uc。※要確認

### Uc

- `Http/Applications/UseCase/{ドメイン}/` に置く。名前は `Uc{内容}`。`UcHub` に登録する。
- `final` クラスで、公開メソッドは `execute()` の1つ。状態を持たない。
- 取得は、`$this->_UseCase::make(UcHub::UC_X)`。呼ぶのは `App*` だけ。

規則:

- Uc は、値を受け取り、設定・ユーティリティ・外部ライブラリ(Sanctum、Hash など)を使って、処理を実行する。
- Uc は、共通化のための部品ではない。App に収まらない処理(Ds を要する処理を含む)を受け持つ。※要確認
- Uc は、結果があれば、戻り値で返す。
- Uc は、業務ルール(→ Ent / Svc)を持たない。
- Uc は、Ent の取得・保存(→ Rep)を持たない。Rep を使わない。
- Uc は、必要な Ent / Vo を、引数で受け取る(App が Rep で取得して渡す)。
- Uc は、Ent 単位で扱えない処理(例: 集計、一括処理、Model を要する外部ライブラリの呼び出し)のために、Ds の取得(DB を変更しないメソッド)のみ呼んでよい。
- Uc は、Ds から得た Model を、直接変更・保存しない。
- Uc は、他の Uc を呼ばない。
- Uc は、レスポンス(`_ResponseParam`)を組み立てない。値を返すだけ。レスポンスは `App*` の責務。

迷ったとき:

1. 業務ルールか → Ent / Svc
2. Ent の取得か保存か → Rep(App が呼ぶ)
3. 上のどちらでもなく、App から切り出したい1ステップ → Uc

### Hub

- `{層}Hub` は、`final` クラスで、クラス名を定数で持つ(`const string X = X::class;`)。新しいクラスを足したら、登録する。
- 参照は、`$this->_Domain::rep(RepHub::REP_USER)` のように、Trait 経由で行う。`new` しない。
- core の Hub と、利用側の Hub は別。R1・R3 が参照するのは、利用側の `DsHub`。
- 粒度が不揃い(Rep、Ent、Svc は、層で1つ。Vo、Vp は、ドメインごと)。※要判断

### Stl と Stf

- 接頭辞は `{Stl|Stf}{Sta|Ins}{名前}`。`Stl` は、状態を持たない。`Stf` は、状態を持つ。`Sta` は、静的クラス。`Ins` は、インスタンス(例: `StlStaConfig`、`StfStaFactory`、`StfInsIterator`)。
- 選ぶ基準は、状態を持つか否かだけ。
- 使うときは、`new` や直接の静的呼び出しをせず、`Trait*` の `_` プロパティ経由(例: `$this->_Config::core()`)。

### Libraries の Enum と Interface

※要判断(案)

- Enum は、値の集合が固定で、ドメイン知識を持たないときに作る。名前は `Type{名前}`(例: `TypeLogText`、`TypeSizeUnit`)。ドメインの Enum は、利用側の `Models/Enum/` に置く。
- Interface は、次のどちらかのときに作る。名前は `I{名前}`。
  - 利用側に実装させたいとき(例: `ITypeExcept`)。
  - 設定の形を、型で示したいとき(例: `IStlStaConfigCore`。`@property-read` で補完を効かせる)。

## 未決事項

- Svc を `Http/Services/` に移す作業: `SvcGacha` と `SvcHub` の移動、`_Domain::svc()` の呼び名、README の構成図(`Domains/(Rep, Ent, Vo, Vp, Svc)`)。core の `StlStaDomain::svc()` を変えるなら、破壊的変更として扱う。
- `mstVo()` と `vp()` が singleton で共有される件の検証。結果で、「VoM の取得」と「Vo と Vp」を確定する。
- Rep と Ent の作法(メソッド名、`commit` の責務)。`RepGacha` `EntGacha` `EntItem` `RepItem` を読んで決める。
- Hub の粒度の統一。
- `BaseSvc` の作成。Trait の選定基準と、`_` プロパティの早見表。
- 接頭辞の揺れ(`Type`(enum)が、CLAUDE.md の一覧に無い。`DS_ACCOUNT` と `DS_U_USER`。CLAUDE.md の `Stf*` の定義と `StfInsIterator`)。
- `Cnt` `Req` `Res` `Mdl` の規則(`Http/` のコードを読んで書く)。
- Job の規則(実例が増えてから)。
- template の `EntItem` `EntUser` `EntProperty` が参照する `VpItem` `VpEnergy` `VpCredit` が、見当たらない件。サンプルの線引き。
- 課題: Svc(と App)が、`Models/Enum`(タイプ)を import してよいか。現状は、Domains と Http の両方が import している。結合度(型とケース名への依存)と、網羅の静的検査のトレードオフ。
  - 具体例: template の `SvcGacha` の `match(true)` は、`isX()` で判定するため、`TypeDraw` に依存しない。ただし、`default` が無く、PHPStan の警告が 1 件ある。選択肢は、そのまま / `default` で例外(`TypeExceptGacha` に1件追加)/ enum を直接 `match`(結合が増える)。
