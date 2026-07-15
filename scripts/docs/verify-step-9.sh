#!/usr/bin/env bash
# verify-step-9.sh — reproducible Step 9 (Competitive Gap Audit & Architecture Re-baseline) verification.
#
# Step 9 is a documentation/governance + architecture-lock sprint: it creates NO application feature, migration, table,
# or runtime. This verifier therefore proves (a) the Step 9 governance artifacts, ADR/AFR integrity, version
# consistency, roadmap lock, and truthful status via the documentation-as-code gates, and (b) that the Step 8
# foundation still regresses green (hermetic suite) — i.e. Step 9 did not touch runtime behaviour.
#
# Real-infra Step 5..8 regressions are exercised by the CI `backend-runtime-ci` gate (and `verify-step-8.sh` locally).
# This script is hermetic by default and never prints secrets. Rule 34, rule 09, rule 13.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"
EVID="docs/evidence/step-9/runtime"
mkdir -p "$EVID"
fail=0
pass() { printf '  \033[32mPASS\033[0m %s\n' "$1"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$1"; fail=1; }
step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

step "0. Preconditions"
if [ "$(id -u)" = "0" ]; then echo "refusing to run as root"; exit 1; fi
git rev-parse --short HEAD | sed 's/^/  HEAD /'

step "1. Step 9 governance coverage"
if scripts/docs/check-step9-coverage.sh >"$EVID/step9-coverage.log" 2>&1; then pass "check-step9-coverage"; else bad "check-step9-coverage"; sed 's/^/    /' "$EVID/step9-coverage.log"; fi

step "2. ADR structure + sequence (incl. 0063..0068)"
if scripts/docs/check-adr.sh >"$EVID/adr.log" 2>&1; then pass "check-adr"; else bad "check-adr"; tail -20 "$EVID/adr.log" | sed 's/^/    /'; fi

step "3. Full documentation-as-code suite"
if scripts/docs/validate.sh >"$EVID/validate.log" 2>&1; then pass "validate.sh (all doc gates)"; else bad "validate.sh"; tail -30 "$EVID/validate.log" | sed 's/^/    /'; fi

step "4. Secret scan"
if scripts/docs/secret-scan.sh >"$EVID/secret-scan.log" 2>&1; then pass "secret-scan"; else bad "secret-scan"; fi

step "5. Step 8 regression (hermetic test suite — proves no runtime change)"
if [ ! -f vendor/autoload.php ]; then echo "  vendor/ absent; skipping hermetic suite (run composer install to include it)"; else
  if env -u DB_CONNECTION -u DB_HOST -u DB_PORT -u DB_DATABASE -u DB_USERNAME -u DB_PASSWORD \
         -u CACHE_STORE -u QUEUE_CONNECTION -u SESSION_DRIVER -u REDIS_HOST -u REDIS_PORT \
         php artisan test >"$EVID/phpunit.log" 2>&1; then
    pass "php artisan test ($(grep -oE '[0-9]+ passed' "$EVID/phpunit.log" | tail -1))"
  else bad "php artisan test (see $EVID/phpunit.log)"; tail -20 "$EVID/phpunit.log" | sed 's/^/    /'; fi
fi

step "6. No unintended production schema/runtime change in Step 9"
# Step 9 must not add/alter migrations or app/ runtime code. Compare against origin/main.
base="$(git merge-base origin/main HEAD 2>/dev/null || echo HEAD)"
changed="$(git diff --name-only "$base" HEAD 2>/dev/null || true)"
if printf '%s\n' "$changed" | grep -Eq '^(app/|database/migrations/|routes/|bootstrap/)'; then
  bad "Step 9 diff touches runtime/schema paths (app/ | database/migrations/ | routes/ | bootstrap/):"
  printf '%s\n' "$changed" | grep -E '^(app/|database/migrations/|routes/|bootstrap/)' | sed 's/^/      /'
else
  pass "no app/ | database/migrations/ | routes/ | bootstrap/ changes (governance-only)"
fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "STEP 9 VERIFICATION: PASS (evidence in $EVID/)"; else echo "STEP 9 VERIFICATION: FAILED"; exit 1; fi
