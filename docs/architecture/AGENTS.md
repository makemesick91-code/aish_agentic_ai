# AGENTS.md — docs/architecture/

Area rules for architecture. See root [AGENTS.md](../../AGENTS.md), `.claude/rules/03,07,08,10,20`, and the ADRs.

- The architecture is a **Laravel 12 modular monolith** (ADR 0009); microservices are not default. Extraction
  needs ADR 0020 evidence.
- 17 modules, each owning its data; **no module writes another module's tables** (ADR 0010, AFR-004).
- Tenant/branch isolation on **every** surface (ADR 0011,0012,0015; AFR-006..020); reporting never bypasses it.
- External effects: outbox + idempotency + retry + dead-letter; no success before provider verification (ADR 0016).
- Any architecture-affecting change requires a new ADR (continue numbering after 0032) + Master Source impact.
- Diagrams are labelled `PLANNED ARCHITECTURE — NOT DEPLOYED`. **Application implementation: NOT STARTED.**
