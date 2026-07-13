# Decision Log — Aish Agentic AI

Rule: `.claude/rules/12`, `00`. Chronological record of material decisions. Times in Asia/Makassar.
Superseded decisions are marked, never deleted.

| # | Date | Decision | Type | Authority | ADR / Ref | Status |
|---|------|----------|------|-----------|-----------|--------|
| D-001 | 2026-07-13 | Canonical repository is `makemesick91-code/aish_agentic_ai`; no alternative repo | Governance | Owner + Master Source §66.2 | Master Source v2.1.1 | Active |
| D-002 | 2026-07-13 | Documentation & Claude Rules Foundation delivered via branch → PR → CI → merge → annotated GO tag | Release | Master Source §66.10–§66.11 | ADR 0006, 0007 | Active |
| D-003 | 2026-07-13 | Canonical documentation hierarchy + authority order adopted | Governance | Master Source §66.3 | ADR 0001 | Active |
| D-004 | 2026-07-13 | Persistent Claude context via concise `CLAUDE.md` + modular `.claude/rules/` | Tooling | Master Source §66.4–§66.5 | ADR 0002 | Active |
| D-005 | 2026-07-13 | Graphify treated as derived; branded product `BLOCKED-OPTIONAL`; deterministic doc index used | Tooling | Master Source §66.7 | ADR 0003 | Active |
| D-006 | 2026-07-13 | Minimal, secret-free MCP allowlist; `.mcp.json` empty server set + governance | Security/Tooling | Master Source §66.8 | ADR 0004 | Active |
| D-007 | 2026-07-13 | Four project skills + six least-privilege review subagents | Tooling | Master Source §66.9 | ADR 0005 | Active |
| D-008 | 2026-07-13 | Limit Saver 1 not installed → documented fallback token-saving protocol | Tooling | Master Source §66.6 | `docs/tooling/LIMIT_SAVER.md` | Active |
| D-009 | 2026-07-13 | Master Source remains v2.1.1 (already records this foundation in §66); PRD normalized to v1.0.1 (metadata only) | Documentation | Master Source §5, GO Tag Prompt v1.0.1 | `VERSION_MATRIX.md` | Active |
| D-010 | 2026-07-13 | Controlled empty-repository bootstrap of `main` (README+.gitignore), not a GO | Release | Master Source §66.2 | commit `516d1bd` | Active |

## Notes
- No decision here changes MVP scope, architecture, security controls, or target market.
- Application implementation remains NOT STARTED.
