# AGENTS.md — app/ (FUTURE IMPLEMENTATION SCAFFOLD)

See root [AGENTS.md](../AGENTS.md), `.claude/rules/03,04,05,08,20`, and ADRs 0009–0032.

- This is a scaffold: `FUTURE IMPLEMENTATION SCAFFOLD — NO RUNTIME IMPLEMENTATION`. Do not add production code
  in Step 3.
- When implementation begins: modules under `app/Modules/*` own their data; no module writes another module's
  tables; tenant/branch context on every path; secrets never committed; AI only via the control plane.
- Follow [REPOSITORY_LAYOUT](../docs/architecture/REPOSITORY_LAYOUT.md) and [MODULE_BOUNDARIES](../docs/architecture/MODULE_BOUNDARIES.md).
- **Application implementation: NOT STARTED.**
