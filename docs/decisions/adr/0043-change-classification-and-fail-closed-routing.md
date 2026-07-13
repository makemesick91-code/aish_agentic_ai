# ADR 0043 — Change Classification and Fail-Closed Routing

- **Status:** Accepted (2026-07-13, Asia/Makassar) — CICD-CTRL-1; CI governance CONFIGURED, application NOT STARTED
- **Owner:** DevSecOps Engineer
- **Rule:** `.claude/rules/28` (AFR-113, AFR-114, AFR-118, AFR-119, AFR-125) · **Canonical:** Master Source v2.5.0 §69

## Context
Path-aware CI must not weaken mandatory checks. GitHub top-level `paths:` filters on a required workflow can leave a
required check permanently "pending" (never reported), which breaks branch protection. Routing must live inside the
workflow and must never let unknown or sensitive changes skip security.

## Decision
`scripts/ci/classify-changes.sh` maps changed files (between the PR base and head SHA) to categories —
documentation, governance, workflow, backend, frontend, database, security, ai, integration, infrastructure,
dependency, test, release, unknown, mixed — and emits routing flags. It **fails closed**: unknown/mixed changes and
any of security/backend/database/dependency/integration/infrastructure/release run the full safe suite. No mandatory
workflow uses a top-level `paths:` filter (CI-PRINCIPLE-09). Runtime suites (backend/frontend/database) are routed
but recorded NOT-YET-AVAILABLE until the application exists — no fake Laravel runtime gate (AFR-093, AFR-125).

## Alternatives
- **Top-level `paths:` filter on the required workflow** — rejected: can leave the required check unreported.
- **Trust-the-author routing** — rejected: not deterministic; unsafe for security-sensitive paths.
- **Run everything always** — rejected: the redundancy CICD-CTRL-1 removes; still the fallback when unsure.

## Consequences
Deterministic, testable routing; security is never routed away; the classifier is unit-tested and self-passing.

## Impacts
- **Security:** security/tenancy/config/dependency paths always trigger the full safe suite.
- **Privacy:** classifier output contains only paths/flags — no file content or PII.
- **Tenant isolation:** tenancy-sensitive paths route to isolation tests when the application exists.
- **Database:** schema/migration paths route to migration + fresh-install checks (future runtime).
- **Operational:** classification evidence written to `docs/evidence/cicd-ctrl-1/change-classifier/`.
- **Cost:** cheap categories (docs) skip runtime suites; unknown/mixed pay for the full safe suite.

## Verification / fitness function
`scripts/ci/test-change-classifier.sh` (docs-only ≠ full; unknown/backend/database/dependency/mixed ⇒ full; space
and deleted-file safety). CI-CLASS-01/02.

## Related
Requirement: Master Source v2.5.0 §69; PRD v1.3.0. Application rules: AFR-113, AFR-114, AFR-118, AFR-119, AFR-125.
Rules: 28, 04, 23. ADRs: 0042, 0045.

## Evidence
`scripts/ci/classify-changes.sh`, `scripts/ci/test-change-classifier.sh`; `docs/ci/CI_CHANGE_CLASSIFICATION.md`,
`docs/ci/CI_TEST_ROUTING_MATRIX.md`.

## Non-claims
Routing does not claim runtime suites exist; backend/frontend/database suites are planned and NOT-YET-AVAILABLE.
It does not claim the application is implemented or deployed.

## Rollback
Fall back to running the full safe suite unconditionally (already the fail-closed default). Loosening routing to
skip security is prohibited without an owner-approved Master Source update.
