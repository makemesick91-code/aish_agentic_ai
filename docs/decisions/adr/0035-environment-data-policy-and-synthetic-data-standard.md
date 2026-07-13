# ADR 0035 — Environment Data Policy and Synthetic-Data Standard

- **Status:** Accepted (2026-07-13, Asia/Makassar) — planning baseline; **no data exists in any environment**
- **Owner:** Multi-Tenant Security & Privacy Architect
- **Rule:** `.claude/rules/23`, `07` (AFR-088) · **Canonical:** Master Source v2.4.0 §68; §43; PRD v1.3.0

## Context
Healthcare-pilot context demands that real patient/tenant data never leak into development or test environments.
A binding per-environment data policy must exist before any environment is populated.

## Decision
Data policy: **local** = synthetic only; **test** = synthetic fixtures; **CI** = synthetic fixtures; **staging**
= synthetic or formally anonymized only; **pilot** = approved pilot data under a minimum-data policy; **production**
= approved tenant data under production controls. Raw production data MUST NOT be copied to local/test/CI/staging.
Any exception requires documented privacy/security approval plus secure, verified anonymization. See
[Data Policy by Environment](../../environments/DATA_POLICY_BY_ENVIRONMENT.md).

## Alternatives
- **Anonymized production data in staging by default** — rejected: re-identification risk; synthetic is the default.
- **Ad-hoc developer test data from production** — rejected: prohibited outright.

## Consequences
Lower environments are safe by construction; realistic testing uses synthetic fixtures and, where justified,
approved anonymized data under gated exception.

## Impacts
- **Security:** eliminates the most common data-leak path (prod copies in dev).
- **Privacy:** protects patient/medical and PII data; aligns with the pilot data boundary and MED prohibition.
- **Tenant isolation:** no cross-tenant real data appears in shared lower environments.
- **Database:** fixtures are synthetic and tenant-scoped; no production dump restore into lower environments.
- **Operational:** clear rule for seeding, refresh, and reset; exceptions are auditable.
- **Cost:** negligible; synthetic generation is cheap — planning only.

## Verification / fitness function
`check-step4-coverage.sh` asserts the synthetic-default policy and the "no production data in local/test/CI"
prohibition (V4-ENV-02). Runtime enforcement is a future implementation gate.

## Related
Requirement: Master Source v2.4.0 §68, §43; PRD v1.3.0. Application rule: AFR-088. Rules: 23, 07, 03, 18.
ADRs: 0029 (data classification), 0034, 0037.

## Evidence
`docs/environments/DATA_POLICY_BY_ENVIRONMENT.md`, `docs/environments/ENVIRONMENT_MATRIX.md`.

## Non-claims
No environment contains any data yet. No anonymization pipeline is implemented. This is policy, not runtime enforcement.

## Rollback
Policy can be tightened at any time; loosening requires documented owner approval and a Master Source update.
