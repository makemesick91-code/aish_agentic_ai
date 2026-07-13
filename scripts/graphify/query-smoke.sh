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

echo "---" | tee -a "$OUT"
if [ "$fail" -eq 0 ]; then
  echo "PASS: 14/14 canonical queries resolved to canonical file paths" | tee -a "$OUT"
else
  echo "query-smoke: FAILED" | tee -a "$OUT"; exit 1
fi
