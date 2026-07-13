# Integration Boundary Standards — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §38, §39 · PRD v1.2.0 §17, §18.2 · **Rules:** `.claude/rules/04`, `08`, `20` ·
**ADR:** [0016](../decisions/adr/0016-domain-events-outbox-idempotency-retry-dead-letter.md), [0017](../decisions/adr/0017-public-api-and-webhook-contracts.md), [0021](../decisions/adr/0021-google-business-profile-integration-boundary.md).

The **Integration module** is the single choke point for external side effects. All providers sit behind
adapters; all outbound effects go through the outbox; all inbound events are signed and untrusted.

## Standards (every integration)
- **Auth**: provider credentials encrypted (ADR 0022); OAuth state validated.
- **Inbound**: signed webhooks, replay-protected, idempotent, tenant-scoped, audited; payloads never determine
  tool/behaviour (prompt-injection/tool-abuse defense).
- **Outbound**: transactional outbox, idempotency key, bounded retry + backoff, dead-letter + replay,
  provider-state verification before success (`.claude/rules/10`).
- **Truthful state**: `Pending → Delivering → Delivered | Retry Scheduled | Failed | Dead Lettered | Cancelled`.
  A mock/unavailable integration is labelled truthfully and may be `BLOCKED` — never faked success.
- **Isolation**: tenant/branch scope on every call, credential, and record (FF-TEN-12).
- **Kill switch**: disable an integration class without data loss.

## Adapter contract
Each provider adapter implements: `authenticate`, `verifyInbound(signature)`, `dispatch(effect)` (idempotent),
`mapToDomainEvent`, `healthCheck`. Domain modules depend on the **contract**, never the provider SDK directly.

## Pilot integrations
- DaengtisiaMS event ingestion → [DAENGTISIAMS_EVENT_INTEGRATION_ARCHITECTURE](DAENGTISIAMS_EVENT_INTEGRATION_ARCHITECTURE.md).
- Google Business Profile → [GOOGLE_BUSINESS_PROFILE_ARCHITECTURE](GOOGLE_BUSINESS_PROFILE_ARCHITECTURE.md).
- WhatsApp invitation → [WHATSAPP_INVITATION_PILOT_BASELINE](WHATSAPP_INVITATION_PILOT_BASELINE.md) (Step 2).

## Assertion
No integration, adapter, webhook, or provider call runs in Step 3. These are the planned boundary standards.
