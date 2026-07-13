#!/usr/bin/env bash
# validate.sh — aggregate documentation-as-code gate runner. Runs every gate, captures output + exit codes
# to docs/evidence/validation/, and fails if any gate fails. Safe/deterministic; no repository mutation
# beyond writing evidence files.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
EVID="docs/evidence/validation"
mkdir -p "$EVID"
overall=0

run() { # $1 = name, $2.. = command
  local name="$1"; shift
  local log="$EVID/${name}.log"
  echo "=== ${name} ==="
  if "$@" >"$log" 2>&1; then
    echo "  PASS (exit 0) -> $log"
    tail -1 "$log" | sed 's/^/  /'
  else
    local rc=$?
    echo "  FAIL (exit $rc) -> $log"; sed 's/^/  /' "$log"
    overall=1
  fi
}

# Markdown structure sanity: no committed .md may be empty (no empty placeholders allowed).
run markdown-structure bash -c '
  set -e
  bad=0
  if git rev-parse --git-dir >/dev/null 2>&1; then files=$(git ls-files "*.md"); else files=$(find . -name "*.md" -not -path "./.git/*"); fi
  for f in $files; do
    if [ ! -s "$f" ]; then echo "EMPTY: $f"; bad=1; fi
  done
  [ "$bad" -eq 0 ] && echo "PASS: no empty markdown files" || { echo "markdown-structure: FAILED"; exit 1; }
'

run version-consistency   scripts/docs/check-version-consistency.sh
run internal-links        scripts/docs/check-links.sh
run rule-frontmatter      scripts/docs/check-rule-frontmatter.sh
run foundation-coverage   scripts/docs/check-foundation-coverage.sh
run secret-scan           scripts/docs/secret-scan.sh
run hook-guard-tests      scripts/hooks/test-guard.sh
run graphify-build        scripts/graphify/build.sh
run graphify-query-smoke  scripts/graphify/query-smoke.sh

# Generated-artifact drift: rebuilding the graph manifest must be deterministic (build re-run is idempotent).
run graphify-drift bash -c '
  set -e
  scripts/graphify/build.sh >/dev/null 2>&1
  a=$(sha256sum scripts/graphify/out/graph-manifest.json 2>/dev/null | cut -d" " -f1 || true)
  scripts/graphify/build.sh >/dev/null 2>&1
  b=$(sha256sum scripts/graphify/out/graph-manifest.json 2>/dev/null | cut -d" " -f1 || true)
  [ -n "$a" ] && [ "$a" = "$b" ] && echo "PASS: graph manifest deterministic ($a)" || { echo "drift detected"; exit 1; }
'

echo
if [ "$overall" -eq 0 ]; then
  echo "VALIDATE: ALL GATES PASSED"
else
  echo "VALIDATE: ONE OR MORE GATES FAILED"
fi
exit "$overall"
