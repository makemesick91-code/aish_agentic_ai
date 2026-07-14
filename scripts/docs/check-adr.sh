#!/usr/bin/env bash
# check-adr.sh — ADR structure, unique sequential numbering, valid status, and Step 3 required sections.
# Rule: .claude/rules/08, 20; canonical: ADRs 0001..0032. No orphan fundamental decision; no TBD on Step 3 ADRs.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
DIR="docs/decisions/adr"
fail=0

# 1. Sequential unique numbering 0001..0046 (each exactly one file, 4-digit ids).
LAST=50
for i in $(seq 1 "$LAST"); do
  n="$(printf '%04d' "$i")"
  matches=( "$DIR/${n}-"*.md )
  if [ ! -e "${matches[0]}" ]; then echo "FAIL: ADR ${n}-*.md missing"; fail=1; continue; fi
  if [ "${#matches[@]}" -ne 1 ]; then echo "FAIL: ADR number ${n} has ${#matches[@]} files (must be unique)"; fail=1; fi
done
echo "OK: ADR sequence 0001..$(printf '%04d' "$LAST") present and unique"

# 2. Lenient checks for all ADRs; strict checks for Step 3 ADRs (0009..0032).
for f in "$DIR"/[0-9][0-9][0-9][0-9]-*.md; do
  base="$(basename "$f")"; num="${base%%-*}"
  head -1 "$f" | grep -Eq "^# ADR ${num}" || { echo "FAIL($base): H1 not '# ADR ${num} …'"; fail=1; }
  grep -q '\*\*Status:\*\*' "$f" || { echo "FAIL($base): missing **Status:**"; fail=1; }
  grep -q '^## Context'  "$f" || { echo "FAIL($base): missing ## Context"; fail=1; }
  grep -q '^## Decision' "$f" || { echo "FAIL($base): missing ## Decision"; fail=1; }
  # Strict: Step 3 fundamental ADRs (base-10 to avoid octal parsing of 0009/0018/...).
  if [ "$((10#$num))" -ge 9 ]; then
    grep -q '^## Alternatives'  "$f" || { echo "FAIL($base): missing ## Alternatives"; fail=1; }
    grep -q '^## Consequences'  "$f" || { echo "FAIL($base): missing ## Consequences"; fail=1; }
    grep -q '^## Impacts'       "$f" || { echo "FAIL($base): missing ## Impacts"; fail=1; }
    for tok in 'Security:' 'Privacy:' 'Tenant' 'Database:' 'Operational:' 'Cost:'; do
      grep -q "$tok" "$f" || { echo "FAIL($base): Impacts missing '$tok'"; fail=1; }
    done
    grep -Eq '^## (Verification|Test)' "$f" || { echo "FAIL($base): missing ## Verification/Test (fitness)"; fail=1; }
    grep -q '^## Related'   "$f" || { echo "FAIL($base): missing ## Related"; fail=1; }
    grep -q '^## Evidence'  "$f" || { echo "FAIL($base): missing ## Evidence"; fail=1; }
    grep -Eq '^## Non-claim' "$f" || { echo "FAIL($base): missing ## Non-claims"; fail=1; }
    grep -Eq '^## Rollback|^## Supersession' "$f" || { echo "FAIL($base): missing ## Rollback/supersession"; fail=1; }
    grep -q 'Accepted' "$f" || { echo "FAIL($base): Step 3 ADR not Accepted"; fail=1; }
    if grep -Eq '(^|[^A-Za-z])TBD([^A-Za-z]|$)' "$f"; then echo "FAIL($base): Step 3 ADR contains TBD"; fail=1; fi
    grep -Eiq 'AFR-[0-9]' "$f" || { echo "FAIL($base): Step 3 ADR has no AFR reference"; fail=1; }
  fi
done

if [ "$fail" -eq 0 ]; then echo "PASS: ADR structure + sequence + Step 3 required sections"; else echo "check-adr: FAILED"; exit 1; fi
