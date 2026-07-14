# Local Development — Aish Agentic AI

Reproducible local setup for the Step 5 runtime foundation. Canonical:
[rule 29](../../.claude/rules/29-runtime-bootstrap-and-operations.md); ADR
[0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md). No hidden knowledge required.

## Prerequisites
- PHP 8.3+ (baseline 8.4), Composer 2, Node.js 22, npm.
- Docker + Docker Compose (for the bundled PostgreSQL 17 + Redis 7). Optional if you provide your own services.

Check everything with:

```bash
make preflight
```

## One-command bootstrap
```bash
make bootstrap        # deps, .env, key, services, migrate, assets (idempotent)
make verify           # prove the runtime works end-to-end
```

`make bootstrap` never overwrites an existing `.env`, never runs as root, and never drops your database (use
`make bootstrap-fresh` to reset local dev data explicitly).

## Services
`docker-compose.yml` runs `postgres:17-alpine` on host port **55432** and `redis:7-alpine` on **63790** (chosen so
they never collide with native services). Point `.env` at them:

```
DB_HOST=127.0.0.1  DB_PORT=55432
REDIS_HOST=127.0.0.1  REDIS_PORT=63790
```

Start/stop services with `make up` / `make down`.

## Everyday commands
| Command | Purpose |
|---------|---------|
| `make test` | PHPUnit suite (sqlite/array/sync, no services) |
| `make analyse` | PHPStan/Larastan static analysis |
| `make format` / `make format-check` | Pint auto-format / check |
| `make assets` | Build frontend assets |
| `make verify` | End-to-end runtime verification |
| `make fast-ci` / `make full-ci` | Local CI gates (mirror draft / ready CI) |
| `php artisan serve` | Run the app at http://localhost:8000 |

## Health probes
- `GET /live` — process liveness (200 when up).
- `GET /ready` — dependency readiness (200 ready / 503 not ready).

## Secrets
Never commit `.env` or real secrets. `.env.example` holds safe placeholders only (rule 04, rule 24).
