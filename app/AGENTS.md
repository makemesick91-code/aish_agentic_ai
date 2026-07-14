# AGENTS.md — app/ (Step 5 runtime foundation)

See root [AGENTS.md](../AGENTS.md), `.claude/rules/03,04,05,08,20,29`, and ADRs 0009–0032, 0047–0050.

- Step 5 established the **runtime foundation only**: `app/Http` (health probes, security-headers middleware),
  `app/Console/Commands` (preflight, heartbeat, queue-smoke), `app/Support` (health checks, runtime preflight,
  smoke job). Business/domain **module implementation: NOT STARTED**.
- Modules under `app/Modules/*` own their data; no module writes another module's tables; tenant/branch context on
  every path; secrets never committed; AI only via the control plane (rules 03, 04, 05, 20).
- `app/Support` holds framework/runtime glue (health, preflight, smoke) — it is NOT a domain module and MUST NOT
  accrete business logic. The domain Shared Kernel is `app/Shared` (rule 20; ADR 0010).
- Follow [REPOSITORY_LAYOUT](../docs/architecture/REPOSITORY_LAYOUT.md) and [MODULE_BOUNDARIES](../docs/architecture/MODULE_BOUNDARIES.md).
- **Business/module implementation: NOT STARTED.** Deployment, pilot, and production: NOT STARTED.
