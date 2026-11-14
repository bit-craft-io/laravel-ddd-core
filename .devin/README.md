# Devin のルール(このリポジトリ用)

> **このファイルは、Devin は読まない。人と AI が、設定の経緯と手順を確かめるための記録。**
> 消しても、`rules/` などの動作は変わらない(Devin に確認した回答。公式のドキュメントでは未確認)。

このディレクトリは、Devin が、このリポジトリ(laravel-ddd-core)のセッションで使うルールなどを置く。
issue #9「Devin のルールを、プロジェクトごとに使い分ける」の実装。

## 構成

### 手動で設定が要る

| 対象 | 設定する場所 | 内容 |
|---|---|---|
| `scripts/run-summary.sh` | Devin の画面(Settings > Environment > Blueprints) | blueprint の `maintenance` で、`/usr/local/bin/run-summary` に入れる。手順は下の「環境(blueprint)の設定」 |

### 手動で設定が要らない

リポジトリに置くだけで足りる(Devin が読むかは、下の「未確認」を参照)。

| 対象 | 役割 |
|---|---|
| `config.json` | このままでよい |
| `..devin-plugin/plugin.json` | このままでよい |
| `rules/rule-*.md` | ルール。1 ファイルが 1 ルール |
| `skills/issue-to-pr/SKILL.md` | スキル。issue を読み、実装して PR を作る |
| `README.md` | このファイル。Devin は読まない |

## PR で Devin を呼ぶ

Devin が作った PR(追跡中の PR)では、コメントの先頭に `@devin-ai-integration`、`/devin `、`Devin` のいずれかを書くと、Devin が応答する。

| 書き方 | 結果 |
|---|---|
| `@devin-ai-integration` | 応答する |
| `/devin ` | 応答する |
| `Devin` | 応答する |

注意:
- `@Devin`、`@devin`、`@devin-ai` は、GitHub の別の個人ユーザーに通知が行くので、PR に書かない。
- PR と関係のない質問は、Devin が答えない。Slack などの新しいセッションで聞く。
- Devin が追跡している PR のコメントは、メンションなしでも受信され、トークンを消費しうる。指摘は 1 回のレビューにまとめる。止めるときは、PR の「Disable automatic comment, CI, and merge conflict monitoring」にチェックを入れるか、PR が閉じたあとにセッションをアーカイブする。

## 環境(blueprint)の設定

`scripts/run-summary.sh` は、リポジトリに置くだけでは動かない。Devin の画面で、blueprint に設定して入れる。リポジトリのファイルからは見えないため、ここに残す。

- 設定する場所: Settings > Environment > Blueprints > laravel-ddd-core(リポジトリ単位)。一覧に無いときは、同じ画面でリポジトリを追加する(入力欄に入力して Add を押すと、一覧が出る)
- 書く内容:

```yaml
maintenance:
  - name: Install run-summary
    run: sudo install -m 755 .devin/scripts/run-summary.sh /usr/local/bin/run-summary
```

- 注意:
  - `maintenance:` の後ろに `|` を付けない(付けると、下の行が文字列になり、リストとして読まれない)
  - リポジトリの `.devin/scripts/run-summary.sh` を参照するため、`develop` に入っていないと、ビルドのこのステップが失敗する
  - `initialize` ではなく `maintenance` にしたのは、差分ビルドでも最新のスクリプトに入れ替わるため
- 反映: 保存するとビルドが始まる。Settings > Environment > Snapshots で Success になるまで待つ。Success の後に始めたセッションから有効(動作中のセッションには反映されない)
- スクリプトを直したとき: 次のビルド(約 24 時間ごとの自動ビルド、または Build snapshot)で反映される
- 確認: 新しいセッションで、次を実行する

```bash
ls -l /usr/local/bin/run-summary && run-summary test true
```

## 採用した方法(リポジトリ単位の `.devin/rules/*.md`)

Devin のルールを、組織(Organization)スコープの「Always on」ではなく、
リポジトリ直下の `.devin/rules/` に、1 ルール 1 ファイルで置く。

### 選んだ理由

1. リポジトリの中に置くため、構造上、このリポジトリのセッションにだけ対応する。別のプロジェクトのルールが混ざらない(issue #9 の目的)。
2. 既存の `.claude/rules/*.md`(Claude Code 用)と同じ構成で、見通しがよい。
3. サブ issue(#10〜#17)と 1 対 1 に対応し、追加・削除がしやすい。

### 他の候補を選ばなかった理由

- 組織ルールのトリガーを `glob`(例: `**/*.php`)にする案: ファイルの種類で絞るため、別の PHP プロジェクトにも一致してしまい、リポジトリ単位の分離にならない。
- `.devin/config.json` の `requiredPlugins` でプラグイン化する案: プラグインの管理が増え、1 リポジトリに閉じる用途には重い。
- リポジトリ直下の `AGENTS.md` 1 ファイル案: すべてのルールが 1 ファイルに混ざり、サブ issue との対応が崩れる。

## 置いたルール

| ファイル | 元 issue | 内容 |
|---|---|---|
| `rule-base.md` | #10 | 公式優先、未確認の扱い、秘密情報の扱い |
| `rule-branch-create.md` | #11 | 作業ブランチの名前と、作る元のブランチ |
| `rule-issue-start.md` | #12 | draft ラベルの issue には着手しない |
| `rule-pr-close.md` | #13 | PR が閉じたら、セッションをアーカイブする |
| `rule-pr-create.md` | #14 | PR を作成するときの、本文に入れる項目 |
| `rule-terraform.md` | #15 | Terraform の実行範囲(`plan` まで。課金が発生するリソースは作成しない) |
| `rule-token-output.md` | #16 | トークン削減(出力とファイル読み込みを絞る) |
| `rule-token-session.md` | #17 | トークン削減(セッションを issue の話題に保つ) |

## 置かなかったルール

なし(元の #10〜#17 は、すべて置いた)。

### 経緯

`rule-terraform.md`(#15)は、当初、置かなかった。このリポジトリは Laravel の Composer パッケージで、Terraform / インフラのコードを持たないため。
その後、グローバル(組織)のルールを削除して、リポジトリ単位に絞る方針にした。Terraform を扱う作業を、このリポジトリのセッションで頼む場合に備え、実行範囲を決めておくため、置くことにした。

## 補足(内容の調整)

- `rule-token-output.md` の実行例は、元 issue(#16)の `run-summary test go test ./...` を、この PHP パッケージに合わせて `run-summary test composer test` にした。ルールの中身は変えていない。

## 反映の記録

| 内容 | 反映した日 |
|---|---|
| blueprint の `maintenance`(`run-summary` の導入) | 2026-11-13 |
| README の「PR で Devin を呼ぶ」を追加(`@Devin` を、GitHub の別の個人ユーザーに当たらない書き方にした) | 2026-11-13 |

## 未確認(人が Devin の画面・セッションで確かめる)

次は、Devin の UI とセッションでしか確認できないため、このリポジトリからは確認できていない。

- リポジトリの `.devin/rules/*.md` と `skills/` が、Devin のクラウドのセッションで読まれるか(公式ドキュメントで確認できていない)。
- フロントマターの `trigger: always_on` と `description` のキー名・形式が、Devin の仕様に合っているか。
- Customize → ルールの「トリガー」の選択肢の表示名(`always_on` / `manual` / `model_decision` / `agent` / `glob` に対応する UI 表示)。
- 新しいセッション(別プロジェクト)を開始し、このリポジトリのルールが読み込まれないことを、Devin に聞いて確かめる。
- 既存の組織ルールのうち、プロジェクト固有の内容が、別プロジェクトのセッションにも適用されていないか。適用されている場合は、組織スコープから外し、各リポジトリの `.devin/rules/` へ移す(グローバルのルールとスキルは、削除する方針。削除後に、新しいセッションで確かめる)。
- blueprint の `knowledge:` の雛形(npm のコマンド)を、このリポジトリ(PHP)に合わせて直すか。
- blueprint の反映後、新しいセッションで `run-summary` が動くこと。