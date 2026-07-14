#!/usr/bin/env bash
# verify-saas-core.sh — prove the Step 6 SaaS core works against real PostgreSQL + Redis.
#
# Extends the Step 5 runtime verification without regressing it: migrates, provisions a
# tenant through the secure command, then exercises tenant isolation (DB fail-closed,
# Redis cache isolation, storage prefixing/traversal, and a real Redis queue round-trip)
# via `aish:verify-saas-core`, and runs the hermetic test suite. Never prints secrets and
# uses only generated, non-sensitive data. Rule 29, rule 30.
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

EVID="docs/evidence/step-6/runtime"
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

step "3. Secure tenant provisioning"
if php artisan aish:tenant-provision \
    --name="Verify Clinic" --owner-email="verify-owner@example.test" --owner-name="Verify Owner" \
    --branch-name="Verify Pusat" --branch-code="VPUSAT" >"$EVID/provision.log" 2>&1; then
  pass "aish:tenant-provision"
else
  bad "aish:tenant-provision (see $EVID/provision.log)"
fi
# The provisioning output must never contain an actual secret VALUE — a bcrypt hash or a
# long random token (the invitation token is 64 chars; ULIDs are only 26, so they do not
# match). We check for secret patterns, not the benign words "password"/"token".
if grep -Eq '\$2[aby]\$[0-9]{2}\$|[A-Za-z0-9]{32,}' "$EVID/provision.log"; then bad "provision output leaked a secret value"; else pass "provision output free of secret values"; fi

step "4. Real-infra tenant isolation (DB + Redis cache + Redis queue)"
if php artisan aish:verify-saas-core >"$EVID/isolation.log" 2>&1; then pass "aish:verify-saas-core"; else bad "aish:verify-saas-core (see $EVID/isolation.log)"; cat "$EVID/isolation.log"; fi

step "5. Hermetic test suite"
if php artisan test >"$EVID/phpunit.log" 2>&1; then pass "php artisan test"; else bad "php artisan test (see $EVID/phpunit.log)"; fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "SAAS CORE VERIFICATION: PASS (evidence in $EVID/)"; else echo "SAAS CORE VERIFICATION: FAILED"; exit 1; fi
