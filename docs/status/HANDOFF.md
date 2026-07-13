# Handoff — Aish Agentic AI

Updated: 2026-07-13 (Asia/Makassar). Rule: `.claude/rules/14`. For the next session/engineer.

## Where we are
Documentation & Claude Rules Foundation for `makemesick91-code/aish_agentic_ai`. `main` is bootstrapped;
all foundation content is on branch `chore/aish-agentic-ai-documentation-foundation`.

## Authority & sources
Follow `CLAUDE.md` §2. Canonical: `docs/canonical/MASTER_SOURCE.md` (v2.1.1) and `docs/canonical/PRD.md`
(v1.0.1). Originals + checksums in `docs/canonical/source/` and `docs/evidence/source-checksums/`.

## Next commands (documentation foundation)
```bash
scripts/docs/validate.sh              # run all documentation gates, capture evidence
scripts/hooks/test-guard.sh           # validate safety hook (positive + negative)
scripts/graphify/build.sh && scripts/graphify/query-smoke.sh
git add -A && git commit               # logical commits (see CONTRIBUTING.md)
git push -u origin chore/aish-agentic-ai-documentation-foundation
gh pr create --base main --fill        # open PR with the required template
# after green CI + review: gh pr merge --merge ; then annotated GO tag per docs/release/
```

## Guardrails
Never force-push, move/delete tags, weaken gates, commit secrets, or claim false completion. The GO tag
`aish-agentic-ai-docs-foundation-v1.0.0-go` attests docs/tooling readiness only, not application readiness.

## Open decisions
See `docs/product/OPEN_DECISIONS.md` (OD-1…OD-10). None resolved in this release.
