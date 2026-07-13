#!/usr/bin/env bash
# query-smoke.sh — canonical query-smoke over the derived documentation index. Each query MUST resolve to
# an existing canonical file that actually contains the relevant content. Results -> docs/evidence/graphify/.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
EVID="docs/evidence/graphify"
mkdir -p "$EVID"
OUT="$EVID/query-smoke.txt"
: > "$OUT"
fail=0

# query | expected file | grep term proving the answer is present
check() {
  local q="$1" path="$2" term="$3"
  if [ -f "$path" ] && LC_ALL=C grep -Eiq -- "$term" "$path"; then
    printf 'PASS | %-58s | %s\n' "$q" "$path" | tee -a "$OUT"
  else
    printf 'FAIL | %-58s | %s (missing/term:%s)\n' "$q" "$path" "$term" | tee -a "$OUT"
    fail=1
  fi
}

check "official product name" \
      "docs/canonical/MASTER_SOURCE.md" "Aish Agentic AI"
check "mandatory tenant-isolation surfaces" \
      ".claude/rules/03-multi-tenant-and-branch-isolation.md" "tenant isolation|isolation on"
check "actions requiring human approval" \
      "docs/ai/HUMAN_APPROVAL_MATRIX.md" "Requires human approval"
check "prohibited in Google Review workflows" \
      "docs/integrations/google/GOOGLE_REVIEW_POLICY.md" "Prohibited|no gating"
check "gates required before a GO tag" \
      "docs/quality/RELEASE_GATES.md" "Documentation-foundation gates|GO tag"
check "canonical doc when PRD conflicts with Master Source" \
      "docs/canonical/DOCUMENT_AUTHORITY.md" "Master Source wins"

# --- Step 2 canonical query smoke (persona & pilot use cases) ---
check "primary pilot personas" \
      "docs/product/PILOT_PERSONA_MATRIX.md" "Business Owner|Reputation Approver"
check "recommended pilot branch" \
      "docs/product/PERSONA_AND_PILOT_USE_CASES.md" "Daengtisia Pusat"
check "invitation frequency cap" \
      "docs/integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md" "14 (calendar )?day|frequency cap"
check "when human approval is mandatory" \
      "docs/ai/PILOT_AI_HUMAN_APPROVAL_RULES.md" "human approval"
check "prohibited healthcare data" \
      "docs/security/PILOT_DATA_BOUNDARY.md" "odontogram|diagnosis"
check "use case when AI or provider fails" \
      "docs/ai/PILOT_MANUAL_FALLBACK.md" "manual|fallback"
check "pilot hard gates" \
      "docs/product/PILOT_SUCCESS_METRICS.md" "cross-tenant|hard (safety|gate)"
check "roadmap step after Step 2" \
      "docs/product/ROADMAP.md" "Step 3"

# --- Step 3 canonical query smoke (application architecture & ADRs) ---
check "canonical application architecture style" \
      "docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md" "modular monolith"
check "module owning survey response" \
      "docs/architecture/MODULE_BOUNDARIES.md" "Feedback"
check "module owning Google OAuth credentials" \
      "docs/architecture/DATA_OWNERSHIP_MATRIX.md" "google_connections"
check "tenant context into queue jobs" \
      "docs/architecture/TENANCY_ARCHITECTURE.md" "rehydrate|context in payload|carry"
check "rule preventing cache leakage" \
      "docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md" "Cache|prefixed"
check "VisitCompleted to survey invitation" \
      "docs/integrations/DAENGTISIAMS_EVENT_INTEGRATION_ARCHITECTURE.md" "VisitCompleted"
check "feedback to recovery ticket" \
      "docs/architecture/EVENT_DRIVEN_ARCHITECTURE.md" "HighRiskFeedbackDetected|Recovery"
check "review to approved public reply" \
      "docs/integrations/GOOGLE_BUSINESS_PROFILE_ARCHITECTURE.md" "Human approval"
check "AI redaction and human approval" \
      "docs/ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md" "redact|human approval"
check "outbox and idempotency strategy" \
      "docs/architecture/OUTBOX_IDEMPOTENCY_RETRY.md" "outbox|idempoten"
check "ADR governing frontend" \
      "docs/decisions/adr/0018-frontend-architecture.md" "Blade"
check "ADR governing deployment topology" \
      "docs/decisions/adr/0032-initial-deployment-topology-and-scale-path.md" "topology"
check "application implementation status" \
      "docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md" "NOT STARTED"
check "roadmap step after Step 3" \
      "docs/product/ROADMAP.md" "Step 4"

# --- Step 4 canonical query smoke (domain/branding/environment/SaaS foundation planning) ---
check "preferred domain candidate" \
      "docs/domain/DOMAIN_STRATEGY.md" "aishagentic\.ai"
check "is the domain owned" \
      "docs/domain/DOMAIN_OWNERSHIP_AND_RENEWAL_GOVERNANCE.md" "NOT OWNED|not owned"
check "fallback domain" \
      "docs/domain/DOMAIN_STRATEGY.md" "fallback"
check "canonical app subdomain" \
      "docs/domain/SUBDOMAIN_AND_URL_MATRIX.md" "app\."
check "working tagline" \
      "docs/brand/WORKING_TAGLINE_DECISION.md" "APPROVED WORKING BASELINE"
check "brand voice" \
      "docs/brand/BRAND_VOICE.md" "non-defensive"
check "environment list" \
      "docs/environments/ENVIRONMENT_MATRIX.md" "staging"
check "data allowed in staging" \
      "docs/environments/DATA_POLICY_BY_ENVIRONMENT.md" "synthetic|anonymized"
check "production data allowed in CI" \
      "docs/environments/DATA_POLICY_BY_ENVIRONMENT.md" "MUST NOT|no production data"
check "recommended local development strategy" \
      "docs/environments/LOCAL_DEVELOPMENT_STRATEGY.md" "Docker Compose"
check "pilot deployment class" \
      "docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md" "dedicated"
check "is the application deployed" \
      "docs/environments/PRODUCTION_ENVIRONMENT_PLAN.md" "NOT STARTED|NOT DEPLOYED"
check "dependency approval status" \
      "docs/dependencies/DEPENDENCY_APPROVAL_MATRIX.md" "APPROVED FOR IMPLEMENTATION"
check "first SaaS Foundation implementation sprint" \
      "docs/planning/NEXT_IMPLEMENTATION_SPRINT.md" "SPRINT-SF-0"
check "rule forbidding secret in repository" \
      ".claude/rules/24-configuration-and-secrets.md" "secret is ever committed|committed secret"
check "rule forbidding false domain ownership claim" \
      ".claude/rules/21-domain-and-dns-governance.md" "ownership \*{0,2}MUST NOT\*{0,2} be claimed"
check "brand token accessibility target" \
      "docs/brand/tokens/brand-tokens.v1.json" "WCAG"
check "roadmap step after Step 4" \
      "docs/product/ROADMAP.md" "Step 5"

# --- CICD-CTRL-1 canonical query smoke (safe CI runtime control) ---
check "draft PR CI policy" \
      "docs/ci/DRAFT_TO_RELEASE_WORKFLOW.md" "draft.*fast CI|DRAFT PR"
check "when the full CI runs" \
      "docs/ci/CI_EVENT_AND_TRIGGER_MATRIX.md" "ready_for_review"
check "may a full CI result be reused from an old SHA" \
      ".claude/rules/28-safe-ci-runtime-control.md" "exact tested commit SHA"
check "what happens when a commit changes after full CI" \
      "docs/ci/DRAFT_TO_RELEASE_WORKFLOW.md" "new.*full CI"
check "how stale runs are cancelled" \
      "docs/ci/CI_ARCHITECTURE.md" "cancel-in-progress"
check "does a feature push run full CI" \
      "docs/ci/CI_EVENT_AND_TRIGGER_MATRIX.md" "no.*feature-branch.*push"
check "how changed files are routed" \
      "docs/ci/CI_CHANGE_CLASSIFICATION.md" "category|routing"
check "what happens for unknown changes" \
      "docs/ci/CI_CHANGE_CLASSIFICATION.md" "fail.closed|full safe suite"
check "the stable required check" \
      "docs/ci/REQUIRED_CHECK_GOVERNANCE.md" "pr-ci / Required Gate"
check "may a path-filtered workflow be a required check" \
      "docs/ci/CI_CHANGE_CLASSIFICATION.md" "pending"
check "what runs after merge to main" \
      "docs/ci/POST_MERGE_VERIFICATION.md" "integrity verification only"
check "what runs after a tag" \
      "docs/ci/POST_TAG_EVIDENCE_POLICY.md" "no full CI"
check "where post-tag evidence is stored" \
      "docs/ci/POST_TAG_EVIDENCE_POLICY.md" "GitHub Release"
check "when a full CI may rerun" \
      "docs/ci/CI_RUN_BUDGET.md" "rerun"
check "rule forbidding skip-ci on mandatory checks" \
      ".claude/rules/28-safe-ci-runtime-control.md" "skip directives|\\[skip ci\\]"
check "rule forbidding security gate removal for speed" \
      ".claude/rules/28-safe-ci-runtime-control.md" "removed for speed"
check "exact target GO tag for CICD-CTRL-1" \
      "docs/release/CICD_CTRL_1_RELEASE_MANIFEST.md" "aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go"
check "current application implementation status" \
      "docs/ci/CI_ARCHITECTURE.md" "NOT STARTED"

echo "---" | tee -a "$OUT"
if [ "$fail" -eq 0 ]; then
  echo "PASS: 64/64 canonical queries resolved to canonical file paths" | tee -a "$OUT"
else
  echo "query-smoke: FAILED" | tee -a "$OUT"; exit 1
fi
