# AGENTS.md — docs/integrations/

Area rules for integrations. See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/06,17,18`.

- The Integration module is the single choke point for external effects; providers sit behind adapters (ADR 0021).
- Inbound webhooks: signed, replay-protected, idempotent, tenant-scoped, untrusted (never steer behaviour) (AFR-039,050).
- Outbound: outbox + idempotency + retry + dead-letter; no success before provider verification (AFR-031..036).
- Google Review: **no gating**, equal access, human-approved replies, no PII/medical disclosure (ADR 0021; AFR-028,052).
- A mock/unavailable integration is labelled truthfully and may be `BLOCKED`; never faked as success (AFR-036).
- Google production API MUST be re-verified before real integration (OD-08). **Application implementation: NOT STARTED.**
