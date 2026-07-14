#!/usr/bin/env bash
# preflight.sh — verify host prerequisites for the Aish Agentic AI runtime.
#
# Fail-fast, read-only, idempotent. Checks tool availability and versions; when the
# application is installed it also runs `php artisan aish:preflight` (config checks).
# Never prints secrets. Does NOT require root. Rule: .claude/rules/29.
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

fail=0
ok()   { printf '  \033[32mOK\033[0m   %s\n' "$1"; }
warn() { printf '  \033[33mWARN\033[0m %s\n' "$1"; }
bad()  { printf '  \033[31mFAIL\033[0m %s\n' "$1"; fail=1; }

need() { # need <cmd> <label>
  if command -v "$1" >/dev/null 2>&1; then ok "$2 present ($($1 --version 2>&1 | head -1))"; else bad "$2 missing (install $1)"; fi
}

echo "== Aish Agentic AI runtime preflight =="

# PHP >= 8.3 (baseline: PHP 8.4, min 8.3 — ADR 0038, rule 25).
if command -v php >/dev/null 2>&1; then
  PHP_VER="$(php -r 'echo PHP_VERSION;')"
  if php -r 'exit(version_compare(PHP_VERSION, "8.3.0", ">=") ? 0 : 1);'; then ok "PHP ${PHP_VER} (>= 8.3)"; else bad "PHP ${PHP_VER} is below the 8.3 minimum"; fi
else
  bad "php missing"
fi

need composer "Composer"
need node "Node.js"
need npm "npm"

# Optional but recommended for local services.
if command -v docker >/dev/null 2>&1; then ok "Docker present"; else warn "Docker not found (needed for the bundled Postgres/Redis via docker-compose)"; fi

# Application-level config preflight (only once dependencies are installed).
if [ -f vendor/autoload.php ] && [ -f .env ]; then
  if php artisan aish:preflight >/dev/null 2>&1; then ok "artisan aish:preflight passed"; else bad "artisan aish:preflight failed (run: php artisan aish:preflight)"; fi
else
  warn "application not fully installed yet (run: make bootstrap)"
fi

if [ "$fail" -eq 0 ]; then echo "preflight: PASS"; else echo "preflight: FAILED"; exit 1; fi
