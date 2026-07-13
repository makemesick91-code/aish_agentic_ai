#!/usr/bin/env bash
# test-change-classifier.sh — unit tests for classify-changes.sh routing logic.
#
# Exercises the pure path->category mapping and the fail-closed superset rules in
# an isolated throwaway git repo, so tests do not depend on the current diff.
# Rule: .claude/rules/28 (CI-PRINCIPLE-10 fail closed; CI-PRINCIPLE-19 evidence).
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
CLASSIFIER="$ROOT/scripts/ci/classify-changes.sh"
fail=0

TMP="$(mktemp -d)"
trap 'rm -rf "$TMP"' EXIT
cd "$TMP"
git init -q
git config user.email t@t.local
git config user.name t
git checkout -q -b main
echo seed > seed.md
git add -A && git commit -qm seed
BASE="$(git rev-parse HEAD)"

# make_change <path> ... ; commit; classify; echo flags to $OUT
classify_after() { # $1.. = files to create/modify
  for p in "$@"; do mkdir -p "$(dirname "$p")"; echo "x" > "$p"; done
  git add -A >/dev/null 2>&1
  git commit -qm "change" >/dev/null 2>&1
  BASE_SHA="$BASE" HEAD_SHA=HEAD bash "$CLASSIFIER" 2>/dev/null
  git reset -q --hard "$BASE" >/dev/null 2>&1
  git clean -fdq >/dev/null 2>&1   # drop the classifier's untracked evidence json so it can't contaminate the next diff
}

expect() { # $1=label $2=grep-pattern $3=output
  if printf '%s\n' "$3" | grep -Eq "$2"; then
    echo "  ok: $1"
  else
    echo "  FAIL: $1 (pattern /$2/ not found)"; echo "$3" | sed 's/^/      /'; fail=1
  fi
}

echo "== classify-changes unit tests =="

out="$(classify_after docs/product/FOO.md)"
expect "docs-only => not full suite" 'full_safe_suite=false' "$out"
expect "docs-only => run_documentation" 'run_documentation=true' "$out"

out="$(classify_after .github/workflows/pr-ci.yml)"
expect "workflow => run_workflow_security" 'run_workflow_security=true' "$out"

out="$(classify_after app/Models/User.php)"
expect "backend => full safe suite" 'full_safe_suite=true' "$out"

out="$(classify_after database/migrations/2026_x.php)"
expect "database => full safe suite" 'full_safe_suite=true' "$out"

out="$(classify_after tests/Security/CrossTenantTest.php)"
expect "security test => full safe suite" 'full_safe_suite=true' "$out"

out="$(classify_after some/unknown/place.xyz)"
expect "unknown path => fail closed (full)" 'full_safe_suite=true' "$out"
expect "unknown path => categories include unknown" 'categories=.*unknown' "$out"

out="$(classify_after "docs/product/space name.md")"
expect "filename with space handled" 'run_documentation=true' "$out"

out="$(classify_after composer.json)"
expect "dependency manifest => full safe suite" 'full_safe_suite=true' "$out"

out="$(classify_after docs/a.md docs/security/b.md .github/workflows/c.yml)"
expect "mixed (>=3 cats) => full safe suite" 'full_safe_suite=true' "$out"

# Deleted-file safety: delete an existing file.
git rm -q seed.md >/dev/null 2>&1; git commit -qm del >/dev/null 2>&1
out="$(BASE_SHA="$BASE" HEAD_SHA=HEAD bash "$CLASSIFIER" 2>/dev/null)"
git reset -q --hard "$BASE" >/dev/null 2>&1
expect "deleted file classified without crash" 'full_safe_suite=(true|false)' "$out"

if [ "$fail" -eq 0 ]; then echo "PASS: change classifier tests"; else echo "test-change-classifier: FAILED"; exit 1; fi
