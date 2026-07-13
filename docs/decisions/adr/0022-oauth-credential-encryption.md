# ADR 0022 — OAuth and Credential Encryption

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Security and Privacy Architect
- **Rule:** `.claude/rules/04`, `20` (AFR-024, AFR-025) · **Canonical:** Master Source v2.3.0 §43; PRD v1.2.0 §15.1, §15.2

## Context
Google and integration credentials are high-value secrets. They must never be committed, must be encrypted at
rest, and must support rotation and tenant-initiated disconnect.

## Decision
Access tokens are **encrypted at rest**; refresh tokens are **never stored in plaintext**; OAuth **state** is
validated; tokens support **rotation**. Credentials are referenced via env/secret manager, never committed.
Tenants can disconnect Google and delete their credentials. See
[Secrets & Credentials Architecture](../../security/SECRETS_AND_CREDENTIALS_ARCHITECTURE.md).

## Alternatives
- **Plaintext token storage** — rejected: catastrophic on DB compromise.
- **Shared platform credential for all tenants** — rejected: breaks tenant isolation and consent.

## Consequences
Strong credential protection; requires an encryption key management approach (env/secret manager; key rotation).

## Impacts
- **Security:** the core subject — limits blast radius of DB or repo compromise.
- **Privacy:** protects tenant's Google identity.
- **Tenant isolation:** per-tenant encrypted credentials; no shared secret.
- **Database:** google_connections/integrations store ciphertext only.
- **Operational:** rotation + disconnect + deletion supported.
- **Cost:** low.

## Verification / fitness function
FF-SEC-01 (no secret committed), FF-SEC-02 (encryption/refresh-not-plaintext). Step 3: `secret-scan.sh`;
implementation: credential-encryption test.

## Related
Requirement: Master Source §43; PRD §15.1, §15.2. Application rule: AFR-024, AFR-025. ADRs: 0021, 0025.

## Evidence
`docs/security/SECRETS_AND_CREDENTIALS_ARCHITECTURE.md`, `docs/evidence/step-3/validation/secret-scan.log`.

## Non-claims
No real credential, token, or key is created, stored, or committed in Step 3.

## Rollback / supersession
Encryption/no-plaintext rules are permanent; superseded only by a security ADR + Master Source update.
