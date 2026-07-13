#!/usr/bin/env bash
# test-guard.sh — positive/negative tests for the PreToolUse safety hook.
# A "block" case MUST exit 2; an "allow" case MUST exit 0.
set -uo pipefail
GUARD="$(dirname "$0")/guard-dangerous-commands.sh"
fail=0

run() { # $1=expected(block|allow) $2=command
  local expected="$1" cmd="$2" rc
  printf '{"tool_input":{"command":%s}}' "$(printf '%s' "$cmd" | sed 's/\\/\\\\/g; s/"/\\"/g; s/^/"/; s/$/"/')" \
    | "$GUARD" >/dev/null 2>&1
  rc=$?
  if [ "$expected" = "block" ] && [ "$rc" -ne 2 ]; then echo "FAIL(expected block): $cmd (rc=$rc)"; fail=1; fi
  if [ "$expected" = "allow" ] && [ "$rc" -ne 0 ]; then echo "FAIL(expected allow): $cmd (rc=$rc)"; fail=1; fi
}

# Negative (must block)
run block "git push --force origin main"
run block "git push --force-with-lease origin main"
run block "git push -f origin main"
run block "git push origin --delete refs/tags/x"
run block "git tag -f aish-agentic-ai-docs-foundation-v1.0.0-go"
run block "git tag -d v1.0.0"
run block "git reset --hard origin/main"
run block "cat .env"
run block "cat backend/.env.production"
run block "rm -rf /"

# Positive (must allow)
run allow "git status"
run allow "git push origin chore/aish-agentic-ai-documentation-foundation"
run allow "git tag -a aish-agentic-ai-docs-foundation-v1.0.0-go -m msg"
run allow "scripts/docs/validate.sh"
run allow "cat README.md"
run allow "rm -f /tmp/scratch.txt"

if [ "$fail" -eq 0 ]; then echo "PASS: all guard hook tests passed"; else echo "GUARD TESTS FAILED"; exit 1; fi
