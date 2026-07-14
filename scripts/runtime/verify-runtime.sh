#!/usr/bin/env bash
# verify-runtime.sh — prove the Aish Agentic AI runtime actually works.
#
# Exercises the running application end-to-end and records evidence. Proves real
# connectivity (not open sockets), truthful health states (positive AND negative),
# queue dispatch+processing, and the scheduler. Read-mostly; the only mutation is
# migrating the configured (dev/CI) database. Never prints secrets. Rule 29, rule 11.
#
# Env:
#   VERIFY_COMPOSE=1|0   manage docker-compose services (default: 1 if docker present)
#   PORT=8000            healthy app port     BROKEN_PORT=8001  degraded-app port
#   BASE=http://127.0.0.1
set -uo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

PORT="${PORT:-8000}"
BROKEN_PORT="${BROKEN_PORT:-8001}"
BASE="${BASE:-http://127.0.0.1}"
VERIFY_COMPOSE="${VERIFY_COMPOSE:-auto}"
EVID="docs/evidence/step-5/runtime"
mkdir -p "$EVID"
SERVE_PID=""; BROKEN_PID=""
fail=0

pass() { printf '  \033[32mPASS\033[0m %s\n' "$1"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$1"; fail=1; }
step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }
cleanup() { [ -n "$SERVE_PID" ] && kill "$SERVE_PID" 2>/dev/null || true; [ -n "$BROKEN_PID" ] && kill "$BROKEN_PID" 2>/dev/null || true; }
trap cleanup EXIT

http_code() { curl -s -o /dev/null -w '%{http_code}' --max-time 10 "$1"; }
http_body() { curl -s --max-time 10 "$1"; }

wait_http() { # wait_http <url> <expected-code> <tries>
  local url="$1" want="$2" tries="${3:-30}" i=0
  while [ "$i" -lt "$tries" ]; do
    [ "$(http_code "$url")" = "$want" ] && return 0
    i=$((i+1)); sleep 1
  done
  return 1
}

step "0. Environment"
if [ ! -f vendor/autoload.php ]; then composer install --no-interaction --prefer-dist >/dev/null; fi
[ -f .env ] || cp .env.example .env
grep -q '^APP_KEY=base64:' .env || php artisan key:generate >/dev/null
php -r 'echo "PHP ".PHP_VERSION."\n";'

step "1. Services"
USE_COMPOSE=0
if { [ "$VERIFY_COMPOSE" = "1" ] || { [ "$VERIFY_COMPOSE" = "auto" ] && command -v docker >/dev/null 2>&1 && [ -f docker-compose.yml ]; }; }; then USE_COMPOSE=1; fi
if [ "$USE_COMPOSE" = "1" ]; then
  docker compose up -d
  for _ in $(seq 1 30); do
    [ "$(docker compose ps --format '{{.Health}}' 2>/dev/null | grep -c healthy || true)" -ge 2 ] && break
    sleep 2
  done
  pass "docker-compose services started"
else
  echo "  using externally-provided services (CI service containers / native)"
fi

step "2. Migrate"
if php artisan migrate:fresh --force >"$EVID/migrate.log" 2>&1; then pass "migrate:fresh"; else bad "migrate:fresh (see $EVID/migrate.log)"; fi

step "3. Preflight"
if php artisan aish:preflight >"$EVID/preflight.log" 2>&1; then pass "aish:preflight"; else bad "aish:preflight"; fi

step "4. Boot HTTP server"
php artisan serve --host=127.0.0.1 --port="$PORT" >"$EVID/serve.log" 2>&1 &
SERVE_PID=$!
if wait_http "$BASE:$PORT/live" 200 30; then pass "server is up (/live 200)"; else bad "server did not become live"; fi

step "5. Liveness /live"
if [ "$(http_code "$BASE:$PORT/live")" = "200" ]; then pass "/live -> 200"; else bad "/live not 200"; fi
http_body "$BASE:$PORT/live" | tee "$EVID/live.json" | grep -q '"status":"alive"' && pass "/live status=alive" || bad "/live missing alive status"

step "6. Readiness /ready (healthy)"
if [ "$(http_code "$BASE:$PORT/ready")" = "200" ]; then pass "/ready -> 200"; else bad "/ready not 200 (deps not ready)"; fi
http_body "$BASE:$PORT/ready" | tee "$EVID/ready.json" | grep -q '"status":"ready"' && pass "/ready status=ready" || bad "/ready not ready"

step "7. Queue dispatch + processing"
TOKEN="rt-verify-$$"
php artisan aish:queue-smoke --token="$TOKEN" >/dev/null 2>&1 && pass "dispatched smoke job" || bad "dispatch failed"
timeout 30 php artisan queue:work --once --stop-when-empty >"$EVID/queue-work.log" 2>&1 || true
if php artisan aish:queue-smoke --token="$TOKEN" --check >/dev/null 2>&1; then pass "worker processed job"; else bad "job was not processed"; fi

step "8. Scheduler"
php artisan aish:heartbeat >/dev/null 2>&1 && pass "aish:heartbeat ran" || bad "heartbeat failed"
php artisan schedule:list 2>/dev/null | tee "$EVID/schedule-list.log" | grep -q 'aish:heartbeat' && pass "aish:heartbeat is scheduled" || bad "heartbeat not scheduled"

step "9. Negative readiness (truthful 503)"
# Boot a second app instance pointed at a dead Redis: /ready must fail, /live must not.
REDIS_HOST=127.0.0.1 REDIS_PORT=1 CACHE_STORE=redis SESSION_DRIVER=redis \
  php artisan serve --host=127.0.0.1 --port="$BROKEN_PORT" >"$EVID/serve-broken.log" 2>&1 &
BROKEN_PID=$!
wait_http "$BASE:$BROKEN_PORT/live" 200 20 || true
if [ "$(http_code "$BASE:$BROKEN_PORT/live")" = "200" ]; then pass "/live stays 200 when a dependency is down"; else bad "/live should remain 200"; fi
if [ "$(http_code "$BASE:$BROKEN_PORT/ready")" = "503" ]; then pass "/ready -> 503 when Redis is unavailable"; else bad "/ready should be 503 when Redis is down"; fi
kill "$BROKEN_PID" 2>/dev/null || true; BROKEN_PID=""

step "10. Frontend asset build"
[ -f public/build/manifest.json ] || npm run build >"$EVID/asset-build.log" 2>&1
if [ -f public/build/manifest.json ]; then pass "vite manifest present"; else bad "vite manifest missing"; fi

step "Summary"
if [ "$fail" -eq 0 ]; then echo "RUNTIME VERIFICATION: PASS (evidence in $EVID/)"; else echo "RUNTIME VERIFICATION: FAILED"; exit 1; fi
