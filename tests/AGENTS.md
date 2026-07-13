# AGENTS.md — tests/ (FUTURE IMPLEMENTATION SCAFFOLD)

See root [AGENTS.md](../AGENTS.md) and `.claude/rules/09,20`.

- Planned categories: Architecture, Unit, Feature, Integration, Security, Performance.
- `tests/Architecture` will enforce fitness functions (module boundaries, one-writer-per-table, acyclic deps,
  tenant isolation) — see [ARCHITECTURE_FITNESS_FUNCTIONS](../docs/architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md).
- Coverage MUST include §50 categories (functional, multi-tenant, AI-eval incl. prompt-injection/PII, security,
  performance). Gates MUST NOT be weakened (AFR-062,066).
- **Application implementation: NOT STARTED.** No application tests exist in Step 3.
