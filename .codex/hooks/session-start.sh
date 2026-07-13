#!/usr/bin/env bash
# session-start.sh — report repository identity, branch, canonical versions, active GO tags, and app status.
# Deterministic, read-only, network-free, secret-safe. Exit 0 always (informational).
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT" || exit 0
echo "== Aish Agentic AI — Codex SessionStart =="
echo "repo(normalized): makemesick91-code/aish_agentic_ai"
echo "branch:  $(git branch --show-current 2>/dev/null || echo '?')"
echo "worktree: $ROOT"
echo "Master Source: $(grep -m1 -E '^\*\*Versi:\*\*' docs/canonical/MASTER_SOURCE.md 2>/dev/null | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1)"
echo "PRD: $(grep -m1 -E '^\*\*Versi:\*\*' docs/canonical/PRD.md 2>/dev/null | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1)"
echo "GO tags (immutable): docs-foundation-v1.0.0-go, step-2-persona-pilot-v1.0.0-go"
echo "Application implementation: NOT STARTED. Deployment/pilot/production: NOT STARTED."
exit 0
