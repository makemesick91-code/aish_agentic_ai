# Release Manifest — Documentation Foundation — Aish Agentic AI

Rule: `.claude/rules/13`. Canonical repository: `https://github.com/makemesick91-code/aish_agentic_ai`
(normalized `makemesick91-code/aish_agentic_ai`).

## Release identity
| Field | Value |
|-------|-------|
| Release | Documentation & Claude Rules Foundation |
| Target tag | `aish-agentic-ai-docs-foundation-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `chore/aish-agentic-ai-documentation-foundation` |
| Bootstrap commit | `516d1bd` (README + .gitignore; NOT a GO) |
| Merge commit | `ba1c80facf2b8fb015e2fdcaa5235daa04f60fbe` (PR #1) |
| Tag object / peeled commit | `0937ce2758e86317678752e236eaedc85039d94b` (annotated) / `ba1c80f…` |
| GO status | **GO TAGGED — 2026-07-13**, exact-match verified local + remote (`TAG_VERIFICATION.md`) |

## Scope
Canonical source preservation + checksums · normalized Master Source v2.1.1 + PRD v1.0.1 · root `CLAUDE.md` ·
16 `.claude/rules/` · `.claude/settings.json` + safety hook · 6 review subagents · 4 project skills ·
MCP governance (`.mcp.json`) · deterministic Graphify index + scripts · docs architecture (product,
architecture, security, ai, integrations, quality, operations, tooling, decisions, status, release) ·
7 ADRs · coverage + traceability matrices · validation scripts · CI workflow · audit evidence · living
Master Source records.

## Out of scope
Application source/runtime/database · deployment · Google API production credentials · pilot/production
readiness. **Application implementation: NOT STARTED.**

## Security controls
No secrets committed; secret scan + GitHub push protection; least-privilege MCP/skills/subagents; safety
hook blocking force-push/tag-deletion/secret-reads; tenant-isolation/privacy/prompt-injection foundations documented.

## Rollback
Documentation/config-only; revert the merge commit with a new commit (no force-push, no history rewrite);
the immutable GO tag is never moved; `main` bootstrap remains the PR base (`../operations/INCIDENT_AND_ROLLBACK_BASELINE.md`).

## Follow-ups (non-blocking)
- Install trusted **Graphify** (record source/version/license/checksum) to replace the deterministic index (OD-adjacent).
- Install a **Limit Saver / usage-limit-reducer** skill if desired; fallback protocol is active meanwhile.
- Resolve open decisions OD-1…OD-10 (`../product/OPEN_DECISIONS.md`) in subsequent product steps.
