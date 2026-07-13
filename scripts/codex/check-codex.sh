#!/usr/bin/env bash
# check-codex.sh — static validation of Codex project config, command rules, and hooks.
# The Codex CLI is not required to run this. Rule: .claude/rules/15; AFR-069, AFR-070.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0

# 1. config.toml present, project-safe, no secrets.
CFG=".codex/config.toml"
if [ ! -s "$CFG" ]; then echo "FAIL: missing $CFG"; fail=1; else
  for kv in 'approval_policy = "on-request"' 'sandbox_mode    = "workspace-write"' 'web_search      = "cached"'; do
    grep -qF "$kv" "$CFG" || { echo "FAIL: $CFG missing '$kv'"; fail=1; }
  done
  if grep -Eiq '(api[_-]?key|secret|token|password|bearer)[[:space:]]*=' "$CFG"; then echo "FAIL: $CFG appears to contain a secret"; fail=1; fi
  echo "OK: $CFG project-safe"
fi

# 2. Command rules present with forbidden/prompt/safe + positive/negative tests.
shopt -s nullglob
RULES=(.codex/rules/*.rules)
if [ "${#RULES[@]}" -eq 0 ]; then echo "FAIL: no .codex/rules/*.rules present"; fail=1; fi
for r in "${RULES[@]}"; do
  grep -q 'decision="forbidden"' "$r" || { echo "FAIL: $r has no forbidden rule"; fail=1; }
  grep -q 'decision="prompt"'    "$r" || { echo "FAIL: $r has no prompt rule"; fail=1; }
  grep -q 'decision="safe"'      "$r" || { echo "FAIL: $r has no safe rule"; fail=1; }
  grep -q '# TEST: block'        "$r" || { echo "FAIL: $r has no negative (block) test"; fail=1; }
  grep -q '# TEST: allow'        "$r" || { echo "FAIL: $r has no positive (allow) test"; fail=1; }
  # A rule that fails to forbid force-push/tag deletion is a failure.
  grep -Eq 'args=\["push", "--force"\]' "$r" || { echo "FAIL: $r does not forbid force-push"; fail=1; }
  grep -Eq 'args=\["tag", "-d"\]'       "$r" || { echo "FAIL: $r does not forbid tag deletion"; fail=1; }
done
echo "OK: checked ${#RULES[@]} .codex/rules file(s)"

# 3. hooks.json valid JSON with the 5 hook types; hook scripts exist + executable.
HJ=".codex/hooks.json"
if [ ! -s "$HJ" ]; then echo "FAIL: missing $HJ"; fail=1; else
  if command -v python3 >/dev/null 2>&1; then python3 -c "import json,sys; json.load(open('$HJ'))" || { echo "FAIL: $HJ invalid JSON"; fail=1; }; fi
  for k in SessionStart PreToolUse PostToolUse PreCompact Stop; do
    grep -q "\"$k\"" "$HJ" || { echo "FAIL: $HJ missing hook '$k'"; fail=1; }
  done
fi
for h in session-start pre-tool-use post-tool-use pre-compact stop; do
  f=".codex/hooks/$h.sh"
  [ -x "$f" ] || { echo "FAIL: hook not present/executable: $f"; fail=1; }
done

# 4. Run the hook tests (positive + negative) — these DO execute (bash-only, no Codex needed).
if [ -x ".codex/hooks/test-hooks.sh" ]; then
  bash .codex/hooks/test-hooks.sh || { echo "FAIL: Codex hook tests failed"; fail=1; }
else echo "FAIL: missing .codex/hooks/test-hooks.sh"; fail=1; fi

# 5. Note runtime limitation truthfully (do not fabricate execpolicy runs).
if command -v codex >/dev/null 2>&1; then
  echo "NOTE: codex CLI present — run 'codex execpolicy check' per rule file for runtime verification"
else
  echo "NOTE: codex CLI absent — execpolicy runtime verification is OD-07 (WATCH); static checks passed"
fi

if [ "$fail" -eq 0 ]; then echo "PASS: Codex config/rules/hooks statically valid"; else echo "check-codex: FAILED"; exit 1; fi
