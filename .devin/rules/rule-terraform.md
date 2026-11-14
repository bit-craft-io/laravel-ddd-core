---
description: Terraform の実行範囲と、課金を伴う操作の扱い
trigger: always_on
---

Terraform を扱うときは、次のルールに従う。

1. `terraform apply` と `terraform destroy` は実行しない。`terraform plan` までにする
2. 課金が発生するリソースは作成しない
