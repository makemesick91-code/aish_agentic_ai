# ADR 0032 — Initial Deployment Topology and Scale Path

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planned topology; **nothing deployed**
- **Owner:** DevOps Architect
- **Rule:** `.claude/rules/11`, `20` (AFR-054, AFR-071) · **Canonical:** Master Source v2.3.0 §34, §43, §51

## Context
A planned topology is needed so implementation and operations share a target, without provisioning anything or
selecting a provider prematurely.

## Decision
Planned components: Nginx + PHP-FPM app, queue workers, scheduler, PostgreSQL, Redis, S3-compatible storage,
TLS, backups, health checks, logging/metrics/tracing/errors, migration runner, rollback path. **Scale path:**
single node → stateless app nodes behind Nginx with shared PostgreSQL/Redis and a separate worker fleet →
module→service extraction only per ADR 0009/0020. Deployment provider is **not selected** in Step 3 (OD-02). See
[Deployment Topology](../../architecture/DEPLOYMENT_TOPOLOGY.md).

## Alternatives
- **Pick a provider and provision now** — rejected: out of Step 3 scope; premature.
- **Microservice topology now** — rejected: no evidence (ADR 0009).

## Consequences
A shared target for later provisioning; provider/topology specifics remain open, non-blocking WATCH items.

## Impacts
- **Security:** TLS + backups + isolation carried into topology; provider-independent baseline.
- **Privacy:** production data isolated per environment (ADR 0025).
- **Tenant isolation:** preserved across app nodes (stateless + shared DB scoping).
- **Database:** single PostgreSQL initially; scale via read paths later.
- **Operational:** the core subject — planned components + health/backup.
- **Cost:** starts small; scales horizontally.

## Verification / fitness function
Operational gates (Master Source §54) at production time; Step 3: topology documented, labelled not-deployed.

## Related
Requirement: Master Source §34, §43, §51. Application rule: AFR-054, AFR-071. ADRs: 0009, 0020, 0027.

## Evidence
`docs/architecture/DEPLOYMENT_TOPOLOGY.md`, `docs/operations/ENVIRONMENT_AND_DEPLOYMENT_BASELINE.md`.

## Non-claims
No host, container, TLS certificate, database, or deployment exists in Step 3; not pilot- or production-ready.

## Rollback / supersession
Superseded by an operations ADR + Master Source update when a provider/topology is selected.
