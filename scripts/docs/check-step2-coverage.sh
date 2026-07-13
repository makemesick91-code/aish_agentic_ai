#!/usr/bin/env bash
# check-step2-coverage.sh — Step 2 (Persona & Pilot Use Cases) decision, persona, use-case, privacy,
# approval, review-gating, manual-fallback, and truthful-state coverage. No orphan P0 requirement.
# Rule: .claude/rules/16–19; canonical: Persona and Pilot Use Cases v1.0.0.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
have() { # file, human-readable, regex proving content present
  local f="$1" label="$2" re="$3"
  if [ ! -f "$f" ]; then echo "FAIL: missing $f ($label)"; fail=1; return; fi
  if LC_ALL=C grep -Eiq -- "$re" "$f"; then echo "OK: $label"; else echo "FAIL: $f missing '$label' (/$re/)"; fail=1; fi
}

PERSONA="docs/product/PERSONA_AND_PILOT_USE_CASES.md"
CATALOG="docs/product/PILOT_USE_CASE_CATALOG.md"
PMATRIX="docs/product/PILOT_PERSONA_MATRIX.md"
DATABND="docs/security/PILOT_DATA_BOUNDARY.md"
APPROVAL="docs/ai/PILOT_AI_HUMAN_APPROVAL_RULES.md"
REPLY="docs/security/PILOT_PUBLIC_REPLY_SAFETY.md"
FALLBACK="docs/ai/PILOT_MANUAL_FALLBACK.md"
STATES="docs/product/PILOT_WORKFLOW_STATES.md"
METRICS="docs/product/PILOT_SUCCESS_METRICS.md"
RTM="docs/testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md"
COV="docs/quality/STEP_2_COVERAGE_MATRIX.md"

# 1. Required Step 2 derived documents exist and are non-empty.
STEP2_DOCS=(
  "docs/product/PERSONA_AND_PILOT_USE_CASES.md"
  "docs/product/PILOT_SCOPE_AND_BOUNDARIES.md" "$PMATRIX" "$CATALOG"
  "docs/product/PILOT_CUSTOMER_JOURNEYS.md" "$STATES" "$METRICS"
  "docs/product/PILOT_READINESS_CHECKLIST.md" "docs/product/PILOT_GO_WATCH_NO_GO.md" "docs/product/PILOT_RACI.md"
  "$DATABND" "docs/security/PILOT_PRIVACY_RULES.md" "$REPLY" "docs/security/PILOT_THREAT_AND_ABUSE_CASES.md"
  "$APPROVAL" "docs/ai/PILOT_AI_EVALUATION_PLAN.md" "$FALLBACK"
  "docs/integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md"
  "docs/integrations/GOOGLE_BUSINESS_PROFILE_PILOT_READINESS.md"
  "docs/integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md"
  "$RTM" "docs/testing/PILOT_ACCEPTANCE_TEST_CATALOG.md" "docs/testing/PILOT_UAT_PLAN.md" "$COV"
)
for f in "${STEP2_DOCS[@]}"; do
  if [ ! -s "$f" ]; then echo "FAIL: Step 2 doc missing/empty: $f"; fail=1; fi
done
echo "OK: checked ${#STEP2_DOCS[@]} Step 2 documents for presence"

# 2. Pilot tenant + recommended branch.
have "$PERSONA" "pilot tenant Klinik Gigi Daengtisia" "Klinik Gigi Daengtisia"
have "$PERSONA" "recommended branch Daengtisia Pusat" "Daengtisia Pusat"

# 3. Persona coverage (5 primary personas present in the persona matrix).
have "$PMATRIX" "persona: Business Owner" "Business Owner"
have "$PMATRIX" "persona: Pilot Coordinator" "Pilot Coordinator"
have "$PMATRIX" "persona: Branch Manager" "Branch Manager"
have "$PMATRIX" "persona: Recovery Assignee" "Recovery Assignee"
have "$PMATRIX" "persona: Reputation Approver" "Reputation Approver"

ACAT="docs/testing/PILOT_ACCEPTANCE_TEST_CATALOG.md"

# 4. Use-case completeness + computed traceability (no orphan P0):
#    each UC-P0-NN MUST appear in the catalog, have an AT-P0-NN acceptance test, and be mapped in the RTM
#    on a row that also carries a rule token and a derived .md doc. This COMPUTES the mapping rather than
#    trusting a self-attested sentence.
for n in $(seq -w 1 16); do
  LC_ALL=C grep -Eq -- "UC-P0-${n}" "$CATALOG" || { echo "FAIL: UC-P0-${n} missing from $CATALOG"; fail=1; }
  LC_ALL=C grep -Eq -- "AT-P0-${n}" "$ACAT"    || { echo "FAIL: AT-P0-${n} missing from $ACAT"; fail=1; }
  row="$(LC_ALL=C grep -E -- "UC-P0-${n}" "$RTM" || true)"
  if [ -z "$row" ]; then echo "FAIL: UC-P0-${n} not mapped in $RTM"; fail=1
  else
    printf '%s\n' "$row" | LC_ALL=C grep -Eq -- "AT-P0-${n}" || { echo "FAIL: UC-P0-${n} RTM row has no AT-P0-${n}"; fail=1; }
    printf '%s\n' "$row" | LC_ALL=C grep -Eq -- "\.md|rules/[01][0-9]| 1[6-9]|,1[6-9]" || { echo "FAIL: UC-P0-${n} RTM row lacks rule/derived-doc token"; fail=1; }
  fi
done
echo "OK: UC-P0-01..16 present in catalog, have AT-P0 tests, and are mapped (no orphan P0)"

# 4b. Hard-gate acceptance tests AT-GATE-01..06 exist.
for g in 1 2 3 4 5 6; do
  LC_ALL=C grep -Eq -- "AT-GATE-0${g}" "$ACAT" || { echo "FAIL: AT-GATE-0${g} missing from $ACAT"; fail=1; }
done
echo "OK: hard-gate acceptance tests AT-GATE-01..06 checked"

# 5. Invitation baseline markers (semantic, not bare numbers).
have "docs/integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md" "invitation frequency cap (1 per 14 days)" "invitation.*14 (calendar )?day|1[ ]*(invitation)?.*14 (calendar )?day|frequency cap.*14"
have "docs/integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md" "sending window 09:00-20:00" "09[:.]00|20[:.]00"

# 6. Healthcare privacy boundary — representative prohibited fields.
have "$DATABND" "prohibited: diagnosis" "diagnosis"
have "$DATABND" "prohibited: odontogram" "odontogram"
have "$DATABND" "prohibited: medical record number" "medical record|rekam medis"
have "$DATABND" "prohibited: prescription" "prescription|resep|medication"

# 7. Human approval + review gating prohibition + manual fallback + truthful state (semantic regexes).
have "$APPROVAL" "human approval required" "human approval"
have "$REPLY" "review gating prohibited" "MUST NOT.*gat|no.*review gating|review gating.*(prohibit|forbidden)|prohibit.*gating"
have "$REPLY" "publication failed truthful state" "Publication failed|publication fail"
have "$FALLBACK" "manual fallback when AI unavailable" "manual.*fallback|fallback.*(when|if).*(AI|provider)|AI.*unavailable.*manual"
have "$STATES" "truthful external states" "Published|Publication failed|under review"

# 8. Metrics framed as hypotheses / hard gates separated.
have "$METRICS" "operational targets are hypotheses" "hypothes"
have "$METRICS" "hard safety/correctness gates" "hard (safety|gate)|cross-tenant"

# 9. Traceability: no orphan critical requirement.
have "$RTM" "no orphan critical requirement" "Orphan critical requirements: none"

# 10. Step 2 coverage matrix asserts no gap and has no GAP status cells.
have "$COV" "coverage asserts no critical gap" "No critical gap"
if grep -Eiq '\|[[:space:]]*(NOT COVERED|GAP|MISSING|TBD|TODO)[[:space:]]*\|' "$COV" 2>/dev/null; then
  echo "FAIL: Step 2 coverage matrix contains an uncovered/gap status cell"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS: Step 2 persona/use-case/privacy/approval/fallback coverage complete"; else echo "check-step2-coverage: FAILED"; exit 1; fi
