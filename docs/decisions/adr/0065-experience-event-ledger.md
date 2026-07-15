# ADR 0065 — Experience Event Ledger and Relationship to the Step 8 Immutable Timeline

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 architecture LOCK; ledger implementation NOT STARTED
- **Owner:** Principal Architect / Analytics & Outcome Ledger
- **Rule:** `.claude/rules/34`, `.claude/rules/03`, `.claude/rules/07` · **Canonical:** Master Source §75, §35, §36; PRD v1.3.0; rules 34, 03, 07, 33

## Context
The Experience OS needs a cross-domain history of survey, feedback, review, ticket, conversation, recovery, AI, and
outcome events for analytics and traceability. Step 8 already has an immutable Feedback Timeline
(`app/Models/FeedbackEvent.php`) that is authoritative for feedback-item history. A naive "one big event table"
approach risks replacing that timeline destructively or creating a second source of truth for feedback state.

## Decision
- Define an **append-only Experience Event Ledger** (design in `docs/architecture/experience-os/EXPERIENCE_EVENT_LEDGER.md`)
  with: ULID event identity; mandatory tenant/branch context; subject + sanitized actor; source domain, typed+versioned
  event type; distinct `occurred_at`/`recorded_at`; idempotency key; correlation + causation ids; per-subject monotonic
  recorded_at ordering with **no global total order**; payload minimization + PII/MED classification and redaction;
  retention + legal hold; idempotent rebuildable projections; DLQ + reconcile; backfill source markers; additive
  versioned schema evolution; tenant-scoped indexing.
- The **Feedback Timeline stays authoritative** for feedback-item lifecycle. The ledger is **additive** and may PROJECT
  from domain events (including feedback events) but does **not** own feedback state and does **not** replace or
  destructively migrate the timeline.
- The ledger inherits the Step 8 immutability guarantees (no `updated_at`; update/delete blocked at the model layer).

## Alternatives
- **Migrate the feedback timeline into a generic ledger** — rejected: destructive; violates Step 8 preservation.
- **Global total ordering** — rejected: unachievable/expensive across domains; consumers must tolerate out-of-order
  `occurred_at`; per-subject ordering is sufficient.
- **Store full payloads incl. free text** — rejected: PII/MED exposure; payloads are minimized and classified.
- **No idempotency key** — rejected: at-least-once delivery would double-apply projections.

## Consequences
A single additive cross-domain history enables analytics and traceability without touching the Step 8 timeline; replays
are safe; projections are rebuildable; privacy is preserved by minimization.

## Impacts
- **Security:** tenant/branch-scoped events; sanitized actor; no secrets/tokens in payloads.
- **Privacy:** payload minimization; PII/MED classification + redaction; no raw feedback free text.
- **Tenant isolation:** every event and index is tenant-scoped; no cross-tenant query.
- **Database:** Step 10+ adds additive ledger tables and indexes `(tenant_id, subject_id, occurred_at)` and
  `(tenant_id, correlation_id)`; the Step 8 `feedback_events` table is unchanged.
- **Operational:** DLQ + reconcile; projection lag is observable (`docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`).
- **Cost:** none in Step 9; bounded append + projection cost later.

## Verification / fitness function
`scripts/docs/verify-step-9.sh` asserts the ledger design exists and specifies identity, ordering, idempotency,
correlation, privacy, replay, projection, retention, failure, and the preserved-timeline relationship. Later steps add
ledger idempotency/immutability tests. AFR-220, AFR-221, AFR-222, AFR-223, AFR-224.

## Related
Requirement: Master Source §75, §35, §36; PRD v1.3.0. Rules: 34, 03, 07, 33. ADRs: 0016, 0017, 0060, 0061, 0063.

## Evidence
`docs/architecture/experience-os/EXPERIENCE_EVENT_LEDGER.md`, `app/Models/FeedbackEvent.php` (preserved timeline);
`docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-9/`.

## Non-claims
Creates no ledger table, event stream, or runtime; does not replace or migrate the Step 8 timeline; does not claim
analytics is implemented; does not claim deployment/pilot/production readiness.

## Rollback
Append-only immutability, preservation of the Step 8 timeline, additive-not-replacing ledger, idempotent rebuildable
projections, per-subject ordering, and payload minimization are permanent; changing any requires a new ADR +
owner-approved Master Source update.
