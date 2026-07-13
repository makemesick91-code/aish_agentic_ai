# ADR 0034 — Environment Topology and Separation Model

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no environment provisioned or deployed**
- **Owner:** DevOps & Environment Architect
- **Rule:** `.claude/rules/23` (AFR-087, 089, 092, 093, 094) · **Canonical:** Master Source v2.4.0 §68; PRD v1.3.0

## Context
The application needs a fixed set of environments with clear isolation and a promotion path so implementation,
CI, and pilot planning share one model — without provisioning anything.

## Decision
Six environments: **local, test, CI, staging, pilot, production**. Each documents purpose, owner, data class,
URL pattern, and isolation of database/redis/queue/storage, plus email/WhatsApp/Google/AI behavior, logging,
retention, backup, monitoring, access control, promotion, reset, and cost. Promotion flows
local→test/CI→staging→pilot→production; there MUST NOT be a direct unreviewed deploy to pilot or production.
Resource names MUST NOT collide across environments (see [Environment Naming Standard](../../environments/ENVIRONMENT_NAMING_STANDARD.md)).
Details in [Environment Strategy](../../environments/ENVIRONMENT_STRATEGY.md) and
[Environment Matrix](../../environments/ENVIRONMENT_MATRIX.md).

## Alternatives
- **Fewer environments (no staging/pilot split)** — rejected: pilot needs production-like isolation with minimum data.
- **Shared resources across environments** — rejected: collision and cross-environment leakage risk.
- **Deploy straight from a branch** — rejected: violates promotion gating.

## Consequences
A predictable promotion pipeline with isolation guarantees; concrete provisioning is deferred and evidence-gated.

## Impacts
- **Security:** per-environment isolation + promotion gates reduce blast radius and unreviewed change risk.
- **Privacy:** data policy (ADR 0035) keeps production data out of lower environments.
- **Tenant isolation:** tenant scoping preserved per environment; non-production never shares production stores.
- **Database:** separate database per environment; no cross-environment DB reuse.
- **Operational:** clear owners, reset policy, and promotion/rollback path per environment.
- **Cost:** environments sized independently; lower environments minimized — planning cost categories only.

## Verification / fitness function
`check-step4-coverage.sh` asserts all six environments, isolation language, promotion policy, and naming
standard (V4-ENV-01/03; FF-TEN-02). No environment is claimed deployed.

## Related
Requirement: Master Source v2.4.0 §68; PRD v1.3.0. Application rules: AFR-087, AFR-089, AFR-092, AFR-093, AFR-094.
Rules: 23, 03, 13. ADRs: 0025 (secrets/env), 0032 (topology), 0035, 0037.

## Evidence
`docs/environments/*` (strategy, matrix, naming, promotion), `docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md`.

## Non-claims
No environment is provisioned, deployed, or connected to any provider. All topology is **PLANNED — NOT DEPLOYED**.

## Rollback
Environment model is reversible before provisioning; changes require a recorded decision and Master Source update.
