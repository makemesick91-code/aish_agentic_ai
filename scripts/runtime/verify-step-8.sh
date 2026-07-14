#!/usr/bin/env bash
# verify-step-8.sh — prove the Step 8 Feedback Operations Foundation works against real PostgreSQL +
# Redis from a clean checkout, without regressing Step 5, Step 6, SPRINT-SF-05, or Step 7.
#
# It migrates, re-runs the SaaS-core, SF-05, and Step 7 real-infra checks (regression), then
# exercises the Step 8 feedback foundation (idempotent projection, lifecycle, scope-validated
# assignment, manual tags, append-only notes, permission-aware search, entitlement-gated export,
# metering, audit, and cross-tenant isolation) via `aish:verify-step-8`, and runs the hermetic test
# suite. Never prints secrets; uses only generated, non-sensitive data. Rule 29, rule 33.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

EVID="docs/evidence/step-8/runtime"
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

step "3. Step 6 SaaS-core isolation regression (real infra)"
if php artisan aish:verify-saas-core >"$EVID/saas-core.log" 2>&1; then pass "aish:verify-saas-core"; else bad "aish:verify-saas-core"; cat "$EVID/saas-core.log"; fi

step "4. SPRINT-SF-05 regression (real infra)"
if php artisan aish:verify-sf-05 >"$EVID/sf-05.log" 2>&1; then pass "aish:verify-sf-05"; else bad "aish:verify-sf-05"; cat "$EVID/sf-05.log"; fi

step "5. Step 7 survey & CSAT regression (real infra)"
if php artisan aish:verify-step-7 >"$EVID/step-7.log" 2>&1; then pass "aish:verify-step-7"; else bad "aish:verify-step-7"; cat "$EVID/step-7.log"; fi

step "6. Step 8 feedback operations foundation (real infra)"
if php artisan aish:verify-step-8 >"$EVID/step-8.log" 2>&1; then pass "aish:verify-step-8"; else bad "aish:verify-step-8"; cat "$EVID/step-8.log"; fi
# Positive proof the feedback checks actually ran (guards against a silently empty verifier).
if grep -q 'Step 8 verification passed' "$EVID/step-8.log"; then pass "Step 8 checks executed"; else bad "Step 8 checks did not report success"; fi

step "7. No secret VALUE leaked into verification logs"
# Bcrypt hashes or long random tokens must never appear; ULIDs (26 chars) do not match.
if grep -Eq '\$2[aby]\$[0-9]{2}\$|[A-Za-z0-9]{40,}' "$EVID"/*.log; then bad "a verification log leaked a secret-like value"; else pass "verification logs free of secret values"; fi

step "8. Hermetic test suite"
# Unset any infra env the operator may have exported for the real-infra steps above so the suite is
# always hermetic (sqlite/array/sync via phpunit.xml), regardless of the surrounding shell.
if env -u DB_CONNECTION -u DB_HOST -u DB_PORT -u DB_DATABASE -u DB_USERNAME -u DB_PASSWORD \
       -u CACHE_STORE -u QUEUE_CONNECTION -u SESSION_DRIVER -u REDIS_HOST -u REDIS_PORT \
       php artisan test >"$EVID/phpunit.log" 2>&1; then pass "php artisan test"; else bad "php artisan test (see $EVID/phpunit.log)"; fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "STEP 8 VERIFICATION: PASS (evidence in $EVID/)"; else echo "STEP 8 VERIFICATION: FAILED"; exit 1; fi
