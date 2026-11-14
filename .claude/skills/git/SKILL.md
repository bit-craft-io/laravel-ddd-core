---
name: git
description: Git / GitHub の操作を行う。`/git issue-form` で issue 入力フォーム(Artifact)を開き、`/git issue-create` でチェック済みの内容から issue を作成する。`/git commit` でコミット、`/git push` でプッシュ、`/git pr-create` でプルリクエストを作成する。`/git pr-review` でプルリクエストをレビューし、指摘をコメントとして書き込む。
---

# git

引数で操作を選ぶ。対応しているのは `issue-form`、`issue-create`、`commit`、`push`、`pr-create`、`pr-review`。それ以外は未対応と伝えて終了する。

issue の書式やラベルなどの決まりは `.claude/rules/git.md` に従う。画像の置き場所(`issue-images` ブランチ)もそこに定めてある。

流れ: `/git issue-form` → フォームで入力して「チェック」(ページ内でAIが検証) → `/git issue-create`。

## `/git issue-form`

フォームは `issue-form.html`(固定)。

1. `.claude/skills/git/.artifact-url` を読む。
2. 無ければ初回なので公開する。`issue-form.html` を全文読んでから Artifact ツールで公開する。
   - `capabilities: {db: {}, sample: {}, assets: {}}`、`contract: "0.2.92"`、初回のみ `icon: "form"`
   - 返ってきた URL を `.claude/skills/git/.artifact-url` に保存する(git 管理外)。
3. あれば再公開せず、`Artifact` の `open` でフォームを表示し、URL を伝える。
4. 「入力して『チェック』を押し、OKになったら `/git issue-create` を実行してください」と案内して終了する。

`issue-form.html` を変更したときは、保存した URL を `url` に指定して、同じ URL に再公開する。

## `/git issue-create`

YAML の形式と検証ルールは `issue-schema.md`。

1. `ArtifactData` で `issue/input` を get し、`status`・`rev`・`yaml` だけを使う。
   - 無い → 「フォームでチェックを通してください」と伝えて終了する。
2. `issue/review` の `rev` が同じで `status` が `created` なら、作成済みと伝えて終了する(二重作成を防ぐ)。
3. 最終確認として、`issue-schema.md` の検証ルールで確認する。`status` が `checked` でも省略しない。`submitted`(AIチェック未実施)なら、ここが本検証になる。
   - 「問題」がある(作業を始められない不備) → `issue/review` に `needs-fix` を set し、フォームで直して再チェックするよう伝えて終了する。
   - 「気づき」だけ → 作成は止めない。次の手順で、ユーザーに内容を見せるときに伝える。
4. `.claude/rules/git.md` の「作成前の確認」に従い、`AskUserQuestion` で確認する。
   - 質問には、タイトルと本文の要点を書く。選択肢は「はい」/「修正する」。
   - 「はい」: 次の手順へ進む。
   - 「修正する」: issue は作らず、「フォームで直して、もう一度チェックしてください」と伝えて終了する。
   - 「Other」(自由入力): 誤字などの軽微な修正だけ、タイトルと本文に反映し、もう一度確認する。大きな変更は、「修正する」でフォームに戻るよう伝える。
5. GitHub MCP の `issue_write` で issue を作成する。リポジトリは `git remote get-url origin` から取る。
   - タイトルは `<key>: <kind>: <summary>` で組み立てる。`key` が `none` のときも、`none: <kind>: <summary>` とする(`.claude/rules/git.md` の「タイトル」)。
   - 本文は `.claude/rules/git.md` の「本文テンプレート」に従い、`what` / `done`(チェックボックス)/ `verify` / `abort` / `background` から組み立てる。書かれていない項目も見出しは残し、中身は「なし」にする。
   - `labels` があれば、`issue_write` の `labels` に渡す。
6. YAML に `images` があるときだけ、画像を `issue-images` ブランチに置き、本文に追記する。
   1. 画像を取り出す。`Artifact` の `read` に `path` として画像の `id` を渡し、ローカルに保存する(画像のデータを会話に流さない)。
   2. 形式と大きさを確かめる。`png / jpg / webp / svg` のみ、1枚 1MiB まで、5枚まで。外れたものは置かず、ユーザーに伝える。
   3. ファイル名を決める。`連番2桁-名前.拡張子`(例: `01-list.png`)。名前は元のファイル名から英数字・`_`・`-` だけ残す。残らなければ `image`。
   4. 作業ツリーを汚さないよう、専用の worktree で作業する。
      - `git fetch origin issue-images`
      - `git worktree add -B issue-images <作業用ディレクトリ> origin/issue-images`
      - `<作業用ディレクトリ>/<issue番号>/` に画像を置き、`git add` → `git commit`(メッセージは `Add images for issue #<番号>`)→ `git push origin issue-images`
      - `git rev-parse HEAD` でコミットIDを控え、`git worktree remove` で片付ける。
   5. `issue_write` の `update` で本文の末尾に、画像ごとに次を追記する。
      ```
      ## 画像
      ### <caption>
      ![<caption>](https://github.com/<owner>/<repo>/blob/<コミットID>/<issue番号>/<ファイル名>?raw=true)
      ```
   6. push に失敗したら、issue は画像なしで作成済みであることと、失敗の内容をユーザーに伝える。
7. `issue/review` に `created` と issue の URL を set する。ユーザーにも URL を伝える。画像を置いたときは、置き場所(ブランチとフォルダ)も伝える。

## `/git commit`

コミットの書式・言語・粒度は `.claude/rules/git.md` の「コミット」に従う。

1. `git status` と `git diff` で、変更を確認する。
2. 現在のブランチが `develop` か `main` なら、コミットせずに止まる。作業ブランチを作るよう伝える。
3. 関係のない変更が混ざっていたら、分けてコミットするか、ユーザーに確認する。
4. `git add` は、ファイルを指定して行う(`-A` は使わない)。`.env` のような秘密情報のファイルは、追加せずに止まる。
5. メッセージ案を作り、`AskUserQuestion` で確認する(選択肢は「はい」/「修正する」)。
6. `git commit` する。
7. push はしない(push は `/git push` で行う)。コミットIDとメッセージを伝える。

## `/git push`

push の決まりは `.claude/rules/git.md` の「プッシュ」に従う。

1. 現在のブランチが `develop` か `main` なら、push せずに止まる。
2. 未 push のコミットを確認する(`git log origin/<ブランチ>..HEAD`)。無ければ「push するものがありません」と伝えて終了する。
3. 未 push のコミットの一覧を見せ、`AskUserQuestion` で確認する(選択肢は「はい」/「やめる」)。
4. `git push -u origin <ブランチ>` を実行する。
   - 拒否された(リモートが先に進んでいる)ときは、強制せずに止まり、`git pull` が必要だと伝える。
   - ネットワークエラーのときだけ、2秒・4秒・8秒・16秒の間隔で、最大4回まで再試行する。
5. push の結果を伝える。PR は作らない(PR は `/git pr-create` で行う)。

## `/git pr-create`

PR の決まりは `.claude/rules/git.md` の「プルリクエスト」に従う。

1. 現在のブランチが `develop` か `main` なら、止まる。
2. 未 push のコミットがあれば、先に `/git push` を行うよう伝えて止まる。
3. `develop` との差分と、衝突の有無を確認する。衝突があれば、止まって伝える。
4. リポジトリに PR テンプレートがあれば、その見出しに合わせる。
5. タイトルと本文の案を作り、`AskUserQuestion` で確認する(選択肢は「はい」/「修正する」)。
6. GitHub MCP の `create_pull_request` で作成する。リポジトリは `git remote get-url origin` から取る。
7. PR の URL を伝える。マージはしない。

## `/git pr-review`

使い方: `/git pr-review <レベル> <対象> [--mention <文字列>]`(例: `/git pr-review medium origin/pull/48`、`/git pr-review medium 62 --mention @name`)。レベルは `code-review` と同じ(`low` / `medium` / `high` / `xhigh` / `max`)。`max` は、ユーザーが明示したときだけ使う。`--mention` は、総括の先頭に付ける宛先の文字列(省略したときは、何も付けない)。

レビューの観点は `code-review` に任せる。このスキルは、その指摘を、PR のコメントとして書き込む。書き込みは「コメントのみ」で、承認・変更の要求・マージはしない。

1. 引数から PR の番号を取る(`origin/pull/<番号>`、または番号だけ)。リポジトリは `git remote get-url origin` から取る。番号が取れなければ、止まって伝える。
2. GitHub MCP の `pull_request_read`(`get`)で、PR を確認する。閉じている、またはマージ済みなら、止まって伝える。
3. `code-review` を、レベルと対象を引数にして実行する。`--comment` と `--fix` は付けない。`code-review` が `ReportFindings` で報告した指摘を、次の手順で使う。使う項目は、`file`、`line`、`summary`、`failure_scenario`、`category`。
   - 指摘が0件なら、「指摘はありません」と伝えて終了する。
   - 指摘がある場合は、PR 本文の「検証」「完了条件」「逸脱」を読み、次の指摘を外す。
     - 外す: 検証の項目がその内容を実行して、結果が OK(期待した出力・終了コード 0)のもの。検証の状態として PR 本文にすでに書かれているもの(「未実行」「人の確認待ち」など)。「逸脱」にすでに書かれているもの。
     - 外さない: 検証の結果が NG のもの、出力が期待と食い違うもの。検証が確かめていない経路や、タイミング・セキュリティのように、検証が OK でも防げないもの。
     - 迷うときは外さない。
   - 外した結果が0件なら、「指摘はありません(検証で扱い済みのものを除く)」と、外した件数と要約を伝えて終了する。
4. `pull_request_read`(`get_files`)で、PR の差分を取り、各指摘の `file` と `line` が、差分の追加された行に含まれるかを調べる。
   - 含まれる → 行のインラインコメントにする。
   - 含まれない(差分の外、または行が特定できない) → レビューの総括コメントに回す。
5. `pull_request_read`(`get_review_comments`、`get_comments`)で、既存のコメントを読む。同じファイル・同じ行に、同じ内容のコメントがすでにあれば、その指摘は書かない。
6. 書き込む内容を組み立てる。
   - 行コメント: `[<category>] <summary>` に続けて、`failure_scenario` を書く。修正案があれば、`suggestion` で添える(値の確認が要るものは、本文で書く)。
   - 総括: 全体の一行(指摘の件数と、確認が要るものの有無)と、行に付けられなかった指摘の一覧。
   - 宛先(`--mention` が渡されたときだけ): 宛先は、メンションを含むコメントにだけ応答する場合がある。指摘を1回で渡すため、宛先の文字列は総括の1か所だけに付ける。
     - 総括の先頭の行を、渡された文字列と半角スペースで始める。続けて「次の指摘を、まとめて直してください。push は1回にまとめてください」と書く。
     - 総括には、行コメントにした指摘も含め、すべての指摘を `file:line` と要約の一覧にして書く(総括だけで作業を始められるように)。
     - 行コメントには、宛先の文字列を付けない(付けると、指摘ごとに宛先が起きる)。
     - `--mention` が渡されないときは、宛先の行も依頼の文も書かない。
   - すべてのコメントの末尾に、署名の行(Claude Code が書いたことを示す行)を付ける。
7. `AskUserQuestion` で確認する(選択肢は「はい」/「修正する」)。質問には、PR の番号と作成者、行コメントの件数と、それぞれの `file:line` と要約、総括の内容を書き、「コメントは、PR を見られる全員に見える」ことを添える。手順 3 で外した指摘があれば、その件数と要約、外した理由(どの検証の項目で扱い済みか)も書く(人が戻せるように)。
   - `--mention` が渡されたときは、「総括の先頭に <文字列> を付ける(宛先が起きて作業を始め、トークンを消費することがある)」ことも質問に書く。選択肢は「はい(宛先を付ける)」/「はい(宛先なし)」/「修正する」にする。「宛先なし」を選んだときは、総括から宛先の行と依頼の文を除く。
   - 「修正する」: 書き込まずに終了する。
   - 「Other」(自由入力): 誤字などの軽微な修正だけ反映して、もう一度確認する。
8. `pull_request_review_write`(`create`)で、保留のレビューを作る。行コメントを、`add_comment_to_pending_review` で1件ずつ足す。最後に、`pull_request_review_write`(`submit_pending`)で、総括を本文にして、「コメントのみ」(`COMMENT`)で送る。
   - 行コメントが拒否されたとき(行が差分の外など)は、その指摘を総括に移して、やり直す。
   - 途中で失敗したときは、止まり、保留のレビューが残っていないかを確認して、ユーザーに伝える。
9. レビューの URL と、行コメントの件数、総括に回した件数を、ユーザーに伝える。宛先を付けたかどうかも伝える。

## フォールバック

Artifact または DB が使えない場合は、`AskUserQuestion` で同じ項目を質問し、同じ検証ルールで確認してから作成する。
