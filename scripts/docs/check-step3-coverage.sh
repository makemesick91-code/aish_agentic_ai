#!/usr/bin/env bash
# check-step3-coverage.sh — Step 3 architecture/ADR/module/tenant-isolation/AFR/security/AI/reliability coverage,
# traceability (no orphan), and truthful-status language. Rule: .claude/rules/03,08,20.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
have() { local f="$1" label="$2" re="$3"
  if [ ! -f "$f" ]; then echo "FAIL: missing $f ($label)"; fail=1; return; fi
  if LC_ALL=C grep -Eiq -- "$re" "$f"; then echo "OK: $label"; else echo "FAIL: $f missing '$label' (/$re/)"; fail=1; fi
}

AFR="docs/architecture/APPLICATION_FOUNDATION_RULES.md"
MODB="docs/architecture/MODULE_BOUNDARIES.md"
TICM="docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md"
TRACE="docs/quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md"
RCOV="docs/quality/STEP_3_ARCHITECTURE_RULE_COVERAGE.md"
FFC="docs/quality/STEP_3_FITNESS_FUNCTION_CATALOG.md"

# 1. Required architecture docs present and non-empty.
ARCH_DOCS=(
  APPLICATION_ARCHITECTURE_BASELINE REPOSITORY_LAYOUT MODULE_BOUNDARIES MODULE_DEPENDENCY_MATRIX
  DATA_OWNERSHIP_MATRIX TENANCY_ARCHITECTURE IDENTITY_AND_ACCESS_ARCHITECTURE DATABASE_ARCHITECTURE
  EVENT_DRIVEN_ARCHITECTURE OUTBOX_IDEMPOTENCY_RETRY API_AND_WEBHOOK_STANDARDS AI_SERVICE_BOUNDARY
  FRONTEND_ARCHITECTURE ENVIRONMENT_STRATEGY DEPLOYMENT_TOPOLOGY OBSERVABILITY_ARCHITECTURE
  BACKUP_RESTORE_ROLLBACK ARCHITECTURE_FITNESS_FUNCTIONS ARCHITECTURE_OPEN_DECISIONS APPLICATION_FOUNDATION_RULES
)
for d in "${ARCH_DOCS[@]}"; do
  [ -s "docs/architecture/${d}.md" ] || { echo "FAIL: missing/empty docs/architecture/${d}.md"; fail=1; }
done
echo "OK: checked ${#ARCH_DOCS[@]} architecture documents"

# 2. Supporting Step 3 docs present.
STEP3_DOCS=(
  "$TICM" docs/security/STEP_3_THREAT_MODEL.md docs/security/DATA_CLASSIFICATION_AND_HANDLING.md
  docs/security/SECRETS_AND_CREDENTIALS_ARCHITECTURE.md docs/ai/AI_RUNTIME_CONTROL_PLANE.md
  docs/ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md docs/ai/AI_OBSERVABILITY_AND_COST_ARCHITECTURE.md
  docs/integrations/INTEGRATION_BOUNDARY_STANDARDS.md docs/integrations/GOOGLE_BUSINESS_PROFILE_ARCHITECTURE.md
  docs/integrations/DAENGTISIAMS_EVENT_INTEGRATION_ARCHITECTURE.md
  docs/operations/ENVIRONMENT_AND_DEPLOYMENT_BASELINE.md docs/operations/OBSERVABILITY_AND_ALERTING_BASELINE.md
  docs/operations/BACKUP_RESTORE_DR_BASELINE.md "$TRACE" "$RCOV" "$FFC" docs/quality/STEP_3_GO_NO_GO_CRITERIA.md
)
for f in "${STEP3_DOCS[@]}"; do [ -s "$f" ] || { echo "FAIL: missing/empty $f"; fail=1; }; done
echo "OK: checked ${#STEP3_DOCS[@]} supporting Step 3 documents"

# 3. All 17 modules named in MODULE_BOUNDARIES.
for m in PlatformAdmin Identity Tenancy Billing Customer ServiceEvent Survey Campaign Feedback Recovery \
         Reputation Knowledge AI Notification Integration Analytics Audit; do
  LC_ALL=C grep -q -- "$m" "$MODB" || { echo "FAIL: module $m missing from MODULE_BOUNDARIES"; fail=1; }
done
echo "OK: 17 modules present"

# 4. Architecture style + modular monolith decision.
have "docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md" "Laravel modular monolith" "modular monolith"
have "$MODB" "no foreign table mutation rule" "MUST NOT.*(mutate|write).*table|own.*data"

# 5. Tenant isolation: all 14 FF-TEN surfaces in the control matrix.
for n in $(seq -w 1 14); do
  LC_ALL=C grep -q -- "FF-TEN-${n}" "$TICM" || { echo "FAIL: FF-TEN-${n} missing from control matrix"; fail=1; }
done
echo "OK: FF-TEN-01..14 isolation surfaces covered"

# 6. AFR catalog completeness AFR-001..AFR-072.
for n in $(seq -w 1 72); do
  LC_ALL=C grep -q -- "AFR-0*${n}\b" "$AFR" || LC_ALL=C grep -q -- "AFR-$(printf '%03d' "$n")" "$AFR" \
    || { echo "FAIL: AFR-$(printf '%03d' "$n") missing from AFR catalog"; fail=1; }
done
echo "OK: AFR-001..072 present"

# 6b. Cross-reference: every FF id referenced in the AFR catalog MUST be defined in the fitness-functions doc,
#     and the defined FF count MUST equal the declared total (catches typos + count drift, not just presence).
FFDEF="docs/architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md"
defined_ff="$(LC_ALL=C grep -oE 'FF-[A-Z]+-[0-9]+' "$FFDEF" | sort -u)"
ff_count="$(printf '%s\n' "$defined_ff" | grep -c . || true)"
if [ "$ff_count" -eq 45 ]; then echo "OK: 45 fitness functions defined"; else echo "FAIL: $ff_count FF defined (expected 45)"; fail=1; fi
missing_ff=0
for id in $(LC_ALL=C grep -oE 'FF-[A-Z]+-[0-9]+' "$AFR" | sort -u); do
  printf '%s\n' "$defined_ff" | grep -qx "$id" || { echo "FAIL: AFR catalog references undefined $id"; fail=1; missing_ff=1; }
done
[ "$missing_ff" -eq 0 ] && echo "OK: all FF ids referenced by AFRs are defined"
# Every declared FF total must also be reflected in the catalog doc.
LC_ALL=C grep -q "Total: 45" "$FFC" || { echo "FAIL: fitness catalog does not declare Total: 45"; fail=1; }

# 7. ADR 0009..0032 mapped in traceability.
for n in $(seq -w 9 32); do
  LC_ALL=C grep -q -- "00${n}\|0${n}" "$TRACE" || LC_ALL=C grep -q -- "$(printf '%04d' "$n")" "$TRACE" \
    || { echo "FAIL: ADR $(printf '%04d' "$n") not in traceability matrix"; fail=1; }
done
echo "OK: ADR 0009..0032 mapped in traceability"

# 8. Security / AI / reliability semantic coverage.
have "docs/security/STEP_3_THREAT_MODEL.md" "prompt injection threat" "prompt injection"
have "docs/security/DATA_CLASSIFICATION_AND_HANDLING.md" "healthcare MED prohibition" "odontogram|diagnosis|PROHIBITED"
have "docs/ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md" "human approval + anti-gating" "human approval"
have "docs/ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md" "review gating prohibited" "MUST NOT.*gat|no.*gating|anti-gating|Anti-gating"
have "docs/architecture/OUTBOX_IDEMPOTENCY_RETRY.md" "outbox + idempotency + dead-letter" "outbox"
have "docs/architecture/OUTBOX_IDEMPOTENCY_RETRY.md" "no success before provider verification" "provider.*verif|before provider"
have "docs/architecture/AI_SERVICE_BOUNDARY.md" "structured output + manual fallback + kill switch" "structured output"

# 9. Traceability: no orphan critical requirement.
have "$TRACE" "no orphan critical requirement" "Orphan critical requirements: none"

# 10. Rule coverage asserts no gap and has no GAP status cells.
have "$RCOV" "coverage asserts no critical gap" "No critical gap"
if grep -Eiq '\|[[:space:]]*(NOT COVERED|GAP|MISSING|TBD|TODO)[[:space:]]*\|' "$RCOV" 2>/dev/null; then
  echo "FAIL: rule coverage contains an uncovered/gap status cell"; fail=1
fi

# 11. Truthful status: no false implementation/deployment claim; key docs assert NOT STARTED.
for f in docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md docs/quality/STEP_3_GO_NO_GO_CRITERIA.md AGENTS.md; do
  LC_ALL=C grep -q "NOT STARTED" "$f" || { echo "FAIL: $f missing truthful 'NOT STARTED' status"; fail=1; }
done
# Detect AFFIRMATIVE false claims only; exclude prohibitions/negations ("MUST NOT claim", "NOT STARTED", "never").
if LC_ALL=C grep -RInE 'application (is )?(implemented|deployed|production ready|pilot ready)' \
     docs/architecture docs/quality 2>/dev/null \
   | grep -viE 'MUST NOT|NOT STARTED|never|no false|not (yet )?(implemented|deployed)|MUST NOT claim' \
   | grep -q .; then
  echo "FAIL: a Step 3 doc appears to claim application implemented/deployed/ready"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS: Step 3 architecture/ADR/module/isolation/AFR/traceability coverage complete"; else echo "check-step3-coverage: FAILED"; exit 1; fi
