#!/usr/bin/env bash
# check-agents.sh — validate the AGENTS.md instruction chain and Codex/Claude drift.
# Rule: .claude/rules/15, 20; AFR-069. Static, read-only.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0

# Required AGENTS.md chain (root + representative subdirectories).
AGENTS=(
  "AGENTS.md" "docs/AGENTS.md" "docs/architecture/AGENTS.md" "docs/security/AGENTS.md"
  "docs/ai/AGENTS.md" "docs/integrations/AGENTS.md" "docs/operations/AGENTS.md"
  "docs/quality/AGENTS.md" "scripts/AGENTS.md" "app/AGENTS.md" "app/Modules/AGENTS.md" "tests/AGENTS.md"
)
for f in "${AGENTS[@]}"; do
  if [ ! -s "$f" ]; then echo "FAIL: missing/empty AGENTS file: $f"; fail=1; continue; fi
  grep -Eiq 'NOT STARTED' "$f" || { echo "FAIL: $f missing truthful 'NOT STARTED' status marker"; fail=1; }
done
echo "OK: checked ${#AGENTS[@]} AGENTS.md files"

# Root AGENTS must point to canonical authority and active versions (no second source of truth).
grep -q "makemesick91-code/aish_agentic_ai" AGENTS.md || { echo "FAIL: root AGENTS.md missing canonical repo identity"; fail=1; }
grep -q "v2.4.0" AGENTS.md || { echo "FAIL: root AGENTS.md missing Master Source v2.4.0"; fail=1; }
grep -q "APPLICATION_FOUNDATION_RULES.md" AGENTS.md || { echo "FAIL: root AGENTS.md missing AFR pointer"; fail=1; }
grep -Eq 'CLAUDE.md|\.claude/rules' AGENTS.md || { echo "FAIL: root AGENTS.md missing Claude-rules sync pointer"; fail=1; }

# Drift: CLAUDE.md and AGENTS.md must reference the same active Master Source version.
ms_claude="$(grep -oE 'v2\.[0-9]+\.[0-9]+' CLAUDE.md | head -1 || true)"
if grep -q "$ms_claude" AGENTS.md; then
  echo "OK: AGENTS.md and CLAUDE.md reference $ms_claude (no drift)"
else
  echo "FAIL: AGENTS.md/CLAUDE.md Master Source version drift ($ms_claude)"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS: AGENTS.md chain valid; no Codex/Claude drift"; else echo "check-agents: FAILED"; exit 1; fi
