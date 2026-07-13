---
id: "23"
title: Environment Separation and Promotion
domain: environments
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.4.0 §68 (Environment strategy)"
  - "PRD v1.3.0 (environment requirements)"
  - "ADRs 0034, 0035, 0036; AFR-087..089, 092, 093, 094"
supersede: "Only via a versioned Master Source update; environment isolation and no-production-data rules are permanent."
---

# Rule 23 — Environment Separation and Promotion

## Purpose
Guarantee isolated environments, a safe promotion path, and that real production data never leaks into lower environments.

## Scope
The six environments, their isolation, data policy, naming, promotion, and local/CI planning.

## Rules
- Six environments **MUST** be defined — **local, test, CI, staging, pilot, production** — each with documented
  isolation of database, redis, queue, and storage; resource names **MUST NOT** collide across environments
  (AFR-087, AFR-089).
- Raw production data **MUST NOT** be copied to local, test, CI, or staging; synthetic data is the default. Staging
  uses synthetic or formally anonymized data only; pilot uses approved minimum data; production uses approved tenant
  data under production controls. Exceptions **MUST** require documented approval + secure anonymization (AFR-088).
- Promotion **MUST** follow `local → test/CI → staging → pilot → production`; there **MUST NOT** be a direct
  unreviewed deploy to pilot or production (AFR-092).
- Step 4 CI **MUST NOT** add fake Laravel runtime gates; only documentation/planning CI runs until the application
  exists (AFR-093).
- Local development **MUST** define a recommended baseline plus a fallback; no runtime is bootstrapped during
  planning steps (AFR-094).

## Required checks
- `scripts/docs/check-step4-coverage.sh` verifies all six environments, isolation language, the no-production-data
  policy, promotion policy, and the local/CI plans.

## Evidence
- `docs/environments/*`.

## Related canonical sections
- Master Source v2.4.0 §68, §43; PRD v1.3.0; ADRs 0034, 0035, 0036; rules 03, 07, 24; rule 13 (promotion/release).

## Supersession
Environment isolation and the no-production-data policy are permanent; tightening is always allowed, loosening
requires documented owner approval and a Master Source update.
