#!/usr/bin/env bash
# post-tool-use.sh — record high-level evidence metadata (NO secrets, NO payloads). Best-effort, exit 0.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT" || exit 0
LOG="docs/evidence/step-3/codex/hook-activity.log"
mkdir -p "$(dirname "$LOG")" 2>/dev/null || exit 0
# Record only a coarse marker; never echo command content or secrets.
printf 'post-tool-use: recorded (branch=%s)\n' "$(git branch --show-current 2>/dev/null || echo '?')" >> "$LOG" 2>/dev/null || true
exit 0
