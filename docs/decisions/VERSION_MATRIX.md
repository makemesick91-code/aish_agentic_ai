# Version Matrix — Aish Agentic AI

Rule: `.claude/rules/12`. Tracks canonical document versions, this repository's foundation tag, and toolchain.

## Canonical documents
| Document | Active version | Working copy | Preserved original | Notes |
|----------|----------------|--------------|--------------------|-------|
| Master Source | **2.1.1** | `../canonical/MASTER_SOURCE.md` | `../canonical/source/…v2.1.1.md` | Records Claude Foundation (§66) + canonical repo; ≥ v2.1.1 GO criterion satisfied |
| PRD | **1.0.1** | `../canonical/PRD.md` | `../canonical/source/…v1.0.1.md` | Metadata/traceability normalization from v1.0.0 baseline; no scope change |
| Master Source (historical) | 2.0.0 | — | `../canonical/source/…v2.0.0.md.txt` | Historical; superseded by 2.0.1 → 2.1.0 → 2.1.1 |

## Master Source version lineage (from changelog §6)
`2.0.0` (unified baseline) → `2.0.1` (PRD baseline) → `2.1.0` (Claude docs & rules foundation) →
`2.1.1` (canonical repository established). **Active: 2.1.1.**

## Documentation-foundation release
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-docs-foundation-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `chore/aish-agentic-ai-documentation-foundation` |
| Bootstrap commit | `516d1bd` (not a GO) |
| Scope | Canonical docs, Claude rules/skills/subagents, MCP governance, Graphify (deterministic), CI, evidence |

## Toolchain (foundation time)
| Component | Version / status |
|-----------|------------------|
| Claude Code | 2.1.179 |
| gh CLI | 2.46.0 (auth: `makemesick91-code`) |
| Limit Saver 1 | NOT INSTALLED (fallback active) |
| Graphify (branded) | NOT INSTALLED — `BLOCKED-OPTIONAL` (deterministic index used) |
| MCP (committed) | `.mcp.json` empty server set + governance |

## Why Master Source was not re-bumped
v2.1.1 already canonically records this Claude Foundation decision (§66) and the canonical repository, and
satisfies the GO criterion "active Master Source ≥ v2.1.1". Re-bumping to record the not-yet-created tag
would risk asserting an artifact before it exists; execution evidence is recorded in `../release/` and
`../evidence/` instead (D-009).
