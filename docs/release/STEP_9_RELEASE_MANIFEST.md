# Step 9 — Release Manifest

**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline (Master Source v2.11.0 §75)
**Target GO tag:** `aish-agentic-ai-step-9-competitive-gap-architecture-rebaseline-v1.0.0-go`
**Base branch:** `main` · **Feature branch:** `feature/step-9-competitive-gap-architecture-rebaseline`
**Type:** product-governance + architecture-lock + roadmap-lock (documentation/governance only — no application feature,
migration, table, or runtime; Step 8 preserved)
**Canonical repo:** makemesick91-code/aish_agentic_ai

## 1. Scope
Evidence-based capability audit, ten-vendor competitor matrix + gap register, Experience OS architecture lock (domain
boundaries, Customer 360 identity, Experience Event Ledger preserving the Step 8 timeline, channel adapters, AI
tool-permission, additive migration), security threat model, observability contract, Wave 1–3 roadmap lock, and the
execution-ready Step 10 Customer 360 implementation contract.

## 2. Exclusions (NOT in this release)
No Customer 360, transaction ingestion, recovery, AI, Google Review, omnichannel, knowledge base, analytics, public API,
or payment implementation; no migration/backfill run; no deployment/pilot/production. All remain **NOT STARTED**.

## 3. Delivered artifacts
- Governance: Master Source **v2.11.0** (§75), Agentic Experience OS PRD Addendum **v1.0.0**, Claude **rule 34**,
  ADRs **0063–0068**, **AFR-211..238**, VERSION_MATRIX, DECISION_LOG D-034, CHANGELOG v2.11.0.
- Product: capability inventory; competitor matrix; gap register; Experience OS roadmap; Step 10 contract.
- Architecture (`docs/architecture/experience-os/`): domain boundary map; Customer 360 identity; Experience Event
  Ledger; channel adapter; AI tool permission; migration strategy; index README.
- Security/ops: `docs/security/STEP_9_THREAT_MODEL.md`; `docs/operations/EXPERIENCE_OS_OBSERVABILITY_CONTRACT.md`.
- Verification: `scripts/docs/check-step9-coverage.sh` (wired into `scripts/docs/validate.sh`);
  `scripts/docs/verify-step-9.sh`.
- Evidence: `docs/evidence/step-9/`; `docs/quality/STEP_9_GO_WATCH_NO_GO.md`.

## 4. Local gate evidence (pre-merge)
- `scripts/docs/validate.sh`: ALL GATES PASSED (incl. step9-coverage, ADR structure, internal links, version
  consistency, secret scan, AGENTS/CLAUDE no-drift, graphify determinism).
- `scripts/ci/test-change-classifier.sh`, `test-required-gate.sh`, `validate-workflow-security.sh`,
  `validate-ci-topology.sh`: PASS.
- `scripts/docs/verify-step-9.sh`: PASS (Step 8 hermetic 354 passed; no runtime/schema change).
- `vendor/bin/pint --test`: passed. `vendor/bin/phpstan analyse`: no errors.

## 5. Release lifecycle (executed under rule 13/28)
Draft PR (fast CI) → review → mark ready (one full CI on final head: full-documentation-ci + workflow-security-ci +
backend-runtime-ci) → merge on exact-SHA green → fresh-clone verification on merge SHA → annotated immutable GO tag at
that SHA → GitHub Release → post-tag evidence sync (no tag move). Merge SHA, CI run id, tag object, peeled commit, and
release URL are recorded in `docs/release/STEP_9_TAG_VERIFICATION.md`.
