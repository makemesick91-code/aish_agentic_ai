# AGENTS.md — app/Modules/ (business modules)

See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/03,07,08,20,29`.

- Each module owns only its tables and exposes a public interface (contracts/services/events) — **no** foreign
  table writes (ADR 0010, AFR-004).
- Every business record carries `tenant_id`; branch-relevant records carry `branch_id` (AFR-006,007).
- Cross-module calls follow the [dependency matrix](../../docs/architecture/MODULE_DEPENDENCY_MATRIX.md); no
  undocumented cycles. The Step 5 architecture test `tests/Architecture/FoundationBoundariesTest` enforces the
  no-cross-module-reference and Shared-Kernel-independence rules as modules land.
- **Module implementation: NOT STARTED.** No module code exists yet; the runtime foundation (Step 5) does not add
  any business module.
