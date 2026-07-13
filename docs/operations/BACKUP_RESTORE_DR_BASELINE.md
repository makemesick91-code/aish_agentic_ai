# Backup, Restore, and Disaster Recovery Baseline (Step 3) — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §51, §54 · **Rules:** `.claude/rules/11`, `20` ·
**ADR:** [0027](../decisions/adr/0027-backup-restore-rollback-disaster-recovery.md), [0014](../decisions/adr/0014-database-ownership-and-migration-governance.md).

Extends the Step 1 [BACKUP_RESTORE_BASELINE](BACKUP_RESTORE_BASELINE.md); architecture view in
[Backup/Restore/Rollback](../architecture/BACKUP_RESTORE_ROLLBACK.md).

## Backup
Regular encrypted backups of PostgreSQL + object storage; configurable retention; backup-failure alert.

## Restore (tested)
Restore is **tested** before any production GO (Master Source §54). Export and deletion paths tested too.

## Rollback
App redeploy of last good build; expand/contract schema (ADR 0014); outbox + idempotency prevent duplicate/lost
external effects on rollback (ADR 0016).

## Disaster recovery
Restore to an alternate environment; secrets **re-provisioned** via secret manager (never restored from repo);
documented recovery sequence. RPO/RTO targets set at implementation (OD-09), not fabricated.

## Assertion
No backup, restore, or DR runs in Step 3; tested-restore evidence is a future operational gate.
