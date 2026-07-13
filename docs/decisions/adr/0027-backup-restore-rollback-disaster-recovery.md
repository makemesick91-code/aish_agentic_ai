# ADR 0027 — Backup, Restore, Rollback, and Disaster Recovery

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** DevOps Architect
- **Rule:** `.claude/rules/11`, `20` (AFR-055, AFR-056) · **Canonical:** Master Source v2.3.0 §51, §54; PRD v1.2.0 §15.5, §24

## Context
Data durability and recoverability are release-critical. Backups without tested restore are not a control.

## Decision
Baseline: regular encrypted backups of PostgreSQL + object storage; **tested restore**; configurable retention;
expand/contract migrations enabling safe rollback (ADR 0014); outbox+idempotency preventing duplicate/lost
external effects on rollback (ADR 0016); a DR sequence to an alternate environment with re-provisioned secrets.
RPO/RTO target values are set at implementation (OD-09), not fabricated here. See
[Backup/Restore/Rollback](../../architecture/BACKUP_RESTORE_ROLLBACK.md).

## Alternatives
- **Backups without restore testing** — rejected: unproven recoverability.
- **Destructive schema rollback** — rejected: data-loss risk; use expand/contract.

## Consequences
Recoverable system with a rehearsed path; requires backup jobs + a restore rehearsal before production GO.

## Impacts
- **Security:** backups encrypted; secrets re-provisioned, never restored from repo.
- **Privacy:** retention configurable; deletion honored in backups per policy.
- **Tenant isolation:** restore preserves tenant scoping.
- **Database:** backup/restore/rollback of the shared DB.
- **Operational:** the core subject — runbook + backup-failure alert.
- **Cost:** backup storage.

## Verification / fitness function
Operational gate (Master Source §54): tested restore before production GO. Step 3: baseline documented only.

## Related
Requirement: Master Source §51, §54; PRD §15.5. Application rule: AFR-055, AFR-056. ADRs: 0014, 0016, 0025.

## Evidence
`docs/architecture/BACKUP_RESTORE_ROLLBACK.md`, `docs/operations/BACKUP_RESTORE_DR_BASELINE.md`, `INCIDENT_AND_ROLLBACK_BASELINE.md`.

## Non-claims
No backup, restore, rollback, or DR runs in Step 3; tested-restore evidence is a future operational gate.

## Rollback / supersession
Tested-restore requirement is permanent; superseded only by an operations ADR + Master Source update.
