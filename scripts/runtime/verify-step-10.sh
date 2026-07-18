#!/usr/bin/env bash
# verify-step-10.sh — prove the Step 10 Customer 360 Foundation works against real PostgreSQL +
# Redis from a clean checkout, without regressing Step 5, Step 6, SPRINT-SF-05, Step 7, or Step 8.
#
# It migrates, re-runs the SaaS-core, SF-05, Step 7, and Step 8 real-infra checks (regression), then
# exercises the Step 10 customer foundation (verified-only deterministic linking, anonymous-never-
# creates, idempotent resolution, cross-tenant hash non-correlation, no plaintext PII in identity
# rows, no-delete reversible merge/split, append-only ledgers, consent semantics, permission-aware
# read-model, entitlement gating, metering, and audit sanitization) via `aish:verify-step-10`, and
# runs the hermetic test suite. Never prints secrets; uses only generated, non-sensitive data.
# Rule 29, rule 36.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

EVID="docs/evidence/step-10/runtime"
mkdir -p "$EVID"
VERIFY_COMPOSE="${VERIFY_COMPOSE:-auto}"
export MAIL_MAILER="${MAIL_MAILER:-log}"
fail=0

pass() { printf '  \033[32mPASS\033[0m %s\n' "$1"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$1"; fail=1; }
step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

step "0. Preconditions"
if [ "$(id -u)" = "0" ]; then echo "refusing to run as root"; exit 1; fi
if [ ! -f vendor/autoload.php ]; then composer install --no-interaction --prefer-dist >/dev/null; fi
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64:' .env || php artisan key:generate >/dev/null
php -r 'echo "PHP ".PHP_VERSION."\n";'

step "1. Services"
if { [ "$VERIFY_COMPOSE" = "1" ] || { [ "$VERIFY_COMPOSE" = "auto" ] && command -v docker >/dev/null 2>&1 && [ -f docker-compose.yml ]; }; }; then
  docker compose up -d
  for _ in $(seq 1 30); do
    [ "$(docker compose ps --format '{{.Health}}' 2>/dev/null | grep -c healthy || true)" -ge 2 ] && break
    sleep 2
  done
  pass "docker-compose services started"
else
  echo "  using externally-provided services (CI service containers / native)"
fi

step "2. Migrate (PostgreSQL)"
if php artisan migrate:fresh --force >"$EVID/migrate.log" 2>&1; then pass "migrate:fresh"; else bad "migrate:fresh (see $EVID/migrate.log)"; fi

step "2b. Drain the queue"
# A job left over from an earlier run would be consumed by a later `queue:work --once` regression
# check instead of that check's own job, producing a spurious failure. Draining first makes
# verification deterministic. This clears only the local verification queue, never customer data.
if php artisan queue:clear --force >"$EVID/queue-clear.log" 2>&1; then pass "queue drained"; else bad "queue:clear (see $EVID/queue-clear.log)"; fi

step "3. Step 6 SaaS-core isolation regression (real infra)"
if php artisan aish:verify-saas-core >"$EVID/saas-core.log" 2>&1; then pass "aish:verify-saas-core"; else bad "aish:verify-saas-core"; cat "$EVID/saas-core.log"; fi

step "4. SPRINT-SF-05 regression (real infra)"
if php artisan aish:verify-sf-05 >"$EVID/sf-05.log" 2>&1; then pass "aish:verify-sf-05"; else bad "aish:verify-sf-05"; cat "$EVID/sf-05.log"; fi

step "5. Step 7 survey & CSAT regression (real infra)"
if php artisan aish:verify-step-7 >"$EVID/step-7.log" 2>&1; then pass "aish:verify-step-7"; else bad "aish:verify-step-7"; cat "$EVID/step-7.log"; fi

step "6. Step 8 feedback operations regression (real infra)"
if php artisan aish:verify-step-8 >"$EVID/step-8.log" 2>&1; then pass "aish:verify-step-8"; else bad "aish:verify-step-8"; cat "$EVID/step-8.log"; fi

step "7. Step 10 Customer 360 foundation (real infra)"
if php artisan aish:verify-step-10 >"$EVID/step-10.log" 2>&1; then pass "aish:verify-step-10"; else bad "aish:verify-step-10"; cat "$EVID/step-10.log"; fi
# Positive proof the customer checks actually ran (guards against a silently empty verifier).
if grep -q 'Step 10 verification passed' "$EVID/step-10.log"; then pass "Step 10 checks executed"; else bad "Step 10 checks did not report success"; fi

step "8. Backfill is idempotent and non-destructive"
# The reconcile command must be safe to rerun; a second pass must not change anything.
if php artisan aish:customer-reconcile >"$EVID/reconcile-1.log" 2>&1; then pass "aish:customer-reconcile (first pass)"; else bad "aish:customer-reconcile (first pass)"; cat "$EVID/reconcile-1.log"; fi
if php artisan aish:customer-reconcile >"$EVID/reconcile-2.log" 2>&1; then pass "aish:customer-reconcile (rerun is safe)"; else bad "aish:customer-reconcile (rerun)"; cat "$EVID/reconcile-2.log"; fi

step "9. No secret VALUE or contact PII leaked into verification logs"
# Bcrypt hashes, hex digests, and long random tokens must never appear; ULIDs (26 chars) are too
# short to match. The 40+ run must contain a digit, so a long CamelCase class name in a queue log
# (e.g. ProjectFeedbackOnSurveyResponseCompleted) is not reported as a secret.
if grep -Pq '\$2[aby]\$[0-9]{2}\$|\b(?=[A-Za-z0-9]*[0-9])[A-Za-z0-9]{40,}\b' "$EVID"/*.log; then bad "a verification log leaked a secret-like value"; else pass "verification logs free of secret values"; fi
# Step 10 handles contact PII, so also assert no email-shaped value reached a log.
if grep -Eq '[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}' "$EVID"/*.log; then bad "a verification log leaked an email-shaped value"; else pass "verification logs free of contact PII"; fi

step "10. Hermetic test suite"
# Unset any infra env the operator may have exported for the real-infra steps above so the suite is
# always hermetic (sqlite/array/sync via phpunit.xml), regardless of the surrounding shell.
if env -u DB_CONNECTION -u DB_HOST -u DB_PORT -u DB_DATABASE -u DB_USERNAME -u DB_PASSWORD \
       -u CACHE_STORE -u QUEUE_CONNECTION -u SESSION_DRIVER -u REDIS_HOST -u REDIS_PORT \
       php artisan test >"$EVID/phpunit.log" 2>&1; then pass "php artisan test"; else bad "php artisan test (see $EVID/phpunit.log)"; fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "STEP 10 VERIFICATION: PASS (evidence in $EVID/)"; else echo "STEP 10 VERIFICATION: FAILED"; exit 1; fi
