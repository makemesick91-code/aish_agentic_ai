# ADR 0026 — CI/CD Architecture

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** DevOps / QA Architect
- **Rule:** `.claude/rules/09`, `13`, `20` (AFR-054, AFR-072) · **Canonical:** Master Source v2.3.0 §54, §66.10; PRD v1.2.0 §24

## Context
CI must gate every change with least-privilege permissions, keep existing docs/Step-2 gates, and add Step 3
architecture/ADR/Codex/traceability gates — without faking or weakening any gate.

## Decision
Extend GitHub Actions to run documentation gates, Step 2 coverage, **Step 3 architecture validation, ADR
validation, module-boundary + tenant-isolation coverage, AFR coverage, AGENTS validation, Codex rule/hook static
tests, skills validation, traceability, graph/index drift + query smoke, secret scan, and shell lint**, with
`permissions: contents: read`. A future app pipeline (build/test/deploy) is added when code exists (ADR 0027,
0032). No Codex GitHub Action is added merely for appearance. See
[Environment Strategy](../../architecture/ENVIRONMENT_STRATEGY.md).

## Alternatives
- **Manual validation only** — rejected: not repeatable; can be skipped.
- **Broad workflow permissions** — rejected: least privilege required.

## Consequences
Deterministic, comprehensive gating; mandatory CI must be green before merge.

## Impacts
- **Security:** secret scan + least-privilege workflow token.
- **Privacy:** evidence never prints secrets.
- **Tenant isolation:** coverage gate asserts per-surface controls documented.
- **Database:** none.
- **Operational:** one aggregate Step 3 validation entrypoint.
- **Cost:** CI minutes only.

## Verification / fitness function
FF-DOC-01..03 plus all Step 3 gates. Evidence recorded under `docs/evidence/step-3/ci/`.

## Related
Requirement: Master Source §54, §66.10; PRD §24. Application rule: AFR-054, AFR-072. ADRs: 0030, 0032.

## Evidence
`.github/workflows/`, `scripts/docs/validate.sh`, `docs/evidence/step-3/ci/`.

## Non-claims
No application build/test/deploy pipeline runs in Step 3; only documentation-as-code gates run.

## Rollback / supersession
Gates may be added, never weakened; changes require an owner-approved Master Source update.
