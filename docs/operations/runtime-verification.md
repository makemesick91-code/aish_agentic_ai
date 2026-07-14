# Runtime Verification — Aish Agentic AI (Step 5)

How the runtime is proven to actually work (not just "ports open"). Canonical:
[rule 29](../../.claude/rules/29-runtime-bootstrap-and-operations.md); ADRs
[0049](../decisions/adr/0049-health-and-readiness-contract.md),
[0050](../decisions/adr/0050-backend-runtime-ci-under-cicd-ctrl-1.md).

## What is verified
`scripts/runtime/verify-runtime.sh` exercises the running application and records evidence under
`docs/evidence/step-5/runtime/`:

1. Environment + PHP version.
2. Services healthy (PostgreSQL 17 + Redis 7 via Docker Compose, or externally provided in CI).
3. `migrate:fresh` succeeds.
4. `aish:preflight` passes (mandatory config present).
5. HTTP server boots; `GET /live` → 200 `status=alive`.
6. `GET /ready` → 200 `status=ready` (database + cache + configuration checks pass).
7. Queue: dispatch a smoke job, process one worker cycle, confirm the processed marker.
8. Scheduler: `aish:heartbeat` runs; `schedule:list` contains it.
9. **Negative readiness**: a second app instance pointed at a dead Redis returns `/ready` → **503** while
   `/live` stays **200** — proving truthful failure states, not a false-ready.
10. Frontend asset build (`vite manifest present`).

Run locally:

```bash
make verify
```

## CI
The `backend-runtime-ci` job in `.github/workflows/pr-ci.yml` runs the same script (`VERIFY_COMPOSE=0`) against
`postgres:17-alpine` + `redis:7-alpine` service containers on every ready PR, and is required by
`pr-ci / Required Gate` (ADR 0050; rule 28).

## Clean-checkout verification
Before a Step 5 GO tag, the runtime is verified from a **fresh clone at the exact merged SHA** (AFR-133) — not from
a development worktree — so the documented bootstrap path is proven to work from nothing.

## Production scheduler/queue (future)
Production cron runs `php artisan schedule:run` every minute; queue workers run `php artisan queue:work` under a
process supervisor. Overlap protection (`withoutOverlapping`) and single-server locking are already declared.
Deployment topology is **NOT STARTED** and out of scope for Step 5 (rule 23, rule 27).

## Health response shape
`/live` → `{"status":"alive","service":"Aish Agentic AI"}`.
`/ready` → `{"status":"ready|unavailable","checks":[{"name","ok","status"}...]}`. No credentials, connection
strings, stack traces, queries, or internal paths appear in any response (rule 04, rule 10).
