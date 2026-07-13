#!/usr/bin/env bash
# test-hooks.sh — positive/negative tests for Codex hooks.
# PreToolUse block cases MUST exit 2; allow cases MUST exit 0. Informational hooks MUST exit 0.
set -uo pipefail
DIR="$(cd "$(dirname "$0")" && pwd)"
fail=0

emit() { printf '{"tool_input":{"command":%s}}' "$(printf '%s' "$1" | sed 's/\\/\\\\/g; s/"/\\"/g; s/^/"/; s/$/"/')"; }

run_pre() { # $1=expected(block|allow) $2=command
  local expected="$1" cmd="$2" rc
  emit "$cmd" | "$DIR/pre-tool-use.sh" >/dev/null 2>&1; rc=$?
  if [ "$expected" = "block" ] && [ "$rc" -ne 2 ]; then echo "FAIL(expect block): $cmd (rc=$rc)"; fail=1; fi
  if [ "$expected" = "allow" ] && [ "$rc" -ne 0 ]; then echo "FAIL(expect allow): $cmd (rc=$rc)"; fail=1; fi
}

# PreToolUse — negative (block)
run_pre block "git push --force origin main"
run_pre block "git push -f origin main"
run_pre block "git tag -f aish-agentic-ai-docs-foundation-v1.0.0-go"
run_pre block "git tag -d v1.0.0"
run_pre block "cat .env"
# PreToolUse — positive (allow)
run_pre allow "git status"
run_pre allow "git push -u origin docs/step-3-application-architecture-adr-foundation"
run_pre allow "scripts/docs/validate.sh"

# Informational hooks MUST exit 0
for h in session-start pre-compact stop post-tool-use; do
  printf '{}' | "$DIR/$h.sh" >/dev/null 2>&1 || { echo "FAIL: $h.sh exited non-zero"; fail=1; }
done

if [ "$fail" -eq 0 ]; then echo "PASS: all Codex hook tests passed"; else echo "CODEX HOOK TESTS FAILED"; exit 1; fi
