# Step 4 Backup & Restore Plan — Aish Agentic AI

**Title:** Step 4 Backup and Restore Plan
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Tested restore required before pilot; nothing executed yet.
**Rule refs:** `.claude/rules/11` (observability/backup/operations), `.claude/rules/07` (data governance/audit), `.claude/rules/03` (tenant isolation).
**Canonical:** Master Source v2.4.0 §51 (operations), §54 (data/operational gates), §36 (data model); PRD v1.3.0.
**AFR refs:** AFR-095..098 (ops/dependency governance context).

## Non-claims

- Nothing is provisioned, backed up, or restored; no backup job has run.
- This plan extends the Step 3 `BACKUP_RESTORE_BASELINE.md` / `BACKUP_RESTORE_DR_BASELINE.md` into a Step 4 planning baseline.
- A **tested restore is required before pilot**; until then, restore is planned, not verified.

## Purpose

Define what is backed up, how often, where copies live, how long they are kept, and how restore is tested, so the operational data gate (Master Source §54) can pass with evidence before pilot. Provider is NOT selected; backup mechanics are described as planned capabilities.

## Backup scope

| Data domain | Source | Backup method (planned) | Tenant isolation note |
|-------------|--------|-------------------------|-----------------------|
| Relational data | PostgreSQL 17 | Logical dump (`pg_dump`) + physical/base backups + WAL archiving | All rows carry `tenant_id`/`branch_id`; backup is tenant-inclusive, restore preserves scoping |
| Object/file storage | S3-compatible bucket | Versioning + cross-location copy | Separate bucket/prefix per the deployment isolation requirement |
| Cache/queue state | Redis 7.x | Not authoritative; rebuildable — snapshot optional | No source-of-truth data in Redis |
| Secrets/config | Secret store / env | Backed up via the secret manager, encrypted, never in repo | `.claude/rules/04`; never committed |
| Audit logs | PostgreSQL audit tables | Included in DB backup; append-only | Audit history MUST NOT be deletable (`.claude/rules/07`) |

## Backup schedule (planned baseline)

| Backup type | Frequency | Retention (baseline) | Storage location |
|-------------|-----------|----------------------|------------------|
| Full DB logical dump | Daily | 30 days | Off-instance, encrypted |
| DB base/physical backup | Weekly | 4 weeks | Off-instance, encrypted |
| WAL / continuous archive | Continuous | 7 days (PITR window) | Off-instance, encrypted |
| Object storage sync | Daily | 30 days + versioning | Separate region/bucket |
| Secrets/config snapshot | On change + weekly | 90 days | Secret manager backup |

- Retention is configurable per Master Source §54 data gate; the values above are the planning baseline.
- Point-in-time recovery (PITR) targets a bounded window via WAL archiving.

## RPO / RTO targets (planned)

| Metric | Baseline target | Basis |
|--------|-----------------|-------|
| RPO (max data loss) | ≤ 24h from daily full; ≤ minutes with WAL PITR | Backup frequency + WAL window |
| RTO (max time to restore) | ≤ 4h for full restore (pilot scale) | Restore drill measurement |

- Targets are **planning hypotheses** until a restore drill measures them; they MUST NOT be reported as achieved without evidence.

## Encryption and access

- Backups are encrypted at rest and in transit; keys managed via the secret manager, never committed.
- Access to backups is least-privilege and audited; restore actions are audited (`.claude/rules/07`).
- Backups are stored **separate** from the primary instance and, per the deployment isolation requirement, separate from DaengtisiaMS/Aish POS backups.

## Restore procedure (planned)

1. Identify recovery point (latest full + WAL, or a specific PITR timestamp).
2. Provision a clean, isolated restore target (never overwrite production blindly).
3. Restore DB from base backup, replay WAL to the target point.
4. Restore object storage from the corresponding daily sync/version.
5. Restore secrets/config from the secret-manager backup.
6. Verify tenant/branch scoping, audit-log integrity, and row counts against expectations.
7. Run smoke checks (auth, survey read, feedback read) before declaring restore successful.
8. Record evidence (timestamps, RPO/RTO measured, checks passed).

## Restore testing (required before pilot)

| Test | Frequency | Evidence |
|------|-----------|----------|
| Full restore drill | Before pilot, then quarterly | Restore log + measured RTO/RPO |
| PITR drill | Before pilot | Recovery to a chosen timestamp |
| Object-storage restore | Before pilot | File integrity check |
| Tenant-isolation post-restore check | Every drill | No cross-tenant leakage |

- A backup with no tested restore does not satisfy the operational gate; **tested restore is required before pilot** (`.claude/rules/11`).
- Restore drills use tenant-safe data; no real customer PII is placed in the repository or evidence.

## Backup integrity and monitoring

- Every backup job reports success/failure and a last-good timestamp surfaced as an observability signal (see the observability plan).
- Backup integrity is verified (checksum / test-restore sample) rather than assumed from a successful job exit.
- A stale or failed backup raises a High alert and enters the incident runbook.

## Roles and RACI

| Activity | Responsible | Accountable |
|----------|-------------|-------------|
| Backup schedule operation | Operations | Ops Architect |
| Restore drill execution | Operations | Ops Architect |
| Restore evidence sign-off | Release governance | Product owner |
| Backup access control | Security reviewer | Ops Architect |

## Data governance linkage

- Backup retention aligns with data-governance retention (`.claude/rules/07`); audit tables are append-only and included in backups.
- Tenant data-deletion requests are reconciled against backups per the deletion policy so deletions are honored within the retention window.
- No real customer PII is placed in the repository or in evidence artifacts.

## Failure handling

- Backup failures raise an alert (see the observability plan) and enter the incident runbook.
- A missed backup within the retention window is remediated before the next release gate.
- Restore failures during a drill block pilot readiness until fixed and re-drilled.

## Status

Backup and restore plan documented as a Step 4 planning baseline: backup scope, schedule, retention, encryption, RPO/RTO targets, restore procedure, and mandatory restore testing. Nothing is backed up or restored yet; **tested restore is required before pilot.** **PLANNING BASELINE — NOT IMPLEMENTED.**
