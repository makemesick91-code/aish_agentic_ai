#!/usr/bin/env bash
# bootstrap-local.sh — reproducible, idempotent local bootstrap for Aish Agentic AI.
#
# Safe to run repeatedly. It NEVER overwrites an existing .env, never drops the
# database without an explicit flag, never runs as root, and never prints secrets.
# Rule: .claude/rules/29 (runtime bootstrap & operations).
#
# Usage: scripts/runtime/bootstrap-local.sh [--fresh]
#   --fresh   run `migrate:fresh` (DROPS local dev data) instead of `migrate`
set -euo pipefail
ROOT="$(git rev-parse --show-toplevel 2>/dev/null || pwd)"
cd "$ROOT"

FRESH=0
[ "${1:-}" = "--fresh" ] && FRESH=1

if [ "$(id -u)" = "0" ]; then
  echo "Refusing to run as root — run bootstrap as an unprivileged developer user." >&2
  exit 1
fi

step() { printf '\n\033[1m== %s ==\033[0m\n' "$1"; }

step "Preflight"
scripts/runtime/preflight.sh || { echo "Fix preflight failures above, then re-run." >&2; exit 1; }

step "PHP dependencies"
if [ ! -f vendor/autoload.php ]; then composer install --no-interaction --prefer-dist; else echo "vendor/ present (skip; delete vendor/ to reinstall)"; fi

step "Environment file"
if [ ! -f .env ]; then
  cp .env.example .env
  echo "created .env from .env.example"
else
  echo ".env already exists (left untouched)"
fi

step "Application key"
if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then php artisan key:generate; else echo "APP_KEY already set"; fi

step "Local services (Postgres + Redis)"
if command -v docker >/dev/null 2>&1 && [ -f docker-compose.yml ]; then
  docker compose up -d
  echo "waiting for services to become healthy..."
  for _ in $(seq 1 30); do
    up="$(docker compose ps --format '{{.Health}}' 2>/dev/null | grep -c healthy || true)"
    [ "${up:-0}" -ge 2 ] && break
    sleep 2
  done
  docker compose ps
else
  echo "docker not available — ensure PostgreSQL and Redis match your .env before continuing"
fi

step "Database migrations"
if [ "$FRESH" -eq 1 ]; then php artisan migrate:fresh --force; else php artisan migrate --force; fi

step "Frontend assets"
if [ ! -d node_modules ]; then npm install; else echo "node_modules present (skip)"; fi
npm run build

step "Done"
echo "Bootstrap complete. Verify the runtime with:  make verify"
