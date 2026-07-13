#!/usr/bin/env bash
# guard-dangerous-commands.sh — Claude Code PreToolUse(Bash) safety hook.
#
# Reads the hook JSON payload on stdin, extracts the Bash command, and BLOCKS
# (exit code 2) clearly destructive or secret-exposing commands. Prose rules are
# not enforcement; this hook makes the highest-risk denials real.
#
# Enforced by .claude/settings.json. Validate with:
#   scripts/hooks/test-guard.sh   (positive + negative cases)
#
# Contract: exit 0 = allow; exit 2 = block with reason on stderr.
set -euo pipefail

payload="$(cat 2>/dev/null || true)"

# Extract the command field without requiring jq (fallback to grep/sed).
cmd=""
if command -v jq >/dev/null 2>&1; then
  cmd="$(printf '%s' "$payload" | jq -r '.tool_input.command // .command // empty' 2>/dev/null || true)"
fi
if [ -z "$cmd" ]; then
  cmd="$(printf '%s' "$payload" | sed -n 's/.*"command"[[:space:]]*:[[:space:]]*"\(.*\)".*/\1/p' | head -1 || true)"
fi
[ -z "$cmd" ] && exit 0

block() { echo "BLOCKED by guard-dangerous-commands.sh: $1" >&2; exit 2; }

# Destructive git / history / tag operations.
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+push[[:space:]]+.*(--force|-f)([[:space:]]|$)' && block "force-push is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+push[[:space:]]+.*--delete'                    && block "remote ref deletion is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+tag([[:space:]]|.*[[:space:]])(-f|--force)([[:space:]]|$)' && block "moving/overwriting a tag is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+tag([[:space:]]|.*[[:space:]])(-d|--delete)([[:space:]]|$)' && block "tag deletion is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+(reset[[:space:]]+--hard|filter-branch|filter-repo)' && block "history rewrite / hard reset is prohibited"

# Secret / dump exposure via shell.
printf '%s' "$cmd" | grep -Eq '(^|[[:space:]])(cat|less|more|head|tail|bat)[[:space:]]+[^|]*\.env([[:space:]]|$|\.)' && block "reading .env via shell is prohibited"
printf '%s' "$cmd" | grep -Eq '\.(pem|key|p12|pfx)([[:space:]]|$)' && block "reading private key material is prohibited"

# Reckless recursive delete of the whole tree / root.
printf '%s' "$cmd" | grep -Eq 'rm[[:space:]]+-[a-z]*r[a-z]*f?[[:space:]]+(/|~|\*|\.)([[:space:]]|$)' && block "recursive delete of / ~ . or * is prohibited"

exit 0
