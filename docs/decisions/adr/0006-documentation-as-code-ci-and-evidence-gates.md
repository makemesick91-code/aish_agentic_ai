# ADR 0006 — Documentation-as-Code CI and Evidence Gates

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/09`, `13` · **Canonical:** Master Source §66.10

## Context
Documentation foundations must be verifiable, not assertion-based, and must not regress.

## Decision
Treat documentation as code: deterministic gates (source checksums, version consistency, markdown, internal
links, rule frontmatter, foundation coverage, requirement traceability, contradiction detection, secret
scan, knowledge-graph build/query smoke, hook tests) run locally via `scripts/docs/validate.sh` and in CI
(`.github/workflows/documentation-foundation.yml`) on PRs and `main`. Evidence is captured under `docs/evidence/`.

## Alternatives considered
- Manual review only — rejected: not reproducible, easy to regress.
- Heavy external doc toolchain — rejected: prefer minimal, pinned, portable POSIX shell + optional linters.

## Consequences
Every PR proves the gates; regressions fail CI. Adds script/CI maintenance. Gates may be added but not weakened.

## Security impact
Secret scan + push protection guard against credential leakage; the graph excludes secrets/PII.

## Migration impact
Application test gates layer on later (Master Source §54) without weakening documentation gates.

## Supersession
Superseded by a Master Source update or later ADR; removing/weakening a gate needs documented owner approval.
