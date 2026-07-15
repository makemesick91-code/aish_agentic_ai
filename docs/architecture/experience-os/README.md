# Agentic Experience OS — Architecture Baseline Index (Step 9)

**Status:** GOVERNANCE / ARCHITECTURE BASELINE — design only; implementation of un-built domains is NOT STARTED
**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Authority:** governed by rule 34 and Master Source §75 (v2.11.0); ADRs 0063–0068
**Canonical repo:** makemesick91-code/aish_agentic_ai

This directory holds the Step 9 architecture re-baseline for expanding Aish Agentic AI into an Agentic Experience OS.
Every document here is a **design/governance artifact**: it locks boundaries and contracts so Wave 1–3 can be built
without reopening fundamentals. None of it implements a production feature.

## Contents
- `DOMAIN_BOUNDARY_MAP.md` — domain ownership, single source of truth per capability, duplicate-ownership resolution (ADR 0063).
- `CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md` — Customer 360 identity model, merge/split, consent, backfill (ADR 0064).
- `EXPERIENCE_EVENT_LEDGER.md` — append-only cross-domain ledger; relationship to the preserved Step 8 timeline (ADR 0065).
- `CHANNEL_ADAPTER_ARCHITECTURE.md` — provider-neutral omnichannel adapter contract (ADR 0066).
- `AI_TOOL_PERMISSION_AND_APPROVAL_ARCHITECTURE.md` — bounded AI action, approval, cost, trace, kill switch (ADR 0067).
- `MIGRATION_AND_COMPATIBILITY_STRATEGY.md` — additive migration, idempotent backfill, progressive rollout (ADR 0068).

## Related (elsewhere in the repo)
- Capability inventory: `docs/product/capability-inventory/STEP_9_CAPABILITY_INVENTORY.md`.
- Competitor matrix + gap register: `docs/product/competitive/`.
- Roadmap + PRD addendum: `docs/product/EXPERIENCE_OS_ROADMAP.md`, `docs/product/AGENTIC_EXPERIENCE_OS_PRD_ADDENDUM.md`.
- Threat model: `docs/security/STEP_9_THREAT_MODEL.md`.
- Observability: `docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`.
- Step 10 contract: `docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`.

## Truthful status
Steps 5–8 are implemented; everything designed here (Customer 360, ledger, channel adapters, AI tool actions) is
**NOT STARTED**. The Step 9 GO tag attests architecture/governance readiness only.
