# CICD-CTRL-1 — Validation Catalog

Rule: `.claude/rules/28`, `.claude/rules/09`. Each `CI-*` fitness function, the script that realises it, what it
asserts, and where its evidence lands. All validators are read-only and deterministic; none mutate the repository.

| ID | Validator | Asserts | Evidence |
|----|-----------|---------|----------|
| CI-DRAFT-01 | `pr-ci.yml` `draft-fast-ci` + `scripts/ci/test-required-gate.sh` | a draft PR runs fast CI only (full jobs skipped); draft fast-only passes the gate | draft run job list (`ci/draft-fast-run.txt`) |
| CI-FULL-01 | `pr-ci.yml` `full-documentation-ci` + `scripts/ci/test-required-gate.sh` | a ready PR runs one full release CI on the final head; ready requires full-doc success | ready run job list |
| CI-CLASS-01 | `scripts/ci/test-change-classifier.sh` | docs-only ≠ full; unknown/backend/database/dependency/mixed ⇒ full safe suite; space + deleted-file safety | `local-validation/*-change-classifier-tests.log` |
| CI-CLASS-02 | `scripts/ci/classify-changes.sh` | runtime suites routed but NOT-YET-AVAILABLE; deterministic JSON output | `change-classifier/last-classification.json` |
| CI-GATE-01 | `scripts/ci/test-required-gate.sh` | draft fast-only passes; ready requires full-doc success; any failure/cancel/missing ⇒ fail closed | `local-validation/*-required-gate-tests.log` |
| CI-TOPO-01 | `scripts/ci/validate-ci-topology.sh` | `pr-ci.yml` triggers on pull_request incl. ready_for_review, not feature push | `local-validation/*-ci-topology.log` |
| CI-TOPO-02 | `scripts/ci/validate-ci-topology.sh` | per-PR concurrency with cancel-in-progress | same |
| CI-TOPO-03 | `scripts/ci/validate-ci-topology.sh` | single required gate; only pr-ci runs the full doc suite on PR | same |
| CI-POST-01 | `scripts/ci/validate-ci-topology.sh` | `main-post-merge.yml` does not run the full release aggregator | same |
| CI-SEC-01 | `scripts/ci/validate-workflow-security.sh` | no `[skip ci]`/skip-checks enablement | `local-validation/*-workflow-security.log` |
| CI-SEC-02 | `scripts/docs/secret-scan.sh` | no committed secret patterns; no tracked `.env` | `validation/secret-scan.log` |
| CI-SEC-03 | `scripts/ci/validate-workflow-security.sh` | every action pinned to a 40-hex SHA | wf-security log |
| CI-SEC-04 | `scripts/ci/validate-workflow-security.sh` | top-level `permissions:` present; no `write-all` | wf-security log |
| CI-SEC-05 | `scripts/ci/validate-workflow-security.sh` | no `pull_request_target`; no `curl\|sh`; per-job `timeout-minutes` | wf-security log |
| CI-TAG-01 | `scripts/release/verify-immutable-tag.sh` | local main = origin/main = merge = local tag peeled = remote tag peeled | `release/tag-verification.*` |
| CI-TAG-02 | `scripts/release/verify-immutable-tag.sh` | prior immutable tags unchanged; post-tag evidence via GitHub Release | `release/tag-verification.json` |
| CI-BUD-01 | `docs/ci/CI_RUN_BUDGET.md` + reviewer | run budget observed; a budget never turns failure into success | `ci/*`, run history |
| CI-EVID-01 | `scripts/ci/audit-ci-runs.sh` | run/duplicate-SHA/trigger evidence derived from real GitHub runs | `baseline/*` |
| CI-SHA-01 | `pr-ci.yml` + `required-gate-decision.sh` | a PASS binds to the exact tested head SHA | run history |
| CI-LOCAL-01 | `scripts/ci/fast-local.sh`, `full-local.sh` | local gates reproduce CI checks before ready-for-review | `local-validation/*` |
| CI-RULE-01 | `main` ruleset + live PR | stable `pr-ci / Required Gate` enforced; force-push/deletion blocked | `ruleset/*` |

## Aggregation
- `scripts/ci/fast-local.sh` runs the fast subset (classifier/gate/topology/workflow-security/secret-scan/
  frontmatter/hook tests). `scripts/ci/full-local.sh` adds `scripts/docs/validate.sh` (the full documentation-as-code
  suite). CI runs the same via `pr-ci.yml`. No gate may be skipped, weakened, or faked (AFR-119, AFR-124).
