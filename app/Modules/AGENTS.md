# AGENTS.md — app/Modules/ (FUTURE IMPLEMENTATION SCAFFOLD)

See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/03,07,08,20`.

- Each module owns only its tables and exposes a public interface (contracts/services/events) — **no** foreign
  table writes (ADR 0010, AFR-004).
- Every business record carries `tenant_id`; branch-relevant records carry `branch_id` (AFR-006,007).
- Cross-module calls follow the [dependency matrix](../../docs/architecture/MODULE_DEPENDENCY_MATRIX.md); no
  undocumented cycles.
- **Application implementation: NOT STARTED.** No module code in Step 3.
