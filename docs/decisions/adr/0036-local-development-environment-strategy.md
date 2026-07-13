# ADR 0036 — Local Development Environment Strategy

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no runtime bootstrapped**
- **Owner:** DevOps & Environment Architect
- **Rule:** `.claude/rules/23` (AFR-094) · **Canonical:** Master Source v2.4.0 §68; §34; PRD v1.3.0

## Context
A recommended local-development approach is needed so future implementation has parity with production and fast,
reproducible onboarding — without installing any runtime now.

## Decision
Recommended baseline: **Docker Compose** (PostgreSQL + Redis + PHP-FPM + Nginx parity with production, reproducible,
cross-OS). Fallback: **Laravel Sail**. Native and hybrid setups are evaluated and not recommended as the default.
Planned (not created) future files are specified: `.env.example`, `compose.yaml`, a `Makefile`/task runner,
`scripts/setup`, `scripts/doctor`, `scripts/test`, `scripts/validate`. See
[Local Development Strategy](../../environments/LOCAL_DEVELOPMENT_STRATEGY.md).

## Alternatives
- **Native PHP/PostgreSQL/Redis** — rejected as default: weaker version parity, higher onboarding friction across OSes.
- **Laravel Sail as primary** — kept as fallback: convenient but less flexible than raw Compose for multi-service parity.
- **Cloud dev environments** — deferred: not needed for the foundation phase.

## Consequences
Reproducible local parity when implementation starts; no runtime is created in Step 4.

## Impacts
- **Security:** local uses synthetic data only (ADR 0035); no production secrets locally.
- **Privacy:** no real data on developer machines.
- **Tenant isolation:** local mirrors tenant-scoping model without real tenants.
- **Database:** local PostgreSQL via Compose; disposable and reset-safe.
- **Operational:** `scripts/doctor` planned for environment self-check; fast onboarding.
- **Cost:** local only; no cloud cost — planning only.

## Verification / fitness function
`check-step4-coverage.sh` asserts a recommended baseline plus fallback in the local-development strategy (V4-ENV-01).
Runtime bootstrap is a future SPRINT-SF-00 gate, not Step 4.

## Related
Requirement: Master Source v2.4.0 §68, §34; PRD v1.3.0. Application rule: AFR-094. Rules: 23.
ADRs: 0025 (env/secrets), 0026 (CI/CD), 0034.

## Evidence
`docs/environments/LOCAL_DEVELOPMENT_STRATEGY.md`, `docs/environments/CI_RUNTIME_PLAN.md`.

## Non-claims
No `.env.example`, `compose.yaml`, or setup scripts are created; no runtime is installed. These are specifications only.

## Rollback
The local strategy is advisory and reversible; changes require a recorded decision, no Master Source major bump.
