# Release Gates — Aish Agentic AI

Canonical: Master Source §54, §66.10. Rule: `.claude/rules/09`, `13`. PRD §24.

## Two distinct gate sets

### A. Documentation-foundation gates (THIS release — must all pass with evidence)
1. Canonical sources preserved + SHA-256 checksummed.
2. Canonical version consistency (Master Source ≥ v2.1.1; PRD reference consistent).
3. Markdown structure/format valid.
4. Internal links resolve.
5. Rule frontmatter/syntax valid for all `.claude/rules/*.md`.
6. `CLAUDE.md` + rules discoverable in the installed Claude version.
7. Foundation coverage 100% for permanent decisions and release gates.
8. Requirements traceability: no orphan critical requirement.
9. No contradictory active decisions.
10. Secret scan passes; no PII/credential source indexed by the knowledge graph.
11. Knowledge-graph build + canonical query smoke pass (or explicit `BLOCKED-OPTIONAL` fallback).
12. Skills/subagents/settings/hooks/MCP validate; hook positive+negative tests pass.
13. CI green on the PR.
14. Git working tree clean after commits.
15. PR merged; annotated GO tag exact-matches the merged commit (local + remote).

Mapped to CI + `scripts/docs/validate.sh`; results in `../evidence/`. GO/NO-GO: `../release/DOCUMENTATION_FOUNDATION_GO_NO_GO.md`.

### B. Product release gates (FUTURE — Master Source §54)
Functional · security · data · AI · integration · operational gates — each with the specific checks in
Master Source §54 and PRD §24 (MVP pilot gate + production gate). These apply when application
implementation begins; **none is claimed complete by this foundation.**

## Invariant
Gates MUST NOT be skipped, weakened, faked, or bypassed (`.claude/rules/09`, `13`). Adding gates is allowed;
removing/weakening one requires documented owner approval.
