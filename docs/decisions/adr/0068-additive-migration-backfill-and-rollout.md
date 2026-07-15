# ADR 0068 — Additive Migration, Backfill, Reconciliation, and Progressive Rollout Strategy

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 architecture LOCK; no migration is run in Step 9
- **Owner:** Principal Architect / Data Platform
- **Rule:** `.claude/rules/34`, `.claude/rules/23`, `.claude/rules/26`, `.claude/rules/29` · **Canonical:** Master Source §75, §36, §62; PRD v1.3.0; rules 34, 03, 11, 23, 26, 29, 33

## Context
Expanding into Customer 360, the Experience Event Ledger, Recovery, and beyond requires new data structures and
backfills over existing Step 8 records. Done unsafely, this risks destroying Step 8 data, resetting migration history,
double-writing on retry, or degrading tenant isolation. This ADR locks the migration/backfill/rollout contract before
Step 10.

## Decision
- Adopt the strategy in `docs/architecture/experience-os/MIGRATION_AND_COMPATIBILITY_STRATEGY.md`: **additive migrations
  only** by default; **no migration-history reset**; **existing Step 8 records remain valid and unaltered**; **no
  destructive production backfill**; **idempotent, queued, chunked, resumable** backfills with progress + failure
  visibility and a dead-letter path; **shadow projections** verified before cutover; **per-tenant feature flags**
  (default off) enabled only after backfill + verification; idempotent `aish:*-reconcile` commands as a controlled
  second read path; additive rollback (drop unused new structures; disable flag; source untouched) with forward-fix
  preferred once live; deployment ordering **migrate → tolerant deploy → backfill → verify → flag**; mixed-version
  tolerance; a tested restore before any pilot/production backfill (rule 11); a stated performance budget; and a
  post-backfill tenant-isolation check.

## Alternatives
- **Destructive/altering migrations** — rejected: risks Step 8 data and history; additive-only preserves the baseline.
- **Synchronous one-shot backfill** — rejected: not resumable; risks isolation and live-traffic degradation.
- **Enable features before backfill** — rejected: exposes half-migrated data; flags follow verification.
- **Blind cutover to a new read-model** — rejected: shadow-verify-then-cutover prevents silent data loss.

## Consequences
New capabilities are added without endangering Step 8; backfills are safe to rerun and resume; rollout is reversible and
observable; tenant isolation is preserved throughout.

## Impacts
- **Security:** backfills are tenant-scoped; post-backfill isolation check; no cross-tenant write.
- **Privacy:** backfills carry no new PII exposure; erasure/retention honored (rule 07).
- **Tenant isolation:** per-tenant chunking + isolation verification; flags are per-tenant.
- **Database:** additive tables/columns/indexes only; the 39 existing migrations are immutable history.
- **Operational:** progress/failure/DLQ/reconcile visibility; tested restore + backup before large backfill; performance
  budget.
- **Cost:** bounded backfill cost within budget; none in Step 9.

## Verification / fitness function
`scripts/docs/verify-step-9.sh` asserts the migration strategy is additive, idempotent, resumable, reconcilable, and
reversible and preserves Step 8. Each build step adds a migration-integrity test and a clean-checkout `aish:verify-*`
(e.g. Step 10 `aish:verify-step-10`). AFR-235, AFR-236, AFR-237, AFR-238.

## Related
Requirement: Master Source §75, §36, §62; PRD v1.3.0. Rules: 34, 03, 11, 23, 26, 29, 33. ADRs: 0016, 0048, 0060, 0063,
0064, 0065.

## Evidence
`docs/architecture/experience-os/MIGRATION_AND_COMPATIBILITY_STRATEGY.md`, `database/migrations/` (39 immutable
migrations), `docs/operations/BACKUP_RESTORE_BASELINE.md`; `docs/governance/foundation-coverage-matrix.md`;
`docs/evidence/step-9/`.

## Non-claims
Runs no migration or backfill; creates no table, flag, or reconcile command; does not alter Step 8 data; does not claim
Customer 360 or any wave is migrated/deployed; does not claim deployment/pilot/production readiness.

## Rollback
Additive-only migrations, no-history-reset, Step-8 preservation, idempotent/resumable/reversible backfill,
verify-before-flag, and backup-tested-restore-before-pilot are permanent; changing any requires a new ADR +
owner-approved Master Source update.
