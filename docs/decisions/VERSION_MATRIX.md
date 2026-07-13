# Version Matrix — Aish Agentic AI

Rule: `.claude/rules/12`. Tracks canonical document versions, this repository's foundation tag, and toolchain.

## Canonical documents
| Document | Active version | Working copy | Preserved original | Notes |
|----------|----------------|--------------|--------------------|-------|
| Master Source | **2.2.0** | `../canonical/MASTER_SOURCE.md` | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.2.0.md` | Persona & Pilot Use Case baseline (Step 2); records docs-foundation GO tag as evidence |
| PRD | **1.1.0** | `../canonical/PRD.md` | `../canonical/source/PRD_AISH_AGENTIC_AI_v1.1.0.md` | Step 2 baseline; canonical source = Master Source v2.2.0 |
| Persona & Pilot Use Cases | **1.0.0** | `../product/PERSONA_AND_PILOT_USE_CASES.md` | `../canonical/source/PERSONA_AND_PILOT_USE_CASES_v1.0.0.md` | Canonical Step 2 persona/pilot source |
| Master Source (historical) | 2.1.1 | — | `../canonical/source/…v2.1.1.md` | Historical; superseded by 2.2.0 |
| PRD (historical) | 1.0.1 | — | `../canonical/source/…v1.0.1.md` | Historical; superseded by 1.1.0 |
| Master Source (historical) | 2.0.0 | — | `../canonical/source/…v2.0.0.md.txt` | Historical; superseded by 2.0.1 → 2.1.0 → 2.1.1 → 2.2.0 |

## Master Source version lineage (from changelog §6)
`2.0.0` (unified baseline) → `2.0.1` (PRD baseline) → `2.1.0` (Claude docs & rules foundation) →
`2.1.1` (canonical repository established) → `2.2.0` (Persona & Pilot Use Case baseline / Step 2).
**Active: 2.2.0.** PRD lineage: `1.0.0` → `1.0.1` (normalization) → `1.1.0` (Step 2). **Active PRD: 1.1.0.**

## Documentation-foundation release (Step 1)
| Item | Value |
|------|-------|
| GO tag | `aish-agentic-ai-docs-foundation-v1.0.0-go` (annotated, immutable; peeled commit `ba1c80f`) |
| Base branch | `main` |
| Feature branch | `chore/aish-agentic-ai-documentation-foundation` |
| Bootstrap commit | `516d1bd` (not a GO) |
| Scope | Canonical docs, Claude rules/skills/subagents, MCP governance, Graphify (deterministic), CI, evidence |

## Step 2 release (Persona & Pilot Use Cases)
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-step-2-persona-pilot-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `docs/step-2-persona-pilot-use-cases` |
| Scope | Persona/pilot canonical import (v2.2.0/v1.1.0/v1.0.0), pilot derived docs, rules 16–19, Step 2 gates, CI, evidence |
| Claim | Documentation baseline only — application implementation, deployment, pilot readiness, and pilot runtime remain NOT STARTED |

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
