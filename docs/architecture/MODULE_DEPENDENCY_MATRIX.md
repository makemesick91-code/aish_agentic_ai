# Module Dependency Matrix — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §34–§35 · **Rules:** `.claude/rules/08`, `20` · **ADR:** [0010](../decisions/adr/0010-repository-layout-and-module-boundaries.md), [0016](../decisions/adr/0016-domain-events-outbox-idempotency-retry-dead-letter.md).

Table mutation of another module is **always** forbidden — cross-module flow is via public interface only. This
document separates two kinds of relationship:

- **Synchronous contract dependency (`C`)** — the row module calls the column module's public contract /
  application service (read or command). These are true build/runtime dependencies and **must form a DAG**.
- **Asynchronous domain events** — published via the transactional outbox. Publisher and consumer are
  **decoupled** (neither compiles against the other), so events are **not** dependencies and are listed
  separately in §2, not in the matrix.

## 1. Synchronous contract dependencies (`C` — row depends on column; must be acyclic)
| depends → | Plat | Iden | Tncy | Bill | Cust | SvcE | Surv | Camp | Feed | Recv | Repu | Know | AI | Notif | Intg | Anly | Audit |
|-----------|------|------|------|------|------|------|------|------|------|------|------|------|----|-------|------|------|-------|
| PlatformAdmin |  | C | C | C |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Identity |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Tenancy |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Billing |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Customer |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| ServiceEvent |  |  | C |  | C |  |  |  |  |  |  |  |  |  |  |  |  |
| Survey |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Campaign |  |  | C |  | C |  | C |  |  |  |  |  |  | C |  |  |  |
| Feedback |  |  | C |  |  |  | C |  |  |  |  |  | C |  |  |  |  |
| Recovery |  | C | C |  |  |  |  |  | C |  |  |  |  | C |  |  |  |
| Reputation |  | C | C |  |  |  |  |  |  |  |  |  | C |  | C |  |  |
| Knowledge |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| AI |  |  | C |  |  |  |  |  |  |  |  | C |  |  | C |  |  |
| Notification |  |  | C |  |  |  |  |  |  |  |  |  |  |  | C |  |  |
| Integration |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Analytics |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |
| Audit |  |  | C |  |  |  |  |  |  |  |  |  |  |  |  |  |  |

- **Tenancy** is depended on by nearly every module (context resolution) but depends on **none** upward — it is
  a base module, not a cycle.
- The `C` graph is a **DAG**: dependencies point only toward base modules (Tenancy, Identity, Survey,
  Customer, Knowledge, Integration, Feedback, ServiceEvent) — no module has a synchronous back-edge to a module
  that (transitively) calls it. Enforced by `FF-MOD-03`/`FF-MOD-05` (`tests/Architecture`).

## 2. Domain event flows (decoupled; producer → event → consumer(s))
Events are published via the outbox and consumed asynchronously; the producer has no reference to the consumer.
| Event | Producer | Consumer(s) |
|-------|----------|-------------|
| `VisitCompleted` / `TransactionCompleted` | ServiceEvent | Campaign |
| `CustomerConsentUpdated` / `CustomerOptedOut` | Customer | Campaign, Notification |
| `InvitationSent` / `InvitationExpired` | Campaign | Analytics, Audit |
| `SurveyResponseSubmitted` | Feedback | Feedback (analysis), Analytics |
| `FeedbackAnalyzed` | Feedback | Analytics |
| `HighRiskFeedbackDetected` | Feedback | **Recovery** |
| `RecoveryTicketEscalated` / `SLABreached` | Recovery | Notification, Analytics |
| `GoogleReviewIngested` | Integration/Reputation | Reputation (draft) |
| `ReplyPublished` / `ReplyPublicationFailed` | Reputation | Analytics, Audit |
| `AgentRunFailed` / `GuardrailBlocked` | AI | Notification, Audit |
| `UsageMetered` | AI, Campaign, Analytics | Billing |
| *(all important actions)* `AuditRecorded` | every module | **Audit** (append-only sink) |

Notes:
- **Recovery ← Feedback** is realized **both** ways: Recovery reads Feedback via contract (`C` above) **and**
  consumes `HighRiskFeedbackDetected` (event here). Feedback depends on **nothing** from Recovery.
- **Audit** is a fan-in **sink**: every module emits audit events that Audit consumes. Emission is
  fire-and-forget and does **not** couple the emitter to Audit, so Audit is **not** a synchronous dependency of
  any module (Audit itself depends only on Tenancy). Documented fan-in, not a cycle.
- **Analytics** consumes events as read-model projections only; it never reads another module's tables.
- **AI** never depends on Feedback/Recovery/Reputation; those modules call **into** AI via contract, so customer
  content cannot steer AI wiring (ADR 0019).

## 3. Documented decoupled/bidirectional pairs (per FF-MOD-03: no *undocumented* cycle)
- **Customer ↔ ServiceEvent** — ServiceEvent reads Customer via contract (`C`); Customer consumes ServiceEvent
  events (§2). No synchronous cycle (only ServiceEvent→Customer is `C`).
- **Recovery ↔ Feedback** — Recovery→Feedback is `C` and event-consume; Feedback→Recovery is event-only. No
  synchronous cycle.
- **Reputation `GoogleReviewIngested`** is an intra-module ingest→draft trigger, not a cross-module dependency.

## 4. Shared Kernel dependency
Every module may depend on `app/Shared` for context/envelope/id/result primitives only. `app/Shared` depends on
**no** module (`FF-MOD-04`).
