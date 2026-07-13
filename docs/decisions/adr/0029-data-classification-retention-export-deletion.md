# ADR 0029 — Data Classification, Retention, Export, and Deletion

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Data Governance / Privacy Architect
- **Rule:** `.claude/rules/07`, `18`, `20` (AFR-026, AFR-061) · **Canonical:** Master Source v2.3.0 §36, §37; PRD v1.2.0 §14

## Context
Data must be classified and governed: PII minimized, medical data excluded, retention configurable, and export/
deletion first-class and audited.

## Decision
Classify every table (CFG/PII/FIN/PUB/CRED/AUD/AIX/KB; `MED` = prohibited) per the
[Data Ownership Matrix](../../architecture/DATA_OWNERSHIP_MATRIX.md). Retention is configurable; export and
deletion are first-class Audit-module capabilities; audit history is append-only and non-deletable; admin access,
exports, and deletions are audited. `MED` data is never stored, sent to AI, or placed in public output. See
[Data Classification & Handling](../../security/DATA_CLASSIFICATION_AND_HANDLING.md).

## Alternatives
- **Unclassified data / indefinite retention** — rejected: privacy + compliance risk.
- **Hard-delete without audit** — rejected: breaks auditability.

## Consequences
Governable, privacy-safe data lifecycle; requires classification metadata and export/deletion workflows.

## Impacts
- **Security:** classification drives access + redaction.
- **Privacy:** the core subject — minimization, medical exclusion, right to export/delete.
- **Tenant isolation:** exports/deletions tenant-scoped.
- **Database:** data_exports/data_deletion_requests; retention config.
- **Operational:** audited export/deletion; retention jobs.
- **Cost:** low.

## Verification / fitness function
FF-DATA-03 (no `MED`), FF-TEN-06 (export scope). Implementation: deny-list + export/deletion tests.

## Related
Requirement: Master Source §36, §37; PRD §14. Application rule: AFR-026, AFR-061. ADRs: 0014, 0024.

## Evidence
`docs/security/DATA_CLASSIFICATION_AND_HANDLING.md`, `docs/architecture/DATA_OWNERSHIP_MATRIX.md`.

## Non-claims
No data, export, or deletion runs in Step 3; no real customer PII is in the repository.

## Rollback / supersession
Medical exclusion and audit immutability are permanent; superseded only by a data ADR + Master Source update.
