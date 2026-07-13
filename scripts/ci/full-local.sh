#!/usr/bin/env bash
# full-local.sh — the full local release gate (CI-PRINCIPLE-02/04).
#
# Runs the complete documentation-as-code suite PLUS the CICD-CTRL-1 CI validators.
# MUST pass before a PR is marked ready-for-review. Mirrors what pr-ci.yml runs on
# a ready final head, so the final full CI is the confirmation, not the discovery.
# Read/validate only; no repository mutation. Rule: .claude/rules/28, 09, 13.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
EVID="docs/evidence/cicd-ctrl-1/local-validation"
mkdir -p "$EVID"
overall=0

run() { # $1 name, $2.. cmd
  local name="$1"; shift
  local log="$EVID/full-${name}.log"
  echo "=== ${name} ==="
  if "$@" >"$log" 2>&1; then echo "  PASS -> $log"; tail -1 "$log" | sed 's/^/  /'
  else echo "  FAIL (exit $?) -> $log"; sed 's/^/  /' "$log"; overall=1; fi
}

# CICD-CTRL-1 CI validators.
run change-classifier-tests scripts/ci/test-change-classifier.sh
run required-gate-tests     scripts/ci/test-required-gate.sh
run ci-topology             scripts/ci/validate-ci-topology.sh
run workflow-security       scripts/ci/validate-workflow-security.sh

# Full documentation-as-code aggregate (canonical gates + evidence).
run documentation-aggregate scripts/docs/validate.sh

if [ "$overall" -eq 0 ]; then echo "PASS: full-local gate"; else echo "full-local: FAILED"; exit 1; fi
