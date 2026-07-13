#!/usr/bin/env bash
# required-gate-decision.sh — the stable "CI / Required Gate" decision function.
#
# Consumes the results of the routed jobs and decides the single required
# conclusion for a pull request (CI-PRINCIPLE-08). It is used by pr-ci.yml and is
# unit-tested by test-required-gate.sh so the gate logic itself is verifiable.
#
# Accepts (needs.<job>.result) values: success | failure | cancelled | skipped |
# ''(empty/never-ran). Rules:
#   * any `failure`            -> gate FAILS
#   * any unexpected `cancelled` -> gate FAILS (a stale cancel is fine only when a
#     newer run supersedes it; here a cancelled required input is treated as fail)
#   * missing classification   -> gate FAILS (fail closed)
#   * `success` and intentional `skipped` per routing -> gate PASSES
#
# Inputs via env:
#   CLASSIFY_RESULT     result of the classify-changes job (must be success)
#   FAST_RESULT         result of the fast/draft job (success|skipped)
#   FULL_DOC_RESULT     result of the full documentation job (success|skipped)
#   WF_SEC_RESULT       result of the workflow-security job (success|skipped)
#   IS_DRAFT            true|false — whether the PR is a draft
# Output: prints DECISION and a summary; exit 0 = pass, 1 = fail.
set -uo pipefail

CLASSIFY_RESULT="${CLASSIFY_RESULT:-}"
FAST_RESULT="${FAST_RESULT:-}"
FULL_DOC_RESULT="${FULL_DOC_RESULT:-}"
WF_SEC_RESULT="${WF_SEC_RESULT:-}"
IS_DRAFT="${IS_DRAFT:-false}"

fail=0
note() { echo "  - $1"; }

# Classification must have succeeded — without it routing is unknown (fail closed).
if [ "$CLASSIFY_RESULT" != "success" ]; then
  note "classification job did not succeed (result='$CLASSIFY_RESULT') -> fail closed"; fail=1
fi

# Any explicit failure fails the gate.
for kv in "classify=$CLASSIFY_RESULT" "fast=$FAST_RESULT" "full-doc=$FULL_DOC_RESULT" "wf-sec=$WF_SEC_RESULT"; do
  name="${kv%%=*}"; res="${kv#*=}"
  case "$res" in
    failure)   note "job '$name' FAILED"; fail=1 ;;
    cancelled) note "job '$name' was cancelled (unexpected for a required input)"; fail=1 ;;
    ""|success|skipped) : ;;
    *) note "job '$name' has unknown result '$res' -> fail closed"; fail=1 ;;
  esac
done

# Ready (non-draft) PRs MUST have actually run the full documentation gate.
if [ "$IS_DRAFT" != "true" ]; then
  if [ "$FULL_DOC_RESULT" != "success" ]; then
    note "ready PR requires the full documentation gate to succeed (result='$FULL_DOC_RESULT')"; fail=1
  fi
fi

if [ "$fail" -eq 0 ]; then
  echo "DECISION: PASS (draft=$IS_DRAFT)"
  exit 0
else
  echo "DECISION: FAIL (draft=$IS_DRAFT)"
  exit 1
fi
