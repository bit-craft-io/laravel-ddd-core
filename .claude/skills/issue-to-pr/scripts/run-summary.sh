#!/usr/bin/env bash
# run-summary: コマンドを実行し、出力をファイルに保存して、結果の要約だけを表示する。
# 使い方: run-summary <名前> <コマンド> [引数...]
#   成功: OK と終了コード、ログの末尾 3 行
#   失敗: NG と終了コード、ログの末尾 30 行
# 終了コードは、実行したコマンドのものをそのまま返す。
set -uo pipefail

if [ "$#" -lt 2 ]; then
  echo "usage: run-summary <name> <command> [args...]" >&2
  exit 2
fi

name=$1
shift
case "$name" in
  *[!A-Za-z0-9_-]*) echo "run-summary: name must match [A-Za-z0-9_-]+" >&2; exit 2 ;;
esac

log="${RUN_SUMMARY_DIR:-/tmp}/run-summary-${name}.log"
"$@" >"$log" 2>&1
code=$?
lines=$(wc -l <"$log")

if [ "$code" -eq 0 ]; then
  echo "[$name] OK exit=0 log=$log lines=$lines"
  tail -n 3 "$log"
else
  echo "[$name] NG exit=$code log=$log lines=$lines"
  tail -n 30 "$log"
fi
exit "$code"
