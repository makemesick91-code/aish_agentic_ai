# ADR 0025 — Environment and Secret Management

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** DevOps Architect
- **Rule:** `.claude/rules/04`, `11`, `20` (AFR-023, AFR-053) · **Canonical:** Master Source v2.3.0 §43

## Context
Six environments (local/test/CI/staging/pilot/production) need a consistent configuration and secret strategy
with an absolute no-commit-secrets rule.

## Decision
Classify configuration as required/optional/secret/environment-specific/tenant-configurable/feature-flag.
Secrets live only in env/secret manager and are **never committed**; enforced by `secret-scan.sh`, deny-lists in
`.claude/settings.json` and `.codex/rules/`, and GitHub push protection. No environment inherits another's
secrets. See [Environment Strategy](../../architecture/ENVIRONMENT_STRATEGY.md).

## Alternatives
- **`.env` in repo per environment** — rejected: forbidden; secret leakage.
- **One shared secret set** — rejected: no environment/tenant separation.

## Consequences
Clean, auditable configuration; requires a secret manager at deploy time (provider not yet selected, OD-02).

## Impacts
- **Security:** the core subject — no secret in VCS; layered deny controls.
- **Privacy:** production data/credentials isolated per environment.
- **Tenant isolation:** tenant-configurable values stored per tenant, not in env.
- **Database:** none new.
- **Operational:** boot-time validation of required config.
- **Cost:** low.

## Verification / fitness function
FF-SEC-01. Step 3: `secret-scan.sh` + deny-list tests; implementation: config-validation test.

## Related
Requirement: Master Source §43. Application rule: AFR-023, AFR-053. ADRs: 0022, 0026, 0027.

## Evidence
`docs/architecture/ENVIRONMENT_STRATEGY.md`, `docs/security/SECRETS_AND_CREDENTIALS_ARCHITECTURE.md`.

## Non-claims
No environment is provisioned and no secret is created in Step 3.

## Rollback / supersession
No-commit-secrets is permanent; superseded only by a security ADR + Master Source update.
