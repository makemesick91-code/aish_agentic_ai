# Contributing — Aish Agentic AI

This repository is governed as documentation-as-code. Read `CLAUDE.md` and the relevant
`.claude/rules/` file before making changes.

## Ground rules

1. **Canonical repository only.** Work must target `makemesick91-code/aish_agentic_ai`. Verify the
   normalized `origin` before committing. Never repoint another project's remote.
2. **Source authority.** Follow the authority order in `CLAUDE.md` §2. The Master Source and PRD are
   canonical; derived docs and knowledge-graph indexes never override them.
3. **No secrets.** Never commit credentials, tokens, `.env`, dumps, or private keys (see `SECURITY.md`).
4. **Truthful status only.** Use the status vocabulary in `CLAUDE.md` §5, always backed by evidence.
5. **Living Master Source.** Any material decision requires a Master Source impact analysis and, if
   material, a versioned update + changelog (`.claude/rules/12`).

## Branching

- Default/integration branch: `main`.
- Documentation foundation work: `chore/aish-agentic-ai-documentation-foundation`.
- Use short, descriptive branch names. Never work directly on `main` except the documented one-time
  empty-repository bootstrap. Never force-push shared branches.

## Local validation

Run the documentation gates before opening a pull request:

```bash
scripts/docs/validate.sh                     # runs all gates below in order
scripts/docs/check-version-consistency.sh
scripts/docs/check-links.sh
scripts/docs/check-rule-frontmatter.sh
scripts/docs/check-foundation-coverage.sh
scripts/docs/secret-scan.sh
scripts/graphify/build.sh && scripts/graphify/query-smoke.sh
```

Also run the CICD-CTRL-1 local gates: `scripts/ci/fast-local.sh` during development and
`scripts/ci/full-local.sh` before marking a PR ready-for-review.

## CI runtime control (CICD-CTRL-1, rule 28)

- Open feature PRs as **drafts** — `pr-ci.yml` runs **fast CI only** on drafts. Mark **ready** only after review;
  one full release CI then runs on the final head. A CI PASS is valid only for the exact tested SHA; a new commit
  requires a new full CI. Do not reuse an old CI result.
- `main-post-merge.yml` runs lightweight integrity verification only; tags run no full CI; post-tag evidence is a
  GitHub Release artifact. Never add a feature-branch `push` trigger, a top-level `paths:` filter on a mandatory
  workflow, `[skip ci]` on mandatory checks, or an unpinned action. Never weaken, skip, or duplicate CI gates.
  See [docs/ci/DRAFT_TO_RELEASE_WORKFLOW.md](docs/ci/DRAFT_TO_RELEASE_WORKFLOW.md).

## Pull requests

Every PR must state: objective; scope and out-of-scope; canonical source versions; files/rules added;
MCP/skills/subagents used; security controls; Graphify status; local validation results; Master Source
impact; rollback method; and a GO/NO-GO checklist. Critical/high review findings must be resolved
before merge; accepted lower-risk findings are documented with owner, reason, and follow-up.

## Commits

Use logical, reviewable commits scoped to one concern. Do not mix unrelated changes.
