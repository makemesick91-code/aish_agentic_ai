#!/usr/bin/env bash
# check-step9-coverage.sh — Step 9 (Competitive Gap Audit & Architecture Re-baseline) artifact + governance coverage.
# Rule 34. Verifies the Step 9 architecture-lock artifacts, ADR/AFR integrity, version consistency, roadmap lock, and
# truthful (no-stale) status. Static/read-only; no repository mutation.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0

need_file() { if [ -s "$1" ]; then echo "OK: $1"; else echo "FAIL: missing/empty $1"; fail=1; fi; }
need_grep() { if grep -q "$2" "$1" 2>/dev/null; then echo "OK: $1 contains '$2'"; else echo "FAIL: $1 missing '$2'"; fail=1; fi; }

echo "== Step 9 required artifacts =="
need_file docs/product/capability-inventory/STEP_9_CAPABILITY_INVENTORY.md
need_file docs/product/competitive/STEP_9_COMPETITOR_CAPABILITY_MATRIX.md
need_file docs/product/competitive/STEP_9_COMPETITIVE_GAP_REGISTER.md
need_file docs/architecture/experience-os/README.md
need_file docs/architecture/experience-os/DOMAIN_BOUNDARY_MAP.md
need_file docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md
need_file docs/architecture/experience-os/EXPERIENCE_EVENT_LEDGER.md
need_file docs/architecture/experience-os/CHANNEL_ADAPTER_ARCHITECTURE.md
need_file docs/architecture/experience-os/AI_TOOL_PERMISSION_AND_APPROVAL_ARCHITECTURE.md
need_file docs/architecture/experience-os/MIGRATION_AND_COMPATIBILITY_STRATEGY.md
need_file docs/security/STEP_9_THREAT_MODEL.md
need_file docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md
need_file docs/product/EXPERIENCE_OS_ROADMAP.md
need_file docs/product/AGENTIC_EXPERIENCE_OS_PRD_ADDENDUM.md
need_file docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md
need_file .claude/rules/34-agentic-experience-os-architecture-baseline.md

echo "== Step 9 ADRs 0063..0068 =="
for n in 0063 0064 0065 0066 0067 0068; do
  m=( docs/decisions/adr/${n}-*.md )
  if [ -e "${m[0]}" ]; then echo "OK: ADR ${n} present"; else echo "FAIL: ADR ${n} missing"; fail=1; fi
done

echo "== AFR-211..238 in catalog =="
AFR=docs/architecture/APPLICATION_FOUNDATION_RULES.md
for a in AFR-211 AFR-215 AFR-220 AFR-225 AFR-230 AFR-235 AFR-238; do need_grep "$AFR" "$a"; done
need_grep "$AFR" "238 AFRs total"

echo "== Master Source v2.11.0 + section 75 =="
MS=docs/canonical/MASTER_SOURCE.md
msver="$(grep -m1 -E '^\*\*Versi:\*\*' "$MS" | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1 || true)"
if [ "$msver" = "2.11.0" ]; then echo "OK: Master Source v2.11.0"; else echo "FAIL: Master Source version '$msver' != 2.11.0"; fail=1; fi
need_grep "$MS" "# 75. STEP 9"
need_grep "$MS" "## Version 2.11.0"

echo "== No stale Step 8 status in Master Source header (truthful-state) =="
# The header status block MUST reflect Step 8 as MERGED, not the pre-tag "NOT merged" wording.
header="$(sed -n '9,30p' "$MS")"
if printf '%s' "$header" | grep -q "6792db5"; then echo "OK: header records Step 8 merge SHA 6792db5"; else echo "FAIL: header does not record Step 8 as merged"; fail=1; fi
if printf '%s' "$header" | grep -Eq 'Step 8.*\*\*NOT\*\* merged'; then echo "FAIL: header still carries stale 'Step 8 NOT merged' status"; fail=1; else echo "OK: no stale Step 8 'NOT merged' status in header"; fi

echo "== PRD unchanged at 1.3.0; addendum at 1.0.0 =="
pver="$(grep -m1 -E '^\*\*Versi:\*\*' docs/canonical/PRD.md | grep -oE '[0-9]+\.[0-9]+\.[0-9]+' | head -1 || true)"
if [ "$pver" = "1.3.0" ]; then echo "OK: PRD v1.3.0 (unchanged)"; else echo "FAIL: PRD version '$pver' != 1.3.0"; fail=1; fi
need_grep docs/product/AGENTIC_EXPERIENCE_OS_PRD_ADDENDUM.md "1.0.0"

echo "== Roadmap wave lock + truthful NOT STARTED =="
RM=docs/product/EXPERIENCE_OS_ROADMAP.md
for tok in "Wave 1" "Wave 2" "Wave 3" "NOT STARTED"; do need_grep "$RM" "$tok"; done
need_grep docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md "Customer 360"

echo "== rule 34 + CLAUDE/AGENTS reference Step 9 =="
need_grep CLAUDE.md "34-agentic-experience-os-architecture-baseline.md"
need_grep CLAUDE.md "v2.11.0"
need_grep AGENTS.md "v2.11.0"

if [ "$fail" -eq 0 ]; then echo "PASS: Step 9 competitive-gap/architecture-rebaseline coverage complete"; else echo "check-step9-coverage: FAILED"; exit 1; fi
