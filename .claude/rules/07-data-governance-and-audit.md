---
id: "07"
title: Data Governance and Audit
domain: data-governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §36, §37, §46, §53"
  - "PRD §14, §10.18, §16"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 07 — Data Governance and Audit

## Purpose
Define data classification, auditability, retention, metering integrity, and truthful data handling.

## Scope
Data model, audit logging, retention, export/deletion, usage metering, and AI data handling.

## Rules
- The core data model **MUST** follow Master Source §36 (tenants, subscriptions/plans, org hierarchy,
  users/roles/permissions, customers/transactions, surveys/campaigns/responses, feedback + AI analyses,
  recovery tickets, Google connections/reviews/replies, knowledge base, agent runs/steps/tool-calls/cost,
  notifications, integrations, audit_logs, security_events, data_exports, data_deletion_requests).
- Sensitive data **MUST** be classified; PII **MUST** be minimized; data retention **MUST** be configurable.
- Admin access, exports, and deletions **MUST** be audited. Audit history **MUST NOT** be deletable.
- Usage metering **MUST** be idempotent, auditable, tenant-scoped, plan-aware, overage-aware, retry-safe,
  and reconcilable (Master Source §46).
- AI input **MAY** pass through redaction; AI output storage **MUST** be controlled; prompt/model versions
  and tool calls **MUST** be recorded. Knowledge retrieval and agent runs **MUST** be tenant-scoped.
- Tenants **MUST** be able to disconnect Google and delete their credentials.

## Required checks
- Coverage matrix maps data governance to `docs/architecture/DOMAIN_MAP.md` + `docs/security/PRIVACY_AND_PII.md`.
- Truthful data states (Master Source §53) reflected in `.claude/rules/10`.

## Evidence
- `docs/architecture/DOMAIN_MAP.md`, `docs/security/PRIVACY_AND_PII.md`, `docs/operations/BACKUP_RESTORE_BASELINE.md`.

## Related canonical sections
- Master Source §36 (data), §37 (governance), §46 (metering), §53 (truthful states); PRD §14, §10.18.

## Supersession
Superseded only by a higher-version Master Source update; audit immutability and metering integrity are permanent.
