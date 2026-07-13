# ADR 0030 — Testing Strategy and Architecture Fitness Functions

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** QA / Architecture Fitness Lead
- **Rule:** `.claude/rules/09`, `20` (AFR-062, AFR-063) · **Canonical:** Master Source v2.3.0 §50, §54, §59; PRD v1.2.0 §23, §24, §30

## Context
The architecture must be verifiable and stay correct over time. Test categories and architecture fitness
functions must be defined now so implementation builds them in from the start.

## Decision
Adopt the test pyramid `tests/{Unit,Feature,Integration,Security,Performance}` plus **`tests/Architecture`** for
fitness functions. Cover Master Source §50 categories (functional, multi-tenant isolation, AI evaluation incl.
adversarial/prompt-injection/PII, security incl. IDOR/SSRF/cross-tenant, performance). Define **45 fitness
functions** ([Architecture Fitness Functions](../../architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md)); each has a
Step 3 documentation check and an implementation enforcement. Gates are never skipped, weakened, or faked.

## Alternatives
- **Tests without architecture checks** — rejected: boundaries erode silently.
- **Deferring fitness definition to implementation** — rejected: loses the design-time contract.

## Consequences
Verifiable architecture; implementation must satisfy the fitness catalog before release gates pass.

## Impacts
- **Security:** dedicated security test category + FF-SEC-*.
- **Privacy:** AI-eval covers PII/medical leakage.
- **Tenant isolation:** FF-TEN-01..14 assert per-surface controls.
- **Database:** ownership + schema fitness tests.
- **Operational:** CI runs Step 3 doc checks now; code checks later.
- **Cost:** CI minutes.

## Verification / fitness function
All 45 FFs; Step 3 gate `check-step3-coverage.sh` + `check-adr.sh`. See
[Fitness Function Catalog](../../quality/STEP_3_FITNESS_FUNCTION_CATALOG.md).

## Related
Requirement: Master Source §50, §59; PRD §23, §30. Application rule: AFR-062, AFR-063. ADRs: 0009, 0026.

## Evidence
`docs/architecture/ARCHITECTURE_FITNESS_FUNCTIONS.md`, `docs/quality/STEP_3_FITNESS_FUNCTION_CATALOG.md`.

## Non-claims
No application tests run in Step 3; only documentation-as-code gates run.

## Rollback / supersession
Test/fitness requirements may be added, never weakened; changes require an owner-approved Master Source update.
