# Backup, Restore, and Rollback — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §51, §54 · PRD v1.2.0 §15.5, §24 · **Rules:** `.claude/rules/11`, `20` ·
**ADR:** [0027](../decisions/adr/0027-backup-restore-rollback-disaster-recovery.md), [0014](../decisions/adr/0014-database-ownership-and-migration-governance.md).

## 1. Backup
Regular backups of PostgreSQL and object storage; encrypted at rest; retention configurable. Backup status is an
observed signal with a failure alert (`.claude/rules/11`).

## 2. Restore (must be tested)
Restore is **not** assumed to work — it is **tested** before any production GO (Master Source §54 operational
gate). Export and deletion paths are likewise tested. A restore runbook documents RPO/RTO targets (values set at
implementation time, not fabricated here).

## 3. Rollback
- **Application rollback**: redeploy previous known-good build.
- **Schema rollback**: expand/contract migrations make forward-fix safe; destructive rollback is avoided by
  design (ADR 0014).
- **External-effect rollback**: outbox + idempotency ensure a rollback does not duplicate or lose external
  actions (ADR 0016).

## 4. Disaster recovery
DR baseline covers backup restoration to an alternate environment, credential re-provisioning (secrets never in
repo), and a documented recovery sequence. Detailed in
[BACKUP_RESTORE_DR_BASELINE](../operations/BACKUP_RESTORE_DR_BASELINE.md) and
[INCIDENT_AND_ROLLBACK_BASELINE](../operations/INCIDENT_AND_ROLLBACK_BASELINE.md).

## 5. Truthful status
No backup, restore, or rollback runs in Step 3. These are planned baselines; tested-restore evidence is a future
operational gate, not a current claim.
