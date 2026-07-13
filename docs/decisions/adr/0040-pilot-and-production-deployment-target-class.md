# ADR 0040 — Pilot and Production Deployment-Target Class

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **nothing provisioned or deployed; provider not selected**
- **Owner:** DevOps & Environment Architect
- **Rule:** `.claude/rules/26`, `11` (AFR-102) · **Canonical:** Master Source v2.4.0 §68; §34; §51; PRD v1.3.0
- **Refines:** ADR 0032 (deployment topology) with a Step 4 target class + isolation requirement.

## Context
Pilot and production need an isolation-first deployment-target class so the pilot never shares infrastructure
with DaengtisiaMS or Aish POS — without selecting a provider or provisioning anything.

## Decision
Recommended class: a **dedicated Ubuntu LTS virtual machine or equivalently isolated compute boundary**. The
pilot MUST NOT by default share database, redis namespace, application directory, PHP-FPM pool, queue worker,
storage, secrets, or deployment user with DaengtisiaMS or Aish POS. If temporary co-hosting is ever accepted it
requires an explicit risk decision plus full separation (separate database, redis prefix/db, directory, Unix
user, PHP-FPM pool, Nginx server block, secrets, backup, monitoring, rollback, resource limit, and port/domain
isolation). Provider classes are compared but **no provider is selected** (WATCH). See
[Step 4 Deployment-Target Evaluation](../../operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md).

## Alternatives
- **Shared host with DaengtisiaMS/Aish POS** — rejected as default: isolation and blast-radius risk.
- **Managed PaaS now** — deferred: provider selection is a later, evidence-based decision.
- **Select a provider in Step 4** — rejected: premature; kept as WATCH.

## Consequences
A clear isolation contract for pilot/production; provider selection and provisioning remain open, non-blocking WATCH items.

## Impacts
- **Security:** dedicated isolation limits blast radius; separate secrets and users prevent cross-product compromise.
- **Privacy:** pilot patient data confined to an isolated boundary with production controls.
- **Tenant isolation:** the multi-tenant app runs on infrastructure not shared with other products.
- **Database:** dedicated PostgreSQL for the pilot; no shared DB with DaengtisiaMS/Aish POS.
- **Operational:** separate backup, monitoring, and rollback per the isolation requirement.
- **Cost:** a dedicated VM is a planning cost category; provider pricing not committed.

## Verification / fitness function
`check-step4-coverage.sh` asserts the dedicated/isolated class and the no-shared-infrastructure-with-DaengtisiaMS
requirement (V4-SF-02). Provider selection is deferred and recorded as WATCH.

## Related
Requirement: Master Source v2.4.0 §68, §34, §51; PRD v1.3.0. Application rule: AFR-102. Rules: 26, 11.
ADRs: 0032, 0027 (backup/restore/DR), 0034.

## Evidence
`docs/operations/STEP_4_DEPLOYMENT_TARGET_EVALUATION.md`, `docs/operations/STEP_4_BACKUP_RESTORE_PLAN.md`,
`docs/operations/STEP_4_ROLLBACK_PLAN.md`.

## Non-claims
No compute is provisioned; no provider account is created; nothing is deployed. Provider choice is **not made**.
Deployment: **NOT STARTED**.

## Rollback
The target class is reversible before provisioning; provider selection will be its own recorded decision + ADR/Master Source update.
