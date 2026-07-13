# CI Troubleshooting — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`.

| Symptom | Likely cause | Action |
|---------|--------------|--------|
| Required check stuck "pending" | Someone added a top-level `paths:` filter to a mandatory workflow | Remove it; routing is internal (ADR 0043). Run `scripts/ci/validate-ci-topology.sh`. |
| Draft PR ran the full suite | `draft-fast-ci`/`full-documentation-ci` `if:` conditions edited | Check `github.event.pull_request.draft` gates in `pr-ci.yml`; verify with a draft PR. |
| Ready PR had no full CI | `ready_for_review` missing from triggers, or full job `if:` wrong | Ensure `types:` includes `ready_for_review`; run `validate-ci-topology.sh`. |
| Two full runs for one SHA | A `push:` trigger crept onto `pr-ci.yml` | Remove it (AFR-110); `validate-ci-topology.sh` fails closed on this. |
| Stale runs not cancelled | Concurrency group/`cancel-in-progress` changed | Restore `concurrency: pr-ci-<PR number>` + `cancel-in-progress: true`. |
| `validate-workflow-security` fails on an action | Unpinned action tag | Pin to a 40-hex SHA verified against the upstream tag. |
| `check-version-consistency` fails | Master Source version / snapshot / CLAUDE.md / AGENTS.md drift | Keep active version, snapshot filename, and CLAUDE.md/AGENTS.md references in sync. |
| Post-merge running the full suite | `main-post-merge.yml` calls `validate.sh`/`full-local.sh` | Remove it (AFR-115); post-merge is lightweight only. |
| classifier says `unknown` for a known path | New path type not mapped | Add a mapping in `classify-changes.sh` **and** a test in `test-change-classifier.sh` (still fail closed until then). |

## First response when full CI fails
1. Read the failing job log; reproduce locally with `scripts/ci/full-local.sh`.
2. Fix the root cause and commit — do **not** disable the gate or mark the run flaky without evidence.
3. Let full CI re-run on the new head; record the rerun reason. A rerun after a real failure is legitimate (AFR-124,126).

## Manual revalidation
Use the `full-ci-manual.yml` `workflow_dispatch` (with a `reason`) for incident investigation or release-candidate
recheck. It does not replace the required PR check.
