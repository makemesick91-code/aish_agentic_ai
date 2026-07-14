# ADR 0051 — Step 6 Consolidated SaaS Core Foundation

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 6 SaaS Core Foundation; SaaS-core foundation IN PROGRESS, business/module features NOT STARTED
- **Owner:** Technical Program Manager / Laravel SaaS Architect
- **Rule:** `.claude/rules/30` (AFR-153, AFR-154), `.claude/rules/26` · **Canonical:** Master Source §62, §68; PRD v1.3.0 §22, §23; rules 26, 13

## Context
ADR 0039 fixes the SaaS Foundation implementation sequence as sixteen epics (EPIC-SF-01..16) mapped to nine
sprints (SPRINT-SF-00..08), and rule 26 requires each sprint to have its own GO/WATCH/NO-GO gate. SPRINT-SF-00
(runtime bootstrap + local/CI) was delivered as Step 5. The next four planned sprints — SPRINT-SF-01
(configuration/secret + authentication), SF-02 (tenant & branch context), SF-03 (RBAC & audit), and SF-04
(queue/cache/storage isolation), covering EPIC-SF-04..09 — are tightly coupled: authentication, tenant context,
RBAC, audit, and isolation are a single cross-cutting substrate that cannot be safely released or verified in
isolation (tenant context precedes business features; RBAC precedes a privileged surface; audit precedes
sensitive mutation; isolation precedes external async and tenant upload). Releasing four thin partial sprints
would create four incomplete isolation surfaces and four GO gates over a foundation that is only meaningful whole.

## Decision
Step 6 delivers SPRINT-SF-01 through SPRINT-SF-04 (EPIC-SF-04..09) as **one consolidated SaaS Core Foundation
release** governed by a single immutable annotated GO tag
`aish-agentic-ai-step-6-saas-core-foundation-v1.0.0-go`. The per-sprint GO/WATCH/NO-GO gate required by rule 26 is
satisfied for SF-01..SF-04 by the **single consolidated Step 6 gate**, which must exercise the full combined scope
(authentication, tenant/branch context, membership/invitation, tenant-scoped RBAC, audit, and cache/queue/storage
isolation) with cross-tenant security evidence. This ADR supersedes the per-sprint release cadence **expectation
for SF-01..SF-04 only**; the remaining sprints (SPRINT-SF-05..SF-08 — notification, subscription/admin skeleton,
observability, backup/restore, deployment/rollback/verification) retain their independent per-sprint gates and are
unchanged. The fixed dependency **sequence** of ADR 0039 is preserved; only the release packaging of four adjacent
sprints is consolidated.

## Alternatives
- **Four separate sprint releases with four GO tags** — rejected: the four sprints form one interdependent
  isolation substrate; partial releases leave incomplete tenant-isolation surfaces and multiply release overhead
  without a verifiable standalone increment.
- **Fold all of SPRINT-SF-00..SF-08 into one mega-release** — rejected: unreviewable, and later sprints
  (notification, subscription, observability, backup/restore, deployment) are genuinely separable with their own
  gates; consolidation is scoped to the coupled core only.
- **Change the ADR 0039 sequence** — rejected: the dependency order is permanent (AFR-099..101); only packaging
  changes.

## Consequences
Step 6 is a single reviewable increment whose GO gate proves the SaaS core boots and enforces tenant isolation,
authentication, RBAC, and audit together against real PostgreSQL + Redis. SF-05..SF-08 continue as independent
sprints. The consolidated gate must not be weaker than the sum of the four per-sprint gates it replaces.

## Impacts
- **Security:** authentication, tenant-scoped RBAC, audit, and isolation land together; cross-tenant security
  tests are a release blocker for the consolidated gate (rule 30).
- **Privacy:** tenant/branch context and audit metadata sanitization land before any business data handling.
- **Tenant isolation:** the whole isolation matrix (DB/cache/queue/storage/log) is verified in one gate rather
  than in four partial states.
- **Database:** the core tables (tenants, branches, memberships, invitations, audit_logs, permission tables) are
  introduced together in dependency order.
- **Operational:** one GO/WATCH/NO-GO decision and one GO tag for the coupled core; SF-05..SF-08 unaffected.
- **Cost:** one consolidated CI/review cycle instead of four partial ones; no gate is dropped for cost.

## Verification / fitness function
`scripts/docs/check-step4-coverage.sh` (sequence + epic presence unchanged), the consolidated Step 6 GO/WATCH/NO-GO
gate, the cross-tenant security test matrix (rule 30), and a clean-checkout SaaS-core verification on the merged
SHA. SC-20, SC-21.

## Related
Requirement: Master Source §62, §68; PRD v1.3.0 §22, §23. Application rules: AFR-153, AFR-154, AFR-099..101.
Rules: 26, 30, 13, 27. ADRs: 0039, 0052, 0053; 0011, 0012, 0013, 0015, 0029.

## Evidence
`docs/release/STEP_6_*` (forthcoming), `docs/governance/foundation-coverage-matrix.md`,
`docs/decisions/DECISION_LOG.md`; `docs/evidence/step-6/` (forthcoming).

## Non-claims
Consolidating four sprints into one release does not claim any business/feature module, deployment, pilot, or
production readiness — all remain **NOT STARTED**. No domain is owned; nothing is deployed. The Step 6 GO tag,
once created, attests SaaS-core-foundation readiness only. This ADR does not assert the release is merged, tagged,
CI-green, or runtime-verified; those remain **PLANNED** until evidenced.

## Rollback
Consolidation is a packaging decision; if the coupled scope proves too large to review as one unit, it may be
re-split into per-sprint releases via a recorded decision and Master Source update, restoring the ADR 0039
per-sprint cadence. The dependency sequence itself is never rolled back.
