# tests/ — foundation test suite (Step 5)

Present layout: `Architecture/` (fitness functions), `Unit/`, `Feature/` (health, runtime, security), `Support/`
(test doubles). Planned additions as capabilities land: `Integration/`, `Security/`, `Performance/`.

Run the fast suite with `php artisan test` (sqlite/array/sync — no external services). Real PostgreSQL + Redis
integration is proven by `scripts/runtime/verify-runtime.sh` and the CI `backend-runtime-ci` job. Fitness functions:
[ARCHITECTURE_FITNESS_FUNCTIONS](../docs/architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md).

**Business/module, AI-evaluation, and performance tests: NOT STARTED.**
