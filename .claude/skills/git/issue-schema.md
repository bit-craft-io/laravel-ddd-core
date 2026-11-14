# issue YAML スキーマと検証ルール

フォーム(`issue-form.html`)が `issue/input` ドキュメントの `yaml` に保存する内容。

```yaml
key: "P1"
kind: "feat"
summary: "文字列"
labels:
  - "draft"
what: |
  複数行の文字列
done:
  - "文字列"
verify: |
  複数行の文字列
abort: |
  複数行の文字列
images:
  - id: "アーティファクトの画像ID(32桁の英数字)"
    name: "元のファイル名"
    type: "image/png"
    caption: "何を示す画像か"
background: |
  複数行の文字列
```

## 項目

| 項目 | 必須 | 内容 |
|---|---|---|
| `key` | 必須 | グループ分けのキー。`none`、または `P` と数字(例: `P1`)。フォームの初期値は `none`。`none` のときも、タイトルに `none` を付ける |
| `kind` | 必須 | 種別。`feat` / `fix` / `docs` / `refactor` / `chore` のいずれか(フォームの初期値は `feat`) |
| `summary` | 必須 | 要約。1行。issueのタイトルは `<key>: <kind>: <summary>` で組み立てる(`key` が `none` のときも、`none: <kind>: <summary>`) |
| `labels` | 任意 | ラベルのリスト。今は `draft`(下書き)のみ |
| `what` | 必須(`draft` のときは任意) | やりたいこと。何をどう変えたいか |
| `done` | 必須(`draft` のときは任意) | 完了条件。1つ以上の項目のリスト |
| `verify` | 任意 | 検証手順。完了を確かめる手順(コマンド・期待結果) |
| `abort` | 任意 | 撤退基準。動かないとき、止まって報告する条件 |
| `images` | 任意 | 添付画像のリスト。最大5枚。各画像に `caption` が必須 |
| `background` | 任意 | 背景。省略可 |

画像の制約: `type` は `image/png` / `image/jpeg` / `image/webp` / `image/svg+xml`、1枚 1MiB まで。

## `issue/input` の `status`

| status | 意味 |
|---|---|
| `checked` | フォームのAIチェックを通った |
| `submitted` | AIチェックが使えず、未チェックで保存した |

## 検証ルール

フォームのAIチェックと、`/git issue-create` の最終確認の両方で使う。フォームの `issue-form.html` 内の `RULES` と同じ内容を保つこと。

判断の基準: **そのissueを受け取った人が、聞き返さずに作業を始められるか。**

- 始められない不備だけを「問題」にする。issueは作らず `needs-fix` を返す。
- それ以外の気になる点は「気づき」にする。issueの作成は止めず、ユーザーに伝えるだけにする。
- 迷う場合は問題にしない(気づきにする)。

問題にするもの:

- `key`: `none` と `P` と数字(例: `P1`)以外(フォームで形を確認するので、通常は起きない)。内容(どのフェーズか)は確認しない。
- `kind`: 上の5つ以外(選択式なので、通常は起きない)。
- `summary`: 空。何をするかが分からない。「修正」「対応」「改善」だけ。
- `labels` に `draft` がある場合は、`summary` だけを確認する。`what` と `done` が空でも問題にしない。
- `what`: 空(`draft` を除く)。`summary` の繰り返しだけ。
- `done`: 項目が0(`draft` を除く)。文の途中で終わる項目。「いい感じにする」のように確認方法が想像できない項目。
- `images`: `caption` が空。「画像」「スクリーンショット」だけの説明。
- `verify` / `abort` / `background`: `what` や `done` と明らかに矛盾する。

問題にしないもの(気づきにしてよい):

- 「など」「または」の使用。
- 何かを決めること自体が作業に含まれる書き方。
- 「関係者」のように、人や場所を特定していない表現。
- 参考画像の出典や、採用するかどうか。

フォームのAIチェックは、画像そのものを見ず、説明文だけで判断する。

## `issue/review` の形式

`needs-fix`(直してほしい点あり):

```json
{"rev": 1730000000000, "status": "needs-fix",
 "problems": [{"field": "done", "message": "「いい感じにする」は確認できません。確認できる形にしてください"}]}
```

`created`(issue作成済み):

```json
{"rev": 1730000000000, "status": "created", "url": "https://github.com/OWNER/REPO/issues/1"}
```

- `rev` は `issue/input` の `rev` をそのまま入れる(フォームが古い指摘を表示しないため)。
- `problems[].field` は `summary` / `what` / `done` / `verify` / `abort` / `images` / `background` / `general` のいずれか。
- `message` は、何が不明確か、どう直すかを1文で書く。
