---
name: issue-to-pr
description: GitHub の issue の URL や番号を渡されたとき、issue を読み、実装して PR を作る。
---

# issue-to-pr

issue を実装し、PR を作成する。

## 手順

1. 始める前に `output.md` を読む(コマンドの出力とファイルの読み込みを絞る決まり)。
2. issue を読み、内容・完了条件・検証手順・撤退基準を把握する。
3. `draft` ラベルが付いているときは、「draft のため着手しません」と伝えて止まる。
4. 不明点があれば、質問して回答を待つ(依頼を受けた Slack のスレッド、または会話)。
5. 作業ブランチを作る。
   - 名前は `feature/issue-<issue番号>`(例: `feature/issue-5`)。0 埋めしない。日付は付けない。
   - `develop` から作る。無いときは「Please create a “develop” branch」と表示して何もしない。
   - 同じ名前のブランチが既にあれば、それを使う。
6. 実装する。コミットは `.claude/rules/git.md` の書式に従う。`/git commit` と `/git push` の確認は省く。
7. テストを実行して、動作を確認する。
8. `pr-body.md` を読み、その形で PR を作る。向き先は `develop`。`/git pr-create` の確認は省く。マージはしない。
9. 変更内容と確認結果を、依頼を受けた場所(Slack のスレッド、または会話)に報告する。

## 仕様

- issue の受け入れ条件を満たしていること
- 既存のテストが壊れていないこと

## 禁止事項

- `main` ブランチへ直接 push しない
- issue に書かれていない範囲の変更をしない
