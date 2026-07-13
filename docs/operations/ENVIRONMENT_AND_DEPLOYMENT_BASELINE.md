# Environment and Deployment Baseline (Step 3) — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Deployment: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §34, §43, §51 · **Rules:** `.claude/rules/04`, `11`, `20` ·
**ADR:** [0025](../decisions/adr/0025-environment-and-secret-management.md), [0026](../decisions/adr/0026-ci-cd-architecture.md), [0032](../decisions/adr/0032-initial-deployment-topology-and-scale-path.md).

Operational view of the Step 3 architecture decisions. Detailed decisions live in
[Environment Strategy](../architecture/ENVIRONMENT_STRATEGY.md) and [Deployment Topology](../architecture/DEPLOYMENT_TOPOLOGY.md);
this doc is the ops baseline that links them.

## Environments
`local · test · CI · staging · pilot · production`. None provisioned in Step 3.

## Configuration & secrets
Config classes and the absolute no-commit-secrets rule per ADR 0025; secrets only via env/secret manager;
`secret-scan.sh` + push protection enforce it. No environment inherits another's secrets.

## Deployment components (planned, not running)
Nginx · PHP-FPM · queue workers · scheduler · PostgreSQL · Redis · S3 · TLS · backups · health checks ·
logging/metrics/tracing/errors · migration runner · rollback. Provider **not selected** (OD-02).

## Promotion & gates
`local → test/CI → staging → pilot → production`, each gated by CI + applicable release gates
(`.claude/rules/09`, `13`). Architecture decision ≠ runtime evidence — there is **no runtime evidence** in Step 3.

## Assertion
Nothing is deployed. "Deployed"/"pilot ready"/"production ready" MUST NOT be claimed.
