# ADR 0060 — Feedback Projection and Lifecycle Architecture

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 8 Feedback Operations Foundation; feedback capability IN PROGRESS toward GO, other business modules NOT STARTED
- **Owner:** Principal Architect / Feedback Operations Engineer
- **Rule:** `.claude/rules/33`, `.claude/rules/03`, `.claude/rules/30` · **Canonical:** Master Source §47, §62; PRD v1.3.0 §10.7, §10.8 (FR-FBK-*); rules 33, 03, 08, 30

## Context
Step 8 turns completed survey responses (and future feedback sources) into an operable **Feedback Inbox**. A feedback
item must be created from a source event exactly once, must never cross a tenant or branch boundary, and must move
through an explicit, auditable lifecycle. The failure modes to prevent: a projection that double-creates an item on
retry or replay, feedback derived synchronously inside the survey transaction (coupling and partial-failure risk), an
implicit or free-form status that lets an item silently skip states, and confusing a resolved feedback item with a
customer-recovery outcome that Step 8 does **not** implement.

## Decision
- **After-commit domain event.** Completing a survey response emits `App\Events\SurveyResponseCompleted` **after**
  the response transaction commits; feedback is never created inside the survey write path, so a survey completion
  never partially creates or blocks on a feedback projection.
- **Queued idempotent projection.** `App\Feedback\Listeners\ProjectSurveyResponseToFeedback` enqueues
  `App\Jobs\Feedback\ProjectFeedbackJob`, which creates a `FeedbackItem` under a validated tenant context. Idempotency
  is enforced by a database unique constraint `(tenant_id, source_type, source_id)`; a replay, a retry, or a duplicate
  event resolves to the **same** item (no duplicate side effect), consistent with the outbox/idempotency baseline
  (ADR 0016).
- **Reconciliation command.** `php artisan aish:feedback-reconcile` (idempotent, safe to rerun, tenant-scoped)
  back-fills any completed response that has no feedback item and never creates a duplicate for one that does; it is a
  repair path, not a second write path.
- **Explicit lifecycle state machine.** A feedback item moves through `new → triaged → assigned → in_progress →
  resolved → closed → archived` via a guarded transition service; invalid transitions are rejected, and every
  transition is recorded on the immutable timeline (ADR 0061). `resolved`/`closed` are **operational feedback states
  only** and are **not** a customer-recovery outcome, refund, compensation, or apology (recovery remains NOT STARTED).
- **Fail-closed tenancy + branch.** `FeedbackItem` (and every feedback-owned table) is tenant-owned via
  `BelongsToTenant`/`TenantScope`; `branch_id` is carried where the source is branch-scoped; a branch-restricted user
  sees only their branch's feedback. Public route keys, where any exist, are opaque ULIDs.
- **Placement.** Feedback code is platform-core in top-level `App\Feedback\*` + `App\Models\Feedback*` (consistent
  with ADR 0052), not `app/Modules/`; formal module extraction remains deferred (ADR 0020 criteria).

## Alternatives
- **Synchronous in-transaction projection** — rejected: couples the survey write to feedback creation and turns a
  projection failure into a survey-completion failure.
- **Application-only dedup (no DB constraint)** — rejected: a race or replay can double-create; the unique
  `(tenant_id, source_type, source_id)` constraint is the authoritative guard.
- **Free-form / string status** — rejected: allows silent skips and untrackable states; an explicit guarded machine
  is auditable.
- **Reusing the recovery-ticket model now** — rejected: recovery is a later step; conflating them would overclaim
  capability and blur truthful states.

## Consequences
Every source event yields exactly one tenant/branch-scoped feedback item; retries and reconciliation never duplicate;
the lifecycle is explicit and auditable; and no feedback state is mistaken for a recovery result.

## Impacts
- **Security:** projection runs under a validated tenant context; DB-level idempotency prevents duplicate creation; no
  cross-tenant projection.
- **Privacy:** the feedback item carries minimized metadata; free-text answer content is not copied into logs/audit
  (rule 33) and is not AI-fed in Step 8.
- **Tenant isolation:** fail-closed tenant scope on all feedback tables; branch scope enforced; queued projection
  carries and clears tenant context per job.
- **Database:** adds feedback-owned tables (`feedback_items`, plus timeline/assignment/tag/note/attachment/export
  tables in ADRs 0061–0062) with a unique `(tenant_id, source_type, source_id)` projection key.
- **Operational:** truthful lifecycle states; `aish:feedback-reconcile` is idempotent and rerun-safe; a failed
  projection job has a bounded retry and a dead-letter path (no duplicate side effect).
- **Cost:** negligible; pure queued projection over existing data.

## Verification / fitness function
`tests/Feature/Feedback/FeedbackProjectionTest.php`, `FeedbackLifecycleTest.php`,
`tests/Feature/Sf08MigrationIntegrityTest.php`, `tests/Architecture/Sf08BoundariesTest.php`,
`tests/Feature/Security/Sf08CrossTenantMatrixTest.php`, `tests/Feature/Console/Sf08CommandsTest.php` assert
one-item-per-source idempotency, replay/retry safety, reconcile idempotency, guarded lifecycle transitions, and
tenant/branch isolation. AFR-188..AFR-191, AFR-208, AFR-210.

## Related
Requirement: Master Source §47, §62; PRD v1.3.0 §10.7, §10.8. Rules: 33, 03, 08, 30. ADRs: 0011, 0012, 0015, 0016,
0052, 0057.

## Evidence
`app/Events/SurveyResponseCompleted.php`, `app/Feedback/Listeners/*`, `app/Jobs/Feedback/ProjectFeedbackJob.php`,
`app/Models/FeedbackItem.php`, `app/Console/Commands/FeedbackReconcile*`, `database/migrations/2026_07_15_*`;
`docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-8/`.

## Non-claims
Does not implement AI sentiment/topic/severity/summary, customer recovery, SLA, or Google integration (all NOT
STARTED); `resolved`/`closed` are not recovery outcomes; does not create an `app/Modules/*` module; does not claim
deployment/pilot/production readiness.

## Rollback
After-commit projection, DB-level `(tenant_id, source_type, source_id)` idempotency, idempotent reconciliation, the
explicit guarded lifecycle, and fail-closed tenant/branch scope are permanent; loosening any requires an
owner-approved Master Source update.
