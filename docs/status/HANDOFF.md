# Handoff — Aish Agentic AI

Updated: 2026-07-13 (Asia/Makassar). Rule: `.claude/rules/14`. For the next session/engineer.

## Where we are
Step 3 — Application Architecture & ADR Foundation for `makemesick91-code/aish_agentic_ai`. Steps 1 and 2 are
MERGED and GO TAGGED. Step 3 content is on branch `docs/step-3-application-architecture-adr-foundation`
(branched from `origin/main`). All local Step 3 gates pass; release/PR/merge/tag remain.

## Authority & sources
Follow `CLAUDE.md` §2 and `AGENTS.md`. Canonical: `docs/canonical/MASTER_SOURCE.md` (v2.3.0),
`docs/canonical/PRD.md` (v1.2.0), ADRs `docs/decisions/adr/0009`–`0032`,
`docs/architecture/APPLICATION_FOUNDATION_RULES.md` (AFR-001..072). Historical versions preserved, never deleted.

## Next commands (Step 3 release)
```bash
scripts/docs/validate.sh                 # all gates incl. step3-coverage/adr/agents/codex
git add -A && git commit                 # logical commits (see §26 of the Step 3 prompt / CONTRIBUTING.md)
git push -u origin docs/step-3-application-architecture-adr-foundation
gh pr create --base main --title "docs: establish Step 3 application architecture and ADR foundation" --fill
gh pr checks <PR>                        # wait for real CI conclusion
# after green CI + independent review + (human) merge authorization:
gh pr merge <PR> --merge
git checkout main && git pull --ff-only origin main
# then annotated GO tag on the merged commit:
git tag -a aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go -m "…" <merge_commit>
git push origin aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go
```

## Guardrails
Never force-push, move/delete tags, weaken gates, commit secrets, or claim false completion. The Step 3 GO tag
attests documentation/architecture/tooling readiness only — **not** application implementation, deployment,
live integration, pilot readiness, or production readiness (all NOT STARTED). Baseline tags `ba1c80f` /
`abf1d00` must remain unchanged.

## Open decisions (WATCH)
`docs/architecture/ARCHITECTURE_OPEN_DECISIONS.md`: OD-01 RLS, OD-02 provider, OD-03 AI extraction, OD-04
frontend, OD-05 branded Graphify, OD-06 Limit Saver, OD-07 Codex CLI, OD-08 Google readiness, OD-09 RPO/RTO.

## Next step after Step 3 GO
Step 4 — Domain, Branding, Environment, and SaaS Foundation Implementation Planning (no feature code in Step 3).
