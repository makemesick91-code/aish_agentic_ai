# ADR 0044 — Post-Merge and Post-Tag Verification

- **Status:** Accepted (2026-07-13, Asia/Makassar) — CICD-CTRL-1; CI governance CONFIGURED, application NOT STARTED
- **Owner:** Release Governance Lead
- **Rule:** `.claude/rules/28` (AFR-115, AFR-116, AFR-117) · **Canonical:** Master Source v2.5.0 §69; rule 13

## Context
The full release suite already passed on the tested PR head before merge. Re-running it on the `push` to `main` and
on tag creation is redundant, because CI evidence is bound to the tested SHA (ADR 0042). Prior steps also recorded
post-tag evidence via a second full-CI pull request, which triggered another full run.

## Decision
`main-post-merge.yml` (push:main) runs **lightweight integrity verification only** — canonical identity, version/
authority consistency, critical secret scan, workflow security — never the full release aggregator. Tag creation
runs **no** full CI; `scripts/release/verify-immutable-tag.sh` proves local main = origin/main = merge commit =
local tag peeled = remote tag peeled and that prior immutable tags are unchanged. Post-tag evidence defaults to a
**GitHub Release artifact**, not a second full-CI evidence PR. If repository policy later requires evidence in Git,
it goes in the next normal planned PR as historical release metadata (a documented exception), never a full-CI run.

## Alternatives
- **Full CI on push:main** — rejected: redundant; evidence is already SHA-bound to the PR head.
- **Full CI on tag** — rejected: a tag points at an already-tested commit; only exact-match verification is needed.
- **Evidence-only full-CI PR** — rejected as the default: it re-runs full CI for no new signal.

## Consequences
Lower runner minutes and faster post-merge signal; release evidence lives in a GitHub Release. Post-merge must stay
strictly lightweight (enforced by topology validation).

## Impacts
- **Security:** critical secret scan and workflow-security still run post-merge; no security regression.
- **Privacy:** verification artifacts contain SHAs and tag refs only — no PII.
- **Tenant isolation:** unaffected.
- **Database:** none.
- **Operational:** immutable-tag exact-match evidence; prior tags proven unchanged on every release.
- **Cost:** removes a redundant full run per merge and per release.

## Verification / fitness function
`scripts/ci/validate-ci-topology.sh` asserts `main-post-merge.yml` does not call `validate.sh`/`full-local.sh`;
`scripts/release/verify-immutable-tag.sh` proves exact-match. CI-POST-01, CI-TAG-01/02.

## Related
Requirement: Master Source v2.5.0 §69; PRD v1.3.0. Application rules: AFR-115, AFR-116, AFR-117. Rules: 28, 13, 11.
ADRs: 0007 (immutable GO-tag semantics), 0042.

## Evidence
`.github/workflows/main-post-merge.yml`, `scripts/release/verify-immutable-tag.sh`;
`docs/ci/POST_MERGE_VERIFICATION.md`, `docs/ci/POST_TAG_EVIDENCE_POLICY.md`.

## Non-claims
Lightweight verification does not claim the application is deployed or runtime-verified. A GitHub Release artifact
attests tag exact-match and CI evidence only — not deployment, pilot, or production readiness.

## Rollback
Re-enable a fuller post-merge job by editing `main-post-merge.yml` (recorded decision). Moving or deleting a tag is
prohibited; a mismatched tag is NO-GO and is corrected only via a new, correctly-created tag.
