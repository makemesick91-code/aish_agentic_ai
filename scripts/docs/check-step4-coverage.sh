#!/usr/bin/env bash
# check-step4-coverage.sh — Step 4 (Domain, Branding, Environment, and SaaS Foundation Implementation Planning)
# coverage: document presence, semantic content tokens, AFR-073..104 catalog completeness, truthful planning
# states (no false implementation/ownership/deployment claim), and no-orphan traceability.
# Rule: .claude/rules/21–27; canonical: Master Source v2.4.0 §67–§70, PRD v1.3.0.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
fail=0
have() { # file, human-readable label, regex proving content present
  local f="$1" label="$2" re="$3"
  if [ ! -f "$f" ]; then echo "FAIL: missing $f ($label)"; fail=1; return; fi
  if LC_ALL=C grep -Eiq -- "$re" "$f"; then echo "OK: $label"; else echo "FAIL: $f missing '$label' (/$re/)"; fail=1; fi
}

# 1. Required Step 4 documents present and non-empty.
DOMAIN_DOCS=(DOMAIN_STRATEGY DOMAIN_CANDIDATE_EVALUATION SUBDOMAIN_AND_URL_MATRIX
  DNS_TLS_AND_EMAIL_SECURITY_PLAN DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE OAUTH_REDIRECT_URI_PLAN)
BRAND_DOCS=(BRAND_FOUNDATION BRAND_ARCHITECTURE BRAND_VOICE WORKING_TAGLINE_DECISION
  VISUAL_IDENTITY_BASELINE ACCESSIBILITY_BASELINE LOGO_AND_ASSET_GOVERNANCE)
ENV_DOCS=(ENVIRONMENT_STRATEGY ENVIRONMENT_MATRIX CONFIGURATION_AND_SECRET_MATRIX DATA_POLICY_BY_ENVIRONMENT
  ENVIRONMENT_NAMING_STANDARD ENVIRONMENT_PROMOTION_POLICY LOCAL_DEVELOPMENT_STRATEGY CI_RUNTIME_PLAN
  STAGING_PLAN PILOT_ENVIRONMENT_PLAN PRODUCTION_ENVIRONMENT_PLAN)
DEP_DOCS=(DEPENDENCY_BASELINE DEPENDENCY_APPROVAL_MATRIX SUPPLY_CHAIN_GOVERNANCE UPGRADE_AND_SECURITY_PATCH_POLICY)
PLAN_DOCS=(SAAS_FOUNDATION_IMPLEMENTATION_PLAN SAAS_FOUNDATION_EPIC_CATALOG SAAS_FOUNDATION_SPRINT_ROADMAP
  SAAS_FOUNDATION_DEPENDENCY_MAP SAAS_FOUNDATION_DEFINITION_OF_READY SAAS_FOUNDATION_DEFINITION_OF_DONE
  SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN SAAS_FOUNDATION_RISK_REGISTER SAAS_FOUNDATION_COST_MODEL
  NEXT_IMPLEMENTATION_SPRINT)
OPS_DOCS=(STEP_4_DEPLOYMENT_TARGET_EVALUATION STEP_4_BACKUP_RESTORE_PLAN STEP_4_OBSERVABILITY_PLAN STEP_4_ROLLBACK_PLAN)
QUAL_DOCS=(STEP_4_REQUIREMENTS_TRACEABILITY_MATRIX STEP_4_RULE_COVERAGE STEP_4_VALIDATION_CATALOG STEP_4_GO_WATCH_NO_GO)

for d in "${DOMAIN_DOCS[@]}"; do [ -s "docs/domain/${d}.md" ] || { echo "FAIL: missing/empty docs/domain/${d}.md"; fail=1; }; done
for d in "${BRAND_DOCS[@]}";  do [ -s "docs/brand/${d}.md" ] || { echo "FAIL: missing/empty docs/brand/${d}.md"; fail=1; }; done
for d in "${ENV_DOCS[@]}";    do [ -s "docs/environments/${d}.md" ] || { echo "FAIL: missing/empty docs/environments/${d}.md"; fail=1; }; done
for d in "${DEP_DOCS[@]}";    do [ -s "docs/dependencies/${d}.md" ] || { echo "FAIL: missing/empty docs/dependencies/${d}.md"; fail=1; }; done
for d in "${PLAN_DOCS[@]}";   do [ -s "docs/planning/${d}.md" ] || { echo "FAIL: missing/empty docs/planning/${d}.md"; fail=1; }; done
for d in "${OPS_DOCS[@]}";    do [ -s "docs/operations/${d}.md" ] || { echo "FAIL: missing/empty docs/operations/${d}.md"; fail=1; }; done
for d in "${QUAL_DOCS[@]}";   do [ -s "docs/quality/${d}.md" ] || { echo "FAIL: missing/empty docs/quality/${d}.md"; fail=1; }; done
[ -s "docs/brand/tokens/brand-tokens.v1.json" ] || { echo "FAIL: missing/empty docs/brand/tokens/brand-tokens.v1.json"; fail=1; }
echo "OK: checked Step 4 document presence"

# 2. Domain governance semantics.
DS="docs/domain/DOMAIN_STRATEGY.md"; DCE="docs/domain/DOMAIN_CANDIDATE_EVALUATION.md"
have "$DS" "product name unchanged" "Aish Agentic AI"
have "$DS" "preferred primary domain" "preferred|primary domain"
have "$DS" "fallback domain plan" "fallback"
have "$DS" "domain not owned / no false claim" "NOT OWNED|not owned|NOT CLAIMED|no.*ownership claim"
have "$DCE" "point-in-time availability evidence" "point-in-time|2026-07-13|RDAP"
have "$DCE" "candidate aishagentic.ai evaluated" "aishagentic\.ai"
have "docs/domain/SUBDOMAIN_AND_URL_MATRIX.md" "app subdomain" "app\."
have "docs/domain/SUBDOMAIN_AND_URL_MATRIX.md" "non-production subdomains" "staging\.|dev\.|pilot\."
have "docs/domain/DNS_TLS_AND_EMAIL_SECURITY_PLAN.md" "SPF/DKIM/DMARC" "SPF|DKIM|DMARC"
have "docs/domain/DNS_TLS_AND_EMAIL_SECURITY_PLAN.md" "DNSSEC + TLS" "DNSSEC|TLS"
have "docs/domain/DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md" "org-owned + MFA + transfer lock" "organization|transfer lock|MFA"
have "docs/domain/OAUTH_REDIRECT_URI_PLAN.md" "exact-match redirect governance" "exact.?match|redirect uri|redirect URI"

# 3. Brand governance semantics.
have "docs/brand/BRAND_FOUNDATION.md" "official descriptor, not just survey/review tool" "Customer Experience|descriptor"
have "docs/brand/BRAND_ARCHITECTURE.md" "Aish Tech Solution parent" "Aish Tech Solution"
have "docs/brand/WORKING_TAGLINE_DECISION.md" "working baseline status not trademark" "APPROVED WORKING BASELINE"
have "docs/brand/BRAND_VOICE.md" "voice do/don't + no false AI claims" "non-defensive|do not|Don.t|autonomy"
have "docs/brand/VISUAL_IDENTITY_BASELINE.md" "planning tokens not implemented" "PLANNING TOKENS — NOT IMPLEMENTED IN UI|PLANNING BASELINE — NOT IMPLEMENTED"
have "docs/brand/ACCESSIBILITY_BASELINE.md" "WCAG contrast target" "WCAG|contrast"
have "docs/brand/LOGO_AND_ASSET_GOVERNANCE.md" "no final-brand claim without approval" "no.*final|not.*final|approval"
have "docs/brand/BRAND_FOUNDATION.md" "no guaranteed rating / no fully autonomous claim" "MUST NOT.*guarantee|no guaranteed|MUST NOT.*fully autonomous|not.*fully autonomous"

# 4. Environment governance semantics.
have "docs/environments/ENVIRONMENT_MATRIX.md" "all six environments" "local"
for e in local test CI staging pilot production; do
  LC_ALL=C grep -qi -- "$e" "docs/environments/ENVIRONMENT_MATRIX.md" || { echo "FAIL: environment '$e' missing from ENVIRONMENT_MATRIX"; fail=1; }
done
have "docs/environments/DATA_POLICY_BY_ENVIRONMENT.md" "synthetic data default" "synthetic"
have "docs/environments/DATA_POLICY_BY_ENVIRONMENT.md" "no production data in local/test/CI" "MUST NOT.*production data|production data MUST NOT|no production data"
have "docs/environments/ENVIRONMENT_MATRIX.md" "database/redis/queue/storage isolation" "isolation"
have "docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md" "no secret in git" "MUST NOT.*(commit|be committed)|no secret"
have "docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md" "env-specific secret separation" "environment-specific|per-environment secret|secret separation"
have "docs/environments/ENVIRONMENT_PROMOTION_POLICY.md" "no direct unreviewed prod deploy" "MUST NOT.*direct|no direct.*(prod|pilot)|unreviewed"
have "docs/environments/LOCAL_DEVELOPMENT_STRATEGY.md" "recommended + fallback local baseline" "recommended|fallback"
have "docs/environments/CI_RUNTIME_PLAN.md" "no fake runtime CI" "no fake|MUST NOT.*fake|not.*fabricate"
have "docs/environments/PILOT_ENVIRONMENT_PLAN.md" "pilot not deployed" "NOT STARTED|not deployed|PLANNED"
have "docs/environments/PRODUCTION_ENVIRONMENT_PLAN.md" "production not deployed" "NOT STARTED|not deployed|PLANNED TOPOLOGY — NOT DEPLOYED"

# 5. Dependency governance semantics.
have "docs/dependencies/DEPENDENCY_BASELINE.md" "Laravel 12 baseline" "Laravel 12"
have "docs/dependencies/DEPENDENCY_BASELINE.md" "no package installed / no lock" "no.*(install|lock)|MUST NOT.*install|not installed"
have "docs/dependencies/DEPENDENCY_APPROVAL_MATRIX.md" "approval status vocabulary" "APPROVED FOR IMPLEMENTATION|EVALUATE DURING IMPLEMENTATION"
have "docs/dependencies/SUPPLY_CHAIN_GOVERNANCE.md" "typosquat + SBOM + official registry" "typosquat|SBOM|official (registry|source)"
have "docs/dependencies/UPGRADE_AND_SECURITY_PATCH_POLICY.md" "pinning + emergency patch" "pin|emergency"

# 6. SaaS Foundation implementation plan semantics.
IPL="docs/planning/SAAS_FOUNDATION_IMPLEMENTATION_PLAN.md"; EPC="docs/planning/SAAS_FOUNDATION_EPIC_CATALOG.md"
for ep in $(seq -w 1 16); do
  LC_ALL=C grep -q -- "EPIC-SF-${ep}" "$EPC" || { echo "FAIL: EPIC-SF-${ep} missing from epic catalog"; fail=1; }
done
echo "OK: EPIC-SF-01..16 present"
have "$IPL" "implementation sequence" "sequence"
have "$IPL" "no application implementation claim" "NOT STARTED|planning only|not.*implement"
have "docs/planning/SAAS_FOUNDATION_DEFINITION_OF_READY.md" "definition of ready" "Definition of Ready|Ready"
have "docs/planning/SAAS_FOUNDATION_DEFINITION_OF_DONE.md" "definition of done + evidence" "Definition of Done|evidence"
have "docs/planning/SAAS_FOUNDATION_SPRINT_ROADMAP.md" "sprint plan with GO/WATCH/NO-GO" "SPRINT-SF|GO/WATCH/NO-GO|GO / WATCH / NO-GO"
have "docs/planning/NEXT_IMPLEMENTATION_SPRINT.md" "first sprint selected" "SPRINT-SF-0|first.*sprint|Sprint Zero"
have "docs/planning/SAAS_FOUNDATION_TEST_AND_EVIDENCE_PLAN.md" "tenant isolation + security tests" "tenant isolation|isolation test"
have "docs/planning/SAAS_FOUNDATION_RISK_REGISTER.md" "risk register" "risk"
have "docs/planning/SAAS_FOUNDATION_COST_MODEL.md" "cost categories" "cost"

# 7. Operations Step 4 semantics.
have "docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md" "dedicated isolated pilot compute class" "dedicated|isolated"
have "docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md" "no shared DB/redis/pool with DaengtisiaMS" "DaengtisiaMS|separate"
have "docs/operations/STEP_4_ROLLBACK_PLAN.md" "rollback plan" "rollback"

# 8. AFR catalog completeness AFR-073..AFR-104.
AFR="docs/architecture/APPLICATION_FOUNDATION_RULES.md"
for n in $(seq -w 73 104); do
  LC_ALL=C grep -q -- "AFR-$(printf '%03d' "$((10#$n))")" "$AFR" || { echo "FAIL: AFR-$(printf '%03d' "$((10#$n))") missing from AFR catalog"; fail=1; }
done
echo "OK: AFR-073..104 present"

# 9. Truthful planning states: key docs assert NOT STARTED and required planning labels present somewhere.
for f in "$DS" "$IPL" docs/quality/STEP_4_GO_WATCH_NO_GO.md; do
  LC_ALL=C grep -q "NOT STARTED" "$f" || { echo "FAIL: $f missing truthful 'NOT STARTED' status"; fail=1; }
done
LC_ALL=C grep -Rq "PLANNING BASELINE — NOT IMPLEMENTED" docs/planning docs/environments docs/brand docs/domain 2>/dev/null \
  || { echo "FAIL: 'PLANNING BASELINE — NOT IMPLEMENTED' label not found in Step 4 docs"; fail=1; }
# Detect AFFIRMATIVE false claims only; exclude prohibitions/negations.
if LC_ALL=C grep -RInE '(domain (is )?(owned|purchased|registered by us))|(application (is )?(implemented|deployed))' \
     docs/domain docs/planning docs/environments 2>/dev/null \
   | grep -viE 'MUST NOT|NOT owned|not owned|NOT STARTED|never|no false|not (yet )?(implemented|deployed|owned)|MUST NOT claim|available|attest|does .?.?not|readiness only|not be (owned|deployed)|prohibit' \
   | grep -q .; then
  echo "FAIL: a Step 4 doc appears to claim domain owned or application implemented/deployed"; fail=1
fi

# 10. Traceability: no orphan critical requirement; rule coverage no gap.
have "docs/quality/STEP_4_REQUIREMENTS_TRACEABILITY_MATRIX.md" "no orphan critical requirement" "Orphan critical requirements: none"
have "docs/quality/STEP_4_RULE_COVERAGE.md" "coverage asserts no critical gap" "No critical gap"
if grep -Eiq '\|[[:space:]]*(NOT COVERED|GAP|MISSING|TBD|TODO)[[:space:]]*\|' docs/quality/STEP_4_RULE_COVERAGE.md 2>/dev/null; then
  echo "FAIL: Step 4 rule coverage contains an uncovered/gap status cell"; fail=1
fi

if [ "$fail" -eq 0 ]; then echo "PASS: Step 4 domain/brand/environment/dependency/SaaS-foundation coverage complete"; else echo "check-step4-coverage: FAILED"; exit 1; fi
