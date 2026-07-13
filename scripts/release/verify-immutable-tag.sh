#!/usr/bin/env bash
# verify-immutable-tag.sh — exact-match verification for an annotated immutable GO tag.
#
# Proves local main == origin/main == merge commit == local tag peeled commit ==
# remote tag peeled commit, and that all prior immutable tags are unchanged. Does
# NOT run full CI (CI-PRINCIPLE-12) and NEVER moves/deletes a tag (read-only).
# Writes lightweight artifacts to the given output dir.
#
# Usage: scripts/release/verify-immutable-tag.sh <TAG> [OUTDIR]
# Rule: .claude/rules/13, 28.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

TAG="${1:-}"
OUT="${2:-docs/evidence/cicd-ctrl-1/release}"
if [ -z "$TAG" ]; then echo "FAIL: usage: verify-immutable-tag.sh <TAG> [OUTDIR]" >&2; exit 2; fi
mkdir -p "$OUT"

git fetch --tags --quiet origin || true

LOCAL_MAIN="$(git rev-parse main 2>/dev/null || true)"
ORIGIN_MAIN="$(git rev-parse origin/main 2>/dev/null || true)"
TAG_OBJ="$(git rev-parse "$TAG" 2>/dev/null || true)"
TAG_PEELED="$(git rev-parse "${TAG}^{}" 2>/dev/null || true)"
REMOTE_TAG_OBJ="$(git ls-remote origin "refs/tags/${TAG}" 2>/dev/null | awk '{print $1}')"
REMOTE_TAG_PEELED="$(git ls-remote origin "refs/tags/${TAG}^{}" 2>/dev/null | awk '{print $1}')"

fail=0
eq() { [ -n "$1" ] && [ "$1" = "$2" ]; }

eq "$LOCAL_MAIN" "$ORIGIN_MAIN"      || { echo "FAIL: local main != origin/main"; fail=1; }
eq "$TAG_PEELED" "$LOCAL_MAIN"       || { echo "FAIL: tag peeled commit != local main"; fail=1; }
eq "$REMOTE_TAG_PEELED" "$TAG_PEELED"|| { echo "FAIL: remote tag peeled != local tag peeled"; fail=1; }

# Annotated (not lightweight) is a MUST: the tag object differs from the commit it points to.
if [ -z "$TAG_OBJ" ]; then
  echo "FAIL: $TAG not found locally"; fail=1
elif [ "$TAG_OBJ" = "$TAG_PEELED" ]; then
  echo "FAIL: $TAG is lightweight (tag object == commit); an annotated tag is required (rule 13, 28)"; fail=1
fi

# Prior immutable tags unchanged: pin against the recorded known-good peeled commits (prefix match),
# and treat a MISSING prior tag (local or remote) as a failure — a deleted/moved tag must not pass.
declare -A PRIOR_KNOWN=(
  [aish-agentic-ai-docs-foundation-v1.0.0-go]="ba1c80f"
  [aish-agentic-ai-step-2-persona-pilot-v1.0.0-go]="abf1d00"
  [aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go]="764a484"
  [aish-agentic-ai-step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go]="3db6ed8"
)
for t in "${!PRIOR_KNOWN[@]}"; do
  known="${PRIOR_KNOWN[$t]}"
  l="$(git rev-parse "${t}^{}" 2>/dev/null || true)"
  r="$(git ls-remote origin "refs/tags/${t}^{}" 2>/dev/null | awk '{print $1}')"
  if [ -z "$l" ]; then echo "FAIL: prior tag $t missing locally"; fail=1; continue; fi
  if [ -z "$r" ]; then echo "FAIL: prior tag $t missing on origin (deleted/moved?)"; fail=1; continue; fi
  if [ "$l" != "$r" ]; then echo "FAIL: prior tag $t changed (local $l != remote $r)"; fail=1; continue; fi
  case "$l" in
    "$known"*) : ;;  # matches recorded known-good peeled commit
    *) echo "FAIL: prior tag $t peeled $l != recorded known-good ${known}…"; fail=1 ;;
  esac
done

{
  echo "tag=$TAG"
  echo "local_main=$LOCAL_MAIN"
  echo "origin_main=$ORIGIN_MAIN"
  echo "tag_object=$TAG_OBJ"
  echo "tag_peeled=$TAG_PEELED"
  echo "remote_tag_object=$REMOTE_TAG_OBJ"
  echo "remote_tag_peeled=$REMOTE_TAG_PEELED"
  echo "exact_match=$([ "$fail" -eq 0 ] && echo true || echo false)"
} | tee "$OUT/tag-verification.txt"

python3 - "$OUT" "$TAG" "$LOCAL_MAIN" "$ORIGIN_MAIN" "$TAG_OBJ" "$TAG_PEELED" "$REMOTE_TAG_OBJ" "$REMOTE_TAG_PEELED" "$fail" <<'PY'
import json, sys
out, tag, lm, om, to, tp, rto, rtp, fail = sys.argv[1:10]
json.dump({
  "tag": tag, "local_main": lm, "origin_main": om,
  "tag_object": to, "tag_peeled": tp,
  "remote_tag_object": rto, "remote_tag_peeled": rtp,
  "exact_match": fail == "0",
}, open(out + "/tag-verification.json", "w"), indent=2)
PY

if [ "$fail" -eq 0 ]; then echo "PASS: immutable tag exact-match verified"; else echo "verify-immutable-tag: FAILED"; exit 1; fi
