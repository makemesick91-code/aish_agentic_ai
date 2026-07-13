#!/usr/bin/env bash
# validate-ci-topology.sh — assert the CICD-CTRL-1 workflow topology invariants.
#
# Guards the structural guarantees that the run-budget and required-gate depend on
# (rule 28; CI-PRINCIPLE-06/07/08/11/12):
#   * exactly one PR-lifecycle workflow that carries the stable required gate
#   * that workflow triggers on pull_request (incl. ready_for_review), NOT on
#     feature-branch push (no duplicate push+PR full CI)
#   * it declares concurrency with cancel-in-progress (stale-run cancellation)
#   * a single stable required-gate job named "Required Gate"
#   * a post-merge workflow on push:main that does NOT run the full release suite
#   * no second workflow triggers the full documentation suite on pull_request
# Static, read-only. Rule: .claude/rules/28.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
WFDIR=".github/workflows"
fail=0

PR="$WFDIR/pr-ci.yml"
POST="$WFDIR/main-post-merge.yml"

# 1. PR lifecycle workflow exists.
if [ ! -f "$PR" ]; then echo "FAIL: missing $PR"; fail=1; fi

if [ -f "$PR" ]; then
  grep -Eq '^[[:space:]]*pull_request:' "$PR" || { echo "FAIL: $PR must trigger on pull_request"; fail=1; }
  grep -Eq 'ready_for_review' "$PR" || { echo "FAIL: $PR must react to ready_for_review"; fail=1; }
  # No feature-branch push trigger on the full PR workflow (avoid duplicate push+PR CI).
  # Scope the scan to the `on:` mapping only: set state at `^on:` and clear it at the next
  # top-level key, so an unrelated `push:` elsewhere cannot cause a false FAIL.
  if awk '/^on:[[:space:]]*$/{o=1;next} /^[A-Za-z]/{o=0} o&&/^[[:space:]]+push:/{found=1} END{exit !found}' "$PR" >/dev/null 2>&1; then
    echo "FAIL: $PR must not use a push: trigger (duplicate push+PR CI risk)"; fail=1
  fi
  grep -Eq '^[[:space:]]*concurrency:' "$PR" || { echo "FAIL: $PR missing concurrency"; fail=1; }
  grep -Eq 'cancel-in-progress:[[:space:]]*true' "$PR" || { echo "FAIL: $PR concurrency must cancel-in-progress"; fail=1; }
  grep -Eq 'Required Gate' "$PR" || { echo "FAIL: $PR missing stable 'Required Gate'"; fail=1; }
  grep -Eq 'if:[[:space:]]*\$\{\{[[:space:]]*always\(\)' "$PR" || grep -Eq 'if:[[:space:]]*always\(\)' "$PR" \
    || { echo "FAIL: $PR Required Gate must use if: always()"; fail=1; }
fi

# 2. Post-merge workflow is lightweight (must not call the full release suite aggregator).
if [ ! -f "$POST" ]; then echo "FAIL: missing $POST"; fail=1; fi
if [ -f "$POST" ]; then
  grep -Eq '^[[:space:]]*push:' "$POST" || { echo "FAIL: $POST must trigger on push"; fail=1; }
  if grep -Eq 'scripts/docs/validate\.sh|scripts/ci/full-local\.sh' "$POST"; then
    echo "FAIL: $POST must not run the full release suite (post-merge is lightweight)"; fail=1
  fi
fi

# 3. Only the PR workflow may run the full documentation aggregator on pull_request.
shopt -s nullglob
for wf in "$WFDIR"/*.yml "$WFDIR"/*.yaml; do
  [ "$(basename "$wf")" = "pr-ci.yml" ] && continue
  if grep -Eq '^[[:space:]]*pull_request:' "$wf" && grep -Eq 'validate\.sh|full-documentation' "$wf"; then
    echo "FAIL: $(basename "$wf") also runs the full doc suite on pull_request (single required workflow only)"; fail=1
  fi
done

if [ "$fail" -eq 0 ]; then echo "PASS: CI topology invariants"; else echo "validate-ci-topology: FAILED"; exit 1; fi
