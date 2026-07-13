# ADR 0016 — Domain Events, Outbox, Idempotency, Retry, and Dead-Letter

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Principal Software Architect
- **Rule:** `.claude/rules/05`, `08`, `10`, `20` (AFR-031..036) · **Canonical:** Master Source v2.3.0 §35, §39

## Context
Cross-module workflows and external side effects must be reliable, non-duplicating, and truthful — reliability
before autonomy.

## Decision
Modules communicate via **versioned domain events** with a mandatory envelope (incl. `tenant_id`,
`correlation_id`). External effects use a **transactional outbox** (business row + outbox row in one txn);
consumers are **idempotent** (dedupe on `event_id`); **bounded retry + backoff**; **dead-letter + replay**; a
**kill switch** halts an effect class without data loss. No success is reported before provider verification.
See [Event-Driven Architecture](../../architecture/EVENT_DRIVEN_ARCHITECTURE.md) and
[Outbox/Idempotency/Retry](../../architecture/OUTBOX_IDEMPOTENCY_RETRY.md).

## Alternatives
- **Direct synchronous external calls in the request** — rejected: lost effects on crash; duplication on retry.
- **Distributed transactions** — rejected: unnecessary in a monolith; complexity.

## Consequences
Reliable, auditable, exactly-once-effect behaviour; requires an outbox dispatcher and idempotency keys.

## Impacts
- **Security:** untrusted payloads never steer behaviour; signature-verified webhooks (ADR 0017).
- **Privacy:** event payloads minimized; no `MED` data; PII minimized.
- **Tenant isolation:** every event carries and rehydrates tenant context (ADR 0012).
- **Database:** outbox + dead_letters tables (Integration module).
- **Operational:** replay is authorized + audited; kill switch; truthful states.
- **Cost:** low; avoids duplicate external charges.

## Verification / fitness function
FF-REL-01..06. Implementation: duplicate-delivery, retry, DLQ/replay, provider-verify tests.

## Related
Requirement: Master Source §35, §39. Application rule: AFR-031..036. ADRs: 0012, 0017, 0021.

## Evidence
`docs/architecture/EVENT_DRIVEN_ARCHITECTURE.md`, `docs/architecture/OUTBOX_IDEMPOTENCY_RETRY.md`.

## Non-claims
No event bus, outbox, or consumer runs in Step 3.

## Rollback / supersession
Reliability guarantees are permanent; superseded only by an architecture ADR + Master Source update.
