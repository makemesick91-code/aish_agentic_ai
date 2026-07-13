# CICD-CTRL-1 — GO / WATCH / NO-GO

Canonical: Master Source v2.5.0 §69. Rule: `.claude/rules/28`, `09`, `13`, `19`.

## GO criteria (all required, with evidence)
- Baseline audit complete; duplicate-trigger situation documented truthfully.
- Unified `pr-ci.yml` active; draft PR runs fast CI only; ready PR runs one full release CI on the final head.
- Final full CI is SHA-bound; a commit change forces revalidation; concurrency cancels stale runs.
- No duplicate feature push+PR full CI; internal fail-closed routing; unknown ⇒ full safe suite.
- Stable `pr-ci / Required Gate` exists and is enforced by the `main` ruleset (force-push/deletion blocked).
- Workflow security passes: actions pinned, least-privilege permissions, no `pull_request_target`, per-job timeout.
- Existing mandatory gates preserved (documentation, secret scan, ADR/AGENTS/codex, graphify).
- Push to main runs lightweight verification only; tags run no full CI; post-tag evidence via GitHub Release.
- Rules recorded in Master Source, ADRs 0042–0046, AFR-105..126, Claude rule 28, AGENTS; traceability has no
  critical orphan; secret scan passes.
- Independent review has no unresolved BLOCKER/HIGH; PR merged; annotated tag exact-matches the merge commit; prior
  tags unchanged; evidence complete; no false "one run forever" claim.

## WATCH (permitted, does not block GO)
- External Limit Saver unavailable → project fallback active.
- Branded Graphify unavailable → deterministic index passes.
- Codex CLI unavailable → static validation only.
- Backend/frontend/database runtime suites NOT-YET-AVAILABLE (application NOT STARTED).
- GitHub billing-minute API unavailable → duration estimates (APPROXIMATE FROM RUN DURATION).
- Merge queue / matrix sharding deferred until runtime justifies them.

## NO-GO / BLOCKED
Wrong repository; a prior tag changed; duplicate full CI for feature push+PR on the same SHA; full CI on a draft
without a documented exception; a ready PR with no full CI; a required check that can stay missing/pending; the
required gate not enforced; routing that skips mandatory security; CI reuse after a SHA change; broad token write;
untrusted PR code with a privileged token; an unpinned action without an accepted exception; a removed mandatory
gate; a hidden CI failure; bypassed branch protection; an unmerged PR; a tag mismatch; or fabricated evidence.

## Current status
CI/release governance CONFIGURED and locally verified. GO is asserted only after the merged commit, enforced
required gate, and exact-match annotated tag are all evidenced. Application implementation, deployment, pilot, and
production readiness remain **NOT STARTED**.
