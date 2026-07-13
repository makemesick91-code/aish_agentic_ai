#!/usr/bin/env bash
# check-foundation-coverage.sh — 100% coverage for permanent decisions + release gates; no orphan requirement.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
MATRIX="docs/quality/FOUNDATION_COVERAGE_MATRIX.md"
TRACE="docs/quality/REQUIREMENTS_TRACEABILITY_MATRIX.md"

# All 16 rule files (00..15) exist and are referenced in the coverage matrix.
for n in $(seq -w 0 15); do
  rule="$(find .claude/rules -maxdepth 1 -name "${n}-*.md" | head -1)"
  if [ -z "$rule" ]; then echo "FAIL: rule ${n}-*.md missing"; fail=1; continue; fi
  base="$(basename "$rule")"
  if ! grep -q "$base" "$MATRIX"; then echo "FAIL: $base not referenced in coverage matrix"; fail=1; fi
done

# No uncovered/gap markers as a table-cell STATUS value (prose like "No critical gap" is fine).
if grep -Eiq '\|[[:space:]]*(NOT COVERED|GAP|MISSING|TBD|TODO)[[:space:]]*\|' "$MATRIX"; then
  echo "FAIL: coverage matrix contains an uncovered/gap status cell"; fail=1
fi

# Coverage matrix asserts full coverage and no critical gap.
grep -q "No critical gap" "$MATRIX" || { echo "FAIL: coverage matrix does not assert 'No critical gap'"; fail=1; }

# Count COVERED rows (expect >= 16).
covered="$(grep -c '| COVERED |' "$MATRIX" || true)"
if [ "${covered:-0}" -lt 16 ]; then echo "FAIL: only ${covered:-0} COVERED rows (<16)"; fail=1; else echo "OK: ${covered} foundation categories COVERED"; fi

# Traceability: no orphan critical requirement.
if grep -q "Orphan critical requirements: none" "$TRACE"; then echo "OK: no orphan critical requirement"; else
  echo "FAIL: traceability matrix does not assert zero orphan critical requirements"; fail=1; fi

if [ "$fail" -eq 0 ]; then echo "PASS: foundation coverage 100% (permanent decisions + release gates)"; else echo "check-foundation-coverage: FAILED"; exit 1; fi
