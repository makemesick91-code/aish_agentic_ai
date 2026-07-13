# CI Architecture — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`. ADRs 0042–0046. AFR-105..126.
**Status:** CI/release governance CONFIGURED. Application implementation **NOT STARTED** — runtime suites are
routed but NOT-YET-AVAILABLE.

## Goal
Minimize redundant CI without weakening any security, tenant-isolation, privacy, documentation, or release gate.
A CI PASS is valid only for the exact tested commit SHA.

## Workflows
| Workflow | Trigger | Purpose | Full suite? |
|----------|---------|---------|-------------|
| `pr-ci.yml` | `pull_request` (opened/synchronize/reopened/ready_for_review) | Draft ⇒ fast CI; ready ⇒ one full release CI on the final head | Ready only |
| `main-post-merge.yml` | `push: main` | Lightweight integrity verification (identity, version/authority, secret scan, workflow security) | No |
| `full-ci-manual.yml` | `workflow_dispatch` | On-demand revalidation / incident investigation | Yes (manual) |

Old `documentation-foundation.yml` is retired and preserved as non-executable evidence
(`docs/evidence/cicd-ctrl-1/workflows/documentation-foundation.yml.txt`).

## `pr-ci.yml` job graph
```
classify-changes ──► draft-fast-ci        (if draft)
                └──► full-documentation-ci (if ready)
                └──► workflow-security-ci  (if ready AND workflow/full route)
                                    └──► Required Gate  (if: always())
```
- `classify-changes` computes categories + routing flags from the PR base/head SHA (internal routing — no
  top-level `paths:` filter on this mandatory workflow).
- `Required Gate` is the single stable required check (`pr-ci / Required Gate`). It fails on any job failure,
  unexpected cancel, missing classification, or (on a ready PR) a full-documentation job that did not succeed.

## Concurrency
`concurrency: pr-ci-<PR number>` with `cancel-in-progress: true` — a new head cancels the prior run for the same PR.

## Security baseline
Actions pinned to immutable SHAs; default `permissions: contents: read`; no `pull_request_target`; per-job
`timeout-minutes`; secret scan and workflow-security gates cannot be optimized away. See [CI Security Baseline](CI_SECURITY_BASELINE.md).

## Related
[Event & Trigger Matrix](CI_EVENT_AND_TRIGGER_MATRIX.md) · [Change Classification](CI_CHANGE_CLASSIFICATION.md) ·
[Test Routing Matrix](CI_TEST_ROUTING_MATRIX.md) · [Run Budget](CI_RUN_BUDGET.md) ·
[Draft→Release Workflow](DRAFT_TO_RELEASE_WORKFLOW.md) · [Required Check Governance](REQUIRED_CHECK_GOVERNANCE.md) ·
[Post-merge](POST_MERGE_VERIFICATION.md) · [Post-tag Evidence](POST_TAG_EVIDENCE_POLICY.md) ·
[Rollback](CI_ROLLBACK_PLAN.md) · [Troubleshooting](CI_TROUBLESHOOTING.md).
