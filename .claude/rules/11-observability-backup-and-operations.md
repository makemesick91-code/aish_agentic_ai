---
id: "11"
title: Observability, Backup, and Operations
domain: operations
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §51, §54 (operational gate)"
  - "PRD §15.3, §15.5, §24"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 11 — Observability, Backup, and Operations

## Purpose
Define the minimum observability, backup/restore, incident, and rollback baselines.

## Scope
Logging, tracing, metrics, alerting, backup/restore, incident response, and rollback.

## Rules
- Observability **MUST** provide (Master Source §51): structured app/queue/API/integration logs, agent and
  tool-call traces, prompt/model version, token usage, cost, latency, error/retry rate, Google sync & OAuth
  health, notification delivery, DB/Redis/storage health, and backup/restore status.
- Minimum alerts **MUST** exist for: high error rate, queue backlog, agent failure spike, Google sync
  failure, OAuth refresh failure, DB/storage issues, backup failure, high AI cost, PII guardrail failure,
  and tenant-isolation anomaly.
- Backups **MUST** exist with **tested** restore; data retention **MUST** be configured; export and deletion
  **MUST** be tested (Master Source §54 data/operational gates).
- An incident runbook, support workflow, and rollback plan **MUST** exist before a production release GO.
- Operational gates **MUST** pass with evidence before production; they **MUST NOT** be faked.

## Required checks
- `docs/operations/OBSERVABILITY_BASELINE.md`, `BACKUP_RESTORE_BASELINE.md`, `INCIDENT_AND_ROLLBACK_BASELINE.md` exist.

## Evidence
- `docs/operations/` baselines; future operational evidence under `docs/evidence/`.

## Related canonical sections
- Master Source §51 (observability), §54 operational/data gates; PRD §15.3, §15.5, §24.

## Supersession
Superseded only by a higher-version Master Source update; observability/backup minimums are permanent.
