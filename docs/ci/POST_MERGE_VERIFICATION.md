# Post-Merge Verification — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.4. Rule: `.claude/rules/28`. ADR 0044. AFR-115.

`main-post-merge.yml` runs on `push: main` and performs **integrity verification only** — it does not re-run the
full release suite that already passed on the merged PR head (CI evidence is bound to the tested SHA, ADR 0042).

## What it runs
- Repository identity + canonical pointer present (`makemesick91-code/aish_agentic_ai` in `CLAUDE.md`).
- Source authority + version consistency (`scripts/docs/check-version-consistency.sh`).
- Critical secret scan (`scripts/docs/secret-scan.sh`).
- Workflow syntax + security (`scripts/ci/validate-workflow-security.sh`).
- Records post-merge evidence to `docs/evidence/cicd-ctrl-1/ci/post-merge-last.txt`.

## What it does NOT run
- The full documentation-as-code aggregate (`scripts/docs/validate.sh`) or `scripts/ci/full-local.sh`. This is
  asserted by `scripts/ci/validate-ci-topology.sh` (CI-POST-01).

## Concurrency
`concurrency: main-post-merge` with `cancel-in-progress: false` — a running post-merge verification is not cancelled
by a later push unless there is a strong, documented reason. Target runtime is far below the full release CI.
