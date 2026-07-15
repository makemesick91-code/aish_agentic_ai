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
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+push[[:space:]]+.*(--force([-a-z]*)?|-f)([[:space:]]|$)' && block "force-push (incl. --force-with-lease) is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+push[[:space:]]+.*--delete'                    && block "remote ref deletion is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+tag([[:space:]]|.*[[:space:]])(-f|--force)([[:space:]]|$)' && block "moving/overwriting a tag is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+tag([[:space:]]|.*[[:space:]])(-d|--delete)([[:space:]]|$)' && block "tag deletion is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+(reset[[:space:]]+--hard|filter-branch|filter-repo)' && block "history rewrite / hard reset is prohibited"

# Secret / dump exposure via shell.
printf '%s' "$cmd" | grep -Eq '(^|[[:space:]])(cat|less|more|head|tail|bat)[[:space:]]+[^|]*\.env([[:space:]]|$|\.)' && block "reading .env via shell is prohibited"
printf '%s' "$cmd" | grep -Eq '\.(pem|key|p12|pfx)([[:space:]]|$)' && block "reading private key material is prohibited"

# Reckless recursive delete of the whole tree / root.
printf '%s' "$cmd" | grep -Eq 'rm[[:space:]]+-[a-z]*r[a-z]*f?[[:space:]]+(/|~|\*|\.)([[:space:]]|$)' && block "recursive delete of / ~ . or * is prohibited"

# Filesystem / device destruction and force-clean of the working tree.
printf '%s' "$cmd" | grep -Eq '(^|[[:space:]])mkfs([.-][a-z0-9]+)?([[:space:]]|$)' && block "filesystem creation (mkfs) is prohibited"
printf '%s' "$cmd" | grep -Eq '(^|[[:space:]])dd[[:space:]]+if=' && block "raw disk write (dd if=) is prohibited"
printf '%s' "$cmd" | grep -Eq '(^|[[:space:]])shred([[:space:]]|$)' && block "shred is prohibited"
printf '%s' "$cmd" | grep -Eq 'git[[:space:]]+clean[[:space:]].*(-[a-z]*f|--force)' && block "git clean force-delete of untracked files is prohibited"

# Implementation-phase supply-chain safeguards (Step 5+).
# The Step-4 planning "install nothing" safeguard is superseded for application
# implementation by the Step 5 Runtime & Repository Bootstrap Master Source update
# (rule 25, AFR-096 supersession; AFR-127): dependency *install* is a normal
# implementation activity and is permitted. Supply-chain integrity is enforced by
# committed lockfiles, `composer audit` / `npm audit`, dependency review, and the
# secret scan — not by this hook. Publishing, provisioning/deployment, and DNS
# mutation remain out of scope and are still blocked (AFR-102; rules 25, 21).
printf '%s' "$cmd" | grep -Eq '(npm|composer)[[:space:]]+publish([[:space:]]|$)' && block "package publishing is prohibited"
printf '%s' "$cmd" | grep -Eq 'terraform[[:space:]]+(apply|destroy)|pulumi[[:space:]]+up|kubectl[[:space:]]+(apply|delete)|docker[[:space:]]+push' && block "cloud provisioning / deployment is out of scope in planning"
printf '%s' "$cmd" | grep -Eq 'nsupdate|route53[[:space:]]+[^|]*(change|create|delete)|gcloud[[:space:]]+dns' && block "DNS mutation is out of scope in planning"

# CICD-CTRL-1 safeguards: skip directives MUST NOT bypass mandatory CI (AFR-118; rule 28).
# Covers every GitHub-recognized skip-CI directive form.
printf '%s' "$cmd" | grep -Eiq 'git[[:space:]]+commit[^|]*(\[(skip ci|ci skip|no ci|skip actions|actions skip)\]|\*\*\*NO_CI\*\*\*)' && block "skip-CI directive on mandatory CI is prohibited (rule 28, AFR-118)"

exit 0
