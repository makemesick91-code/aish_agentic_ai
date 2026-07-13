#!/usr/bin/env bash
# stop.sh — end-of-turn checks: worktree state, uncommitted evidence, truthful final status. Read-only, exit 0.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT" || exit 0
echo "== Stop: session wrap-up checks =="
if [ -n "$(git status --porcelain 2>/dev/null)" ]; then
  echo "worktree: uncommitted changes present (commit evidence + update status docs before exit)"
else
  echo "worktree: clean"
fi
echo "reminder: run scripts/docs/validate.sh; record blockers truthfully; do NOT claim implementation/deployment."
echo "Application implementation: NOT STARTED."
exit 0
