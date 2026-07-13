---
id: "24"
title: Configuration Classification and Secret Management
domain: security
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §68; §43 (security controls)"
  - "PRD v1.3.0 (configuration & secret requirements)"
  - "ADR 0037 (refines 0025); AFR-090, AFR-091"
supersede: "Only via a versioned Master Source update; no-secret-in-git and encryption controls are permanent."
---

# Rule 24 — Configuration Classification and Secret Management

## Purpose
Ensure every configuration item is classified and that no secret is ever committed or shared across environments.

## Scope
Configuration classification, secret handling, encryption, rotation, and per-environment separation.

## Rules
- Configuration **MUST** be classified as one of: public configuration, internal configuration, secret, credential,
  tenant setting, environment setting, feature flag, build-time setting, or runtime setting (AFR-091).
- Secrets, credentials, access/refresh tokens, private keys, `.env` files, dumps, and backups **MUST NOT** be
  committed (AFR-091; rule 04). `.env.example` carries safe placeholders only, at implementation time.
- Access tokens **MUST** be encrypted; refresh tokens **MUST NOT** be plaintext; OAuth credentials **MUST** be
  encrypted at rest; rotation and ownership **MUST** be documented.
- Each environment **MUST** use its own secrets; no environment inherits another environment's secrets; a
  developer's personal secret **MUST NOT** become an organization production secret (AFR-090).

## Required checks
- `scripts/docs/check-step4-coverage.sh` (no-commit rule + per-environment separation) and
  `scripts/docs/secret-scan.sh` (no committed secret patterns).

## Evidence
- `docs/environments/CONFIGURATION_AND_SECRET_MATRIX.md`; `docs/evidence/validation/secret-scan.log`.

## Related canonical sections
- Master Source v2.4.0 §68, §43; PRD v1.3.0; ADRs 0037, 0025, 0022; rules 04, 11, 23.

## Supersession
No-secret-in-git, encryption-at-rest, and per-environment separation are permanent; superseded only by a
higher-version Master Source update with documented owner approval.
