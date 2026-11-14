---
description: 作業ブランチの名前と、作る元のブランチ
trigger: always_on
---

作業ブランチを作成するときは、次のルールに従う。

1. 名前は `feature/issue-<issue番号>`(例: `feature/issue-5`)。0 埋めしない。日付は付けない
2. `develop` から作る。無いときは「Please create a “develop” branch」と表示して何もしない
3. 同じ名前のブランチが既にあれば、それを使う
4. PR の向き先は `develop`
