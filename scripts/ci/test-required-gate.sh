#!/usr/bin/env bash
# test-required-gate.sh — unit tests for required-gate-decision.sh.
# Rule: .claude/rules/28 (CI-PRINCIPLE-08 stable gate, CI-PRINCIPLE-10 fail closed).
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
GATE="$ROOT/scripts/ci/required-gate-decision.sh"
fail=0

check() { # $1=expected(pass|fail) $2=label ; env already set
  bash "$GATE" >/dev/null 2>&1; local rc=$?
  local got=pass; [ "$rc" -ne 0 ] && got=fail
  if [ "$got" = "$1" ]; then echo "  ok: $2"; else echo "  FAIL: $2 (expected $1, got $got)"; fail=1; fi
}

echo "== required-gate decision tests =="

CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=success BACKEND_RESULT=success IS_DRAFT=false \
  bash "$GATE" >/dev/null 2>&1 && echo "  ok: ready all-green passes" || { echo "  FAIL: ready all-green"; fail=1; }

( export CLASSIFY_RESULT=success FAST_RESULT=success FULL_DOC_RESULT=skipped WF_SEC_RESULT=skipped BACKEND_RESULT=skipped IS_DRAFT=true; check fail "draft never satisfies the required gate (red until ready)" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=failure WF_SEC_RESULT=success BACKEND_RESULT=success IS_DRAFT=false; check fail "ready with full-doc failure fails" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=skipped WF_SEC_RESULT=success BACKEND_RESULT=success IS_DRAFT=false; check fail "ready without full-doc run fails (fail closed)" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=skipped BACKEND_RESULT=success IS_DRAFT=false; check fail "ready without workflow-security run fails (fail closed)" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=success BACKEND_RESULT=failure IS_DRAFT=false; check fail "ready with backend runtime failure fails" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=success BACKEND_RESULT=skipped IS_DRAFT=false; check fail "ready without backend runtime run fails (fail closed)" )
( export CLASSIFY_RESULT=failure FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=success BACKEND_RESULT=success IS_DRAFT=false; check fail "classification failure fails closed" )
( export CLASSIFY_RESULT=success FAST_RESULT=cancelled FULL_DOC_RESULT=success WF_SEC_RESULT=success BACKEND_RESULT=success IS_DRAFT=false; check fail "cancelled required input fails" )
( export CLASSIFY_RESULT=success FAST_RESULT=skipped FULL_DOC_RESULT=success WF_SEC_RESULT=failure BACKEND_RESULT=success IS_DRAFT=false; check fail "workflow-security failure fails" )
( export CLASSIFY_RESULT="" FAST_RESULT="" FULL_DOC_RESULT="" WF_SEC_RESULT="" BACKEND_RESULT="" IS_DRAFT=false; check fail "empty results fail closed" )

if [ "$fail" -eq 0 ]; then echo "PASS: required-gate tests"; else echo "test-required-gate: FAILED"; exit 1; fi
