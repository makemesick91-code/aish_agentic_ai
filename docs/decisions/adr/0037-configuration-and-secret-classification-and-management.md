# ADR 0037 — Configuration and Secret Classification and Management

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no secret created or stored**
- **Owner:** Multi-Tenant Security Architect
- **Rule:** `.claude/rules/24`, `04` (AFR-090, AFR-091) · **Canonical:** Master Source v2.4.0 §68; §43; PRD v1.3.0
- **Refines:** ADR 0025 (environment & secret management) for Step 4 classification detail.

## Context
Implementation needs an unambiguous configuration/secret classification so that no secret is ever committed and
each environment uses its own secrets. This refines ADR 0025 with a concrete matrix.

## Decision
Every configuration item is classified as one of: public configuration, internal configuration, secret,
credential, tenant setting, environment setting, feature flag, build-time setting, or runtime setting. Secrets and
credentials MUST NOT be committed; `.env.example` carries safe placeholders only (at implementation); refresh
tokens MUST NOT be plaintext; OAuth credentials are encrypted at rest; rotation and ownership are documented; each
environment uses its own secrets and no environment inherits another's. A developer's personal secret MUST NOT
become an organization production secret. See
[Configuration and Secret Matrix](../../environments/CONFIGURATION_AND_SECRET_MATRIX.md).

## Alternatives
- **Single shared secret set across environments** — rejected: blast radius and rotation coupling.
- **Committing an `.env` with "test" secrets** — rejected: secrets MUST NOT enter git.

## Consequences
A precise contract for config handling that CI secret-scanning and future secret-manager integration enforce.

## Impacts
- **Security:** prevents secret leakage; enforces encryption-at-rest and rotation.
- **Privacy:** credentials protecting PII/medical access are encrypted and scoped.
- **Tenant isolation:** tenant settings are classified distinctly from environment/system config.
- **Database:** encryption keys and DB credentials classified as secrets; never in code.
- **Operational:** documented ownership and rotation; per-environment secret paths.
- **Cost:** secret-manager usage is a planning cost category; no spend now.

## Verification / fitness function
`check-step4-coverage.sh` asserts the no-commit rule and per-environment secret separation (FF-SEC-01); the
existing `secret-scan.sh` gate continues to enforce no committed secrets.

## Related
Requirement: Master Source v2.4.0 §68, §43; PRD v1.3.0. Application rules: AFR-090, AFR-091. Rules: 24, 04, 11.
ADRs: 0022 (OAuth encryption), 0025, 0034.

## Evidence
`docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md`, `docs/evidence/validation/secret-scan.log`.

## Non-claims
No secret, credential, `.env`, or secret-manager is created. The matrix is classification guidance, not a stored config.

## Rollback
Classification can be extended anytime; weakening a control requires documented owner approval and a Master Source update.
