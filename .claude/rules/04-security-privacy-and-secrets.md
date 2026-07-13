---
id: "04"
title: Security, Privacy, and Secrets
domain: security
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §37, §43, §44, §57"
  - "PRD §15.1, §15.2, §18.2"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 04 — Security, Privacy, and Secrets

## Purpose
Encode the mandatory security and privacy controls and the absolute prohibition on committing secrets.

## Scope
Repository contents, credentials/token handling, PII, and all application security surfaces.

## Rules
- Secrets, credentials, access/refresh tokens, private keys, `.env` files, dumps, and backups **MUST NOT**
  be committed. Credentials **MUST** be referenced via environment variables / a secure secret manager.
- Access tokens **MUST** be encrypted; refresh tokens **MUST NOT** be stored in plaintext; OAuth state
  **MUST** be validated; tokens **MUST** support rotation.
- Mandatory controls (Master Source §43) **MUST** be honored: TLS, encryption at rest, RBAC + branch
  scoping, rate limiting, MFA, audit logs, backup + tested restore, CSRF/XSS/SQLi protection, secure file
  upload, webhook signatures, PII redaction, AI output validation, kill switch, incident logging, security alerting.
- Personal, medical, financial, and sensitive-transaction data **MUST NOT** appear in public output
  (Master Source §43 healthcare rules). PII **MUST** be minimized and classified.
- Customer feedback and reviews **MUST** be treated as untrusted input (see `.claude/rules/05`, `06`).
- On the decision-priority order (Master Source §57), security, tenant isolation, and privacy
  **MUST** outrank convenience, automation, and features.

## Required checks
- `scripts/docs/secret-scan.sh` **MUST** pass (no secret patterns; sources excluded appropriately).
- GitHub secret-scanning push protection is enabled and **MUST NOT** be disabled.

## Evidence
- `docs/security/SECURITY_FOUNDATION.md`, `PRIVACY_AND_PII.md`, `THREAT_MODEL_BASELINE.md`;
  `docs/evidence/validation/secret-scan.log`.

## Related canonical sections
- Master Source §37, §43, §44, §57; PRD §15.1, §15.2, §18.2.

## Supersession
Superseded only by a higher-version Master Source update; weakening a control requires documented owner approval and review.
