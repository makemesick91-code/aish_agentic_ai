#!/usr/bin/env bash
# classify-changes.sh — deterministic, fail-closed change classifier for CICD-CTRL-1.
#
# Maps the set of changed files between a base and head ref to CI categories and
# emits machine-readable routing flags. Unknown / unclassified / mixed-sensitive
# changes fail CLOSED to the full safe suite (CI-PRINCIPLE-10, AFR-CI internal
# path routing). This runs INSIDE the workflow — it is NOT a top-level `paths`
# filter on a mandatory workflow (CI-PRINCIPLE-09).
#
# Inputs (env or args), all optional:
#   BASE_SHA   base ref/sha (default: merge-base of origin/main and HEAD, else HEAD^)
#   HEAD_SHA   head ref/sha (default: HEAD)
#   GITHUB_OUTPUT  when set, key=value routing flags are appended for Actions
# Args: classify-changes.sh [BASE_SHA] [HEAD_SHA]
#
# Output: writes JSON to docs/evidence/cicd-ctrl-1/change-classifier/last-classification.json
# and prints key=value flags to stdout. Deterministic; no secrets emitted.
#
# Rule: .claude/rules/28. Safe for: PR synchronize, shallow checkout, renames,
# deleted files, filenames with spaces, and the first-commit boundary.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

BASE_SHA="${1:-${BASE_SHA:-}}"
HEAD_SHA="${2:-${HEAD_SHA:-HEAD}}"

# Resolve a sensible base if none supplied.
if [ -z "$BASE_SHA" ]; then
  if git rev-parse --verify -q origin/main >/dev/null 2>&1; then
    BASE_SHA="$(git merge-base origin/main "$HEAD_SHA" 2>/dev/null || true)"
  fi
  if [ -z "$BASE_SHA" ]; then
    BASE_SHA="$(git rev-parse --verify -q "${HEAD_SHA}^" 2>/dev/null || true)"
  fi
fi

# Compute changed files (NUL-delimited => space-safe). Fall back to full tree on
# an unusable base boundary (fail closed).
files=()
if [ -n "$BASE_SHA" ] && git rev-parse --verify -q "$BASE_SHA" >/dev/null 2>&1; then
  # --no-renames splits a rename into (delete old path + add new path) so a sensitive
  # old path (e.g. app/** or .github/workflows/**) still re-classifies and fails closed,
  # instead of a rename emitting only the new (possibly docs) path (fail-open).
  while IFS= read -r -d '' f; do files+=("$f"); done \
    < <(git diff --name-only -z --no-renames --diff-filter=ACMD "$BASE_SHA" "$HEAD_SHA" 2>/dev/null || true)
else
  # No usable base => cannot scope safely => classify as unknown (fail closed).
  files=("__UNRESOLVED_BASE__")
fi

# Category flags.
declare -A CAT=()
mark() { CAT["$1"]=1; }

classify_one() {
  local f="$1"
  case "$f" in
    __UNRESOLVED_BASE__)                         mark unknown ;;
    .github/workflows/*|scripts/ci/*|scripts/release/*) mark workflow ;;
    .claude/*|.agents/*|.codex/*|AGENTS.md|CLAUDE.md|*/AGENTS.md) mark governance ;;
    docs/ci/*)                                   mark documentation; mark workflow ;;
    docs/security/*|docs/evidence/*/secret*|SECURITY.md) mark documentation; mark security ;;
    docs/ai/*)                                   mark documentation; mark ai ;;
    docs/integrations/*)                         mark documentation; mark integration ;;
    docs/canonical/*|docs/decisions/*|docs/architecture/*) mark documentation; mark governance ;;
    docs/*)                                      mark documentation ;;
    scripts/docs/*|scripts/graphify/*|scripts/codex/*|scripts/hooks/*) mark governance ;;
    scripts/runtime/*)                           mark backend ;;
    app/*|routes/*|bootstrap/*)                  mark backend ;;
    artisan|phpstan.neon|phpstan.neon.dist|phpunit.xml|phpunit.xml.dist) mark backend ;;
    Makefile|.env.example)                        mark backend ;;
    config/*)                                    mark backend; mark security ;;
    database/*|*/migrations/*)                   mark database ;;
    resources/*|vite.config.js|tailwind.config.*|postcss.config.*) mark frontend ;;
    package.json|package-lock.json|pnpm-lock.yaml|yarn.lock) mark frontend; mark dependency ;;
    composer.json|composer.lock)                 mark backend; mark dependency ;;
    tests/Security/*)                            mark security; mark test ;;
    tests/*)                                     mark test ;;
    infrastructure/*|deploy/*|Dockerfile|docker-compose*.yml) mark infrastructure ;;
    graphify.yaml|.mcp.json|.gitignore|.editorconfig|CHANGELOG.md|CONTRIBUTING.md|README.md|LICENSE) mark governance ;;
    "")                                          : ;;   # empty line guard
    *)                                           mark unknown ;;  # fail closed
  esac
}

for f in "${files[@]}"; do classify_one "$f"; done

# Empty diff => documentation-only no-op is unsafe to assume; treat as unknown=full.
if [ "${#CAT[@]}" -eq 0 ]; then mark unknown; fi

# Fail-closed superset triggers: any of these => full safe suite.
FULL=false
for c in unknown security backend database dependency integration infrastructure release; do
  [ "${CAT[$c]:-0}" = "1" ] && FULL=true
done
# Mixed (>=3 categories) also runs the full safe suite.
if [ "${#CAT[@]}" -ge 3 ]; then FULL=true; fi

# Concrete suites available TODAY (application runtime is NOT STARTED):
#   documentation  -> full documentation-as-code gates
#   workflow_security -> workflow topology + security validators
# Runtime suites (backend/frontend/database) are routed but recorded as
# NOT-YET-AVAILABLE until the application exists (WATCH; AFR-CI, rule 23 AFR-093).
run_documentation=true          # every change runs the documentation gates (cheap, canonical)
run_workflow_security=false
[ "${CAT[workflow]:-0}" = "1" ] && run_workflow_security=true
[ "$FULL" = "true" ] && run_workflow_security=true

# Ordered category list.
cats="$(for k in "${!CAT[@]}"; do echo "$k"; done | sort | paste -sd, -)"
[ -z "$cats" ] && cats="unknown"

# JSON evidence (deterministic; sorted keys).
OUTDIR="docs/evidence/cicd-ctrl-1/change-classifier"
mkdir -p "$OUTDIR"
{
  echo "{"
  echo "  \"base_sha\": \"${BASE_SHA:-}\","
  echo "  \"head_sha\": \"$(git rev-parse --verify -q "$HEAD_SHA" 2>/dev/null || echo "$HEAD_SHA")\","
  echo "  \"changed_file_count\": ${#files[@]},"
  echo "  \"categories\": \"$cats\","
  echo "  \"full_safe_suite\": $FULL,"
  echo "  \"run_documentation\": $run_documentation,"
  echo "  \"run_workflow_security\": $run_workflow_security"
  echo "}"
} > "$OUTDIR/last-classification.json"

emit() {
  echo "$1"
  if [ -n "${GITHUB_OUTPUT:-}" ]; then echo "$1" >> "$GITHUB_OUTPUT"; fi
}
emit "categories=$cats"
emit "full_safe_suite=$FULL"
emit "run_documentation=$run_documentation"
emit "run_workflow_security=$run_workflow_security"
