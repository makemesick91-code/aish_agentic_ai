#!/usr/bin/env bash
# pre-compact.sh — remind to persist a concise checkpoint before context compaction. Read-only, exit 0.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT" || exit 0
echo "== PreCompact: update docs/status/CURRENT_STATE.md, HANDOFF.md, SESSION_CHECKPOINTS.md before compaction =="
echo "Do not rely on chat history alone for decisions (.claude/rules/14)."
exit 0
