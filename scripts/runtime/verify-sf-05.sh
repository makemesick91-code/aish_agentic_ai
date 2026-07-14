#!/usr/bin/env bash
# verify-sf-05.sh — prove the SPRINT-SF-05 foundations work against real PostgreSQL + Redis
# from a clean checkout, without regressing Step 5 or Step 6.
#
# It migrates, re-runs the Step 6 SaaS-core real-infra isolation check (regression), then
# exercises the SF-05 notification/subscription/platform foundations (DB + Redis cache +
# real Redis queue delivery, entitlement fail-closed, usage idempotency, security-state
# precedence, platform/tenant plane separation, last-super-admin protection, no impersonation)
# via `aish:verify-sf-05`, and runs the hermetic test suite. Never prints secrets; uses only
# generated, non-sensitive data. Rule 29, rule 31.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

EVID="docs/evidence/sprint-sf-05/runtime"
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
if php artisan aish:verify-saas-core >"$EVID/saas-core.log" 2>&1; then pass "aish:verify-saas-core"; else bad "aish:verify-saas-core (see $EVID/saas-core.log)"; cat "$EVID/saas-core.log"; fi

step "4. SF-05 foundations (notification/subscription/platform, real infra)"
if php artisan aish:verify-sf-05 >"$EVID/sf-05.log" 2>&1; then pass "aish:verify-sf-05"; else bad "aish:verify-sf-05 (see $EVID/sf-05.log)"; cat "$EVID/sf-05.log"; fi
# Positive proof the isolation checks actually ran (guards against a silently empty verifier).
if grep -q 'SF-05 verification passed' "$EVID/sf-05.log"; then pass "SF-05 checks executed"; else bad "SF-05 checks did not report success"; fi

step "5. No secret VALUE leaked into verification logs"
# Bcrypt hashes or long random tokens must never appear; ULIDs (26 chars) do not match.
if grep -Eq '\$2[aby]\$[0-9]{2}\$|[A-Za-z0-9]{40,}' "$EVID"/*.log; then bad "a verification log leaked a secret-like value"; else pass "verification logs free of secret values"; fi

step "6. Hermetic test suite"
if php artisan test >"$EVID/phpunit.log" 2>&1; then pass "php artisan test"; else bad "php artisan test (see $EVID/phpunit.log)"; fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "SF-05 VERIFICATION: PASS (evidence in $EVID/)"; else echo "SF-05 VERIFICATION: FAILED"; exit 1; fi
