# ADR 0002 — Claude Persistent Context via CLAUDE.md + Modular Rules

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/14`, `15` · **Canonical:** Master Source §66.4, §66.5

## Context
Claude needs reliable cross-session memory without loading thousands of lines into startup context.

## Decision
Use a concise root `CLAUDE.md` as an instruction index + authority map, and place all permanent foundations
in modular `.claude/rules/` (16 files). Claude reads the relevant rule on demand. Detailed knowledge stays
in `docs/`; rules distill enforceable behavior and link back via the coverage matrix.

## Alternatives considered
- One large `CLAUDE.md` — rejected: token-heavy, violates Limit Saver, hard to maintain.
- Rules only in external docs (no `.claude/rules/`) — rejected: not discoverable as enforceable behavior.

## Consequences
Low startup cost; high signal. Requires keeping the rule index and coverage matrix in sync (validated by CI).

## Security impact
Security/privacy/isolation rules are first-class and enforceable; hooks back the highest-risk denials.

## Migration impact
None (greenfield).

## Supersession
Superseded by a higher-version Master Source update or a later ADR that this one references.
