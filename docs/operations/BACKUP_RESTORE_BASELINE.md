# Backup and Restore Baseline — Aish Agentic AI

Canonical: Master Source §43, §54 (data gate). Rule: `.claude/rules/11`, `07`. PRD §15.5, §24.

## Requirements
- Backups MUST exist for database and critical storage, with a **tested restore** (a backup is not valid
  until restore is proven).
- Data retention MUST be configurable per tenant (`.claude/rules/07`).
- Data **export** and **deletion** MUST be implemented, audited, and tested (Master Source §54 data gate).
- Migrations MUST be safe/reversible; no critical orphan data; idempotency verified (Master Source §54).

## Data gate (Master Source §54 — evidence required before product release)
Migration pass · backup pass · restore pass · data retention configured · export tested · deletion tested ·
no critical orphan data · idempotency verified.

## Governance
Backups/dumps MUST NOT be committed to the repository and MUST NOT be indexed by the knowledge graph
(`.claude/rules/04`, `15`; `.gitignore`). Restore drills and backup status are part of observability
(`OBSERVABILITY_BASELINE.md`).

**Status:** backup/restore baseline documented. Drills at implementation (NOT STARTED).
