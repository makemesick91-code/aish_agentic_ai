# Step 3 Architecture Rule Coverage — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0; PRD v1.2.0 · **Rules:** `.claude/rules/09`, `12`, `15`, `20`.

Confirms each permanent-decision area is covered by a Claude rule, an AGENTS instruction area, an AFR, and an
ADR — with no gap.

| Area | Claude rule | AGENTS area | AFR | ADR | Status |
|------|-------------|-------------|-----|-----|--------|
| Document authority / identity | 00,01 | AGENTS.md, docs/AGENTS.md | AFR-001,002 | — | COVERED |
| MVP scope / roadmap | 02 | docs/AGENTS.md | AFR-065 | — | COVERED |
| Multi-tenant / branch isolation | 03 | docs/architecture/AGENTS.md | AFR-006..020 | 0011,0012,0015 | COVERED |
| Security / privacy / secrets | 04 | docs/security/AGENTS.md | AFR-023..025,048,058,064 | 0022,0025,0031 | COVERED |
| AI governance / approval | 05 | docs/ai/AGENTS.md | AFR-027,029,030,041..045,049,050 | 0019,0028 | COVERED |
| Google review policy | 06 | docs/integrations/AGENTS.md | AFR-028,051,052 | 0021 | COVERED |
| Data governance / audit | 07 | docs/architecture/AGENTS.md | AFR-009,026,059,061 | 0014,0024,0029 | COVERED |
| Architecture / events | 08 | docs/architecture/AGENTS.md | AFR-003..005,031..040 | 0009,0010,0016,0017 | COVERED |
| Testing / quality gates | 09 | scripts/AGENTS.md | AFR-054,062,063,066 | 0026,0030 | COVERED |
| UI/UX / truthful states | 10 | docs/architecture/AGENTS.md | AFR-036,047,068 | 0016,0018 | COVERED |
| Observability / backup / ops | 11 | docs/operations/AGENTS.md | AFR-055,056,057,060,071 | 0024,0027,0032 | COVERED |
| Documentation / versioning | 12 | docs/AGENTS.md | AFR-065 | — | COVERED |
| Git / CI / release / GO tag | 13 | scripts/AGENTS.md | AFR-054,067,072 | 0026 | COVERED |
| Limit saver / context / handoff | 14 | .agents/skills | AFR-066 | — | COVERED |
| MCP / skills / tool safety | 15 | docs/tooling/MCP_GOVERNANCE.md, .codex | AFR-064,069,070 | 0031 | COVERED |
| Pilot persona / scope | 16 | docs/product | AFR-002,022 | 0013 | COVERED |
| Pilot invitation / fallback | 17 | docs/integrations/AGENTS.md | AFR-020,045 | 0019 | COVERED |
| Pilot privacy / approval / review safety | 18 | docs/security/AGENTS.md, docs/ai/AGENTS.md | AFR-027,028,046,048,052 | 0021,0023,0028 | COVERED |
| Pilot metrics / evidence / GO-NO-GO | 19 | docs/quality/AGENTS.md | AFR-066,068 | — | COVERED |
| Application architecture & foundation rules | 20 | AGENTS.md, docs/architecture/AGENTS.md | AFR-003..072 | 0009..0032 | COVERED |

## Assertion
All 21 rule areas (00–20) are COVERED by a rule + AGENTS area + AFR (+ADR where architectural).
**No critical gap.** No `NOT COVERED` / `GAP` / `MISSING` / `TBD` status.
