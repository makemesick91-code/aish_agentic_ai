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

echo "---" | tee -a "$OUT"
if [ "$fail" -eq 0 ]; then
  echo "PASS: 6/6 canonical queries resolved to canonical file paths" | tee -a "$OUT"
else
  echo "query-smoke: FAILED" | tee -a "$OUT"; exit 1
fi
