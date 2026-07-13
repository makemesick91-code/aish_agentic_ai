#!/usr/bin/env bash
# pre-tool-use.sh — Codex PreToolUse guard. Blocks dangerous git/secret/destructive/production commands.
# Reads hook JSON on stdin, extracts the command, delegates to the shared guard for consistent enforcement.
# Contract: exit 0 = allow; exit 2 = block with reason on stderr. Secret-safe, network-free.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
GUARD="$ROOT/scripts/hooks/guard-dangerous-commands.sh"
payload="$(cat 2>/dev/null || true)"
if [ -x "$GUARD" ]; then
  printf '%s' "$payload" | "$GUARD"
  exit $?
fi
# Fallback minimal guard if shared script is unavailable.
cmd="$(printf '%s' "$payload" | sed -n 's/.*"command"[[:space:]]*:[[:space:]]*"\(.*\)".*/\1/p' | head -1 || true)"
[ -z "$cmd" ] && exit 0
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+push[[:space:]]+.*(--force|-f)([[:space:]]|$)' && { echo "BLOCKED: force-push" >&2; exit 2; }
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+tag[[:space:]]+.*(-f|-d|--force|--delete)' && { echo "BLOCKED: tag move/delete" >&2; exit 2; }
printf '%s' "$cmd" | grep -Eq '(cat|less|head|tail)[[:space:]]+[^|]*\.env' && { echo "BLOCKED: read .env" >&2; exit 2; }
exit 0
