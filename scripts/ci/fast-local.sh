#!/usr/bin/env bash
# fast-local.sh — the fast local/draft gate (CI-PRINCIPLE-02/03).
#
# Cheap, deterministic checks safe to run repeatedly during development and on a
# DRAFT pull request. This is NOT a substitute for full-local.sh before marking a
# PR ready-for-review. Read/validate only; no repository mutation.
# Rule: .claude/rules/28. Evidence: docs/evidence/cicd-ctrl-1/local-validation/.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
EVID="docs/evidence/cicd-ctrl-1/local-validation"
mkdir -p "$EVID"
overall=0

run() { # $1 name, $2.. cmd
  local name="$1"; shift
  local log="$EVID/fast-${name}.log"
  echo "=== ${name} ==="
  if "$@" >"$log" 2>&1; then echo "  PASS -> $log"; tail -1 "$log" | sed 's/^/  /'
  else echo "  FAIL (exit $?) -> $log"; sed 's/^/  /' "$log"; overall=1; fi
}

run change-classifier-tests scripts/ci/test-change-classifier.sh
run required-gate-tests     scripts/ci/test-required-gate.sh
run ci-topology             scripts/ci/validate-ci-topology.sh
run workflow-security       scripts/ci/validate-workflow-security.sh
run secret-scan             scripts/docs/secret-scan.sh
run rule-frontmatter        scripts/docs/check-rule-frontmatter.sh
run hook-guard-tests        scripts/hooks/test-guard.sh
run classify-current        bash -c 'scripts/ci/classify-changes.sh >/dev/null && echo "classified current diff"'

if [ "$overall" -eq 0 ]; then echo "PASS: fast-local gate"; else echo "fast-local: FAILED"; exit 1; fi
