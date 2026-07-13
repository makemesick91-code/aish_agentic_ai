---
id: "28"
title: Safe CI Runtime Control and Single-Final-Head Release Gate
domain: ci-release-governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.5.0 §69 (CICD-CTRL-1); §50, §54, §66.10"
  - "PRD v1.3.0 (unchanged); NFR-CI-001..006 recorded in Master Source §6"
  - "ADRs 0042–0046; AFR-105..126; rules 04, 09, 13, 15, 25, 27"
supersede: "Permanent; security/tenant-isolation/release gates cannot be weakened. Superseded only by a higher-version Master Source update preserving these guarantees."
---

# Rule 28 — Safe CI Runtime Control and Single-Final-Head Release Gate

## Purpose
Minimize redundant CI runs without weakening any security, tenant-isolation, privacy, documentation, or release
gate. Bind every CI PASS to the exact tested commit SHA and keep CI-efficiency claims evidence-based.

## Scope
GitHub Actions workflows, change classification, local gates, required-check enforcement, post-merge/post-tag
verification, and CI security. Applies to `.github/workflows/`, `scripts/ci/`, and `scripts/release/`.

## Rules
- A CI PASS **MUST** be valid only for the exact tested commit SHA; a result **MUST NOT** be reused after the head
  changes (AFR-105, AFR-109). Any new commit after a full CI **MUST** trigger a new full CI.
- Validation **MUST** be runnable locally (`scripts/ci/fast-local.sh` during work; `scripts/ci/full-local.sh`
  before ready-for-review); CI **MUST NOT** be the substitute for local validation (AFR-106).
- Feature PRs **MUST** open as drafts; a draft PR **MUST** run fast CI only. After review the PR **MUST** be marked
  ready and one full release CI **MUST** be targeted at the final head (AFR-107, AFR-108).
- A feature branch **MUST NOT** run full CI separately for `push` and `pull_request` on the same SHA; stale runs
  for the same PR **MUST** be cancelled on a new head (AFR-110, AFR-111).
- There **MUST** be exactly one stable required gate (`pr-ci / Required Gate`) that always reports a conclusion; a
  mandatory workflow **MUST NOT** be skipped via a top-level path filter (classification is internal); unknown/
  mixed changes **MUST** fail closed to the full safe suite (AFR-112, AFR-113, AFR-114).
- Push to main **MUST** run lightweight integrity verification only; tag creation **MUST NOT** run full CI;
  post-tag evidence **MUST NOT** trigger full CI and defaults to a GitHub Release artifact (AFR-115..117).
- `[skip ci]`/skip directives **MUST NOT** bypass mandatory checks; secret scan, workflow-security, tenant-
  isolation, and release-integrity gates **MUST NOT** be removed for speed (AFR-118, AFR-119).
- Actions **MUST** be pinned to an immutable 40-hex commit SHA; default `GITHUB_TOKEN` permission **MUST** be
  read-only; `pull_request_target` **MUST NOT** execute untrusted PR code with a privileged token (AFR-120..122).
- `main` **SHOULD** enforce a ruleset requiring the stable required gate and blocking force-push/deletion; admin
  bypass **MUST NOT** be used (AFR-123). A run-budget **MUST NOT** turn a failure into a success (AFR-124).
- Runtime suites (backend/frontend/database) **MUST** be routed but recorded NOT-YET-AVAILABLE until the
  application exists; there **MUST NOT** be a fake Laravel runtime gate (AFR-125).
- CI-efficiency claims **MUST** be backed by actual GitHub run evidence; reruns after a failure or corrective
  commit **MUST** be reported truthfully — no false "one run forever" claim (AFR-126).

## Required checks
- `scripts/ci/test-change-classifier.sh`, `scripts/ci/test-required-gate.sh`, `scripts/ci/validate-ci-topology.sh`,
  `scripts/ci/validate-workflow-security.sh` (all in `scripts/ci/fast-local.sh` and `full-local.sh`), plus the full
  documentation-as-code suite (`scripts/docs/validate.sh`).

## Evidence
- `docs/ci/*`, `docs/quality/CICD_CTRL_1_*`, `docs/release/CICD_CTRL_1_*`, `docs/evidence/cicd-ctrl-1/*`,
  `.github/workflows/pr-ci.yml`, `main-post-merge.yml`, `full-ci-manual.yml`.

## Related canonical sections
- Master Source v2.5.0 §69, §50, §54, §66.10; PRD v1.3.0; ADRs 0042–0046; AFR-105..126; rules 04, 09, 13, 15, 25, 27.

## Supersession
Permanent. The SHA-bound-evidence, fail-closed-routing, no-security-weakening, and truthful-claim constraints
cannot be weakened; superseded only by a higher-version Master Source update that preserves these guarantees.
