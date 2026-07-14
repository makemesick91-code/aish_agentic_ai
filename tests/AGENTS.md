# AGENTS.md — tests/ (Step 5 foundation test suite)

See root [AGENTS.md](../AGENTS.md) and `.claude/rules/09,20,29`.

- Present today (Step 5): `tests/Architecture` (foundation fitness functions), `tests/Unit`, `tests/Feature`
  (health, runtime, security), and `tests/Support` (test doubles). Fast suite runs on sqlite/array/sync; real
  PostgreSQL + Redis integration is proven by `scripts/runtime/verify-runtime.sh` and the `backend-runtime-ci` job.
- `tests/Architecture` enforces fitness functions (module boundaries, Shared-Kernel independence, controller
  placement) and grows as modules land — see
  [ARCHITECTURE_FITNESS_FUNCTIONS](../docs/architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md).
- Coverage MUST include the §50 categories (functional, multi-tenant, AI-eval incl. prompt-injection/PII, security,
  performance) as those capabilities are built. Gates MUST NOT be weakened (AFR-062,066).
- **Business/module and AI-evaluation tests: NOT STARTED.** Only runtime-foundation tests exist today.
