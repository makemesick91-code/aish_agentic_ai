# Version Matrix — Aish Agentic AI

Rule: `.claude/rules/12`. Tracks canonical document versions, this repository's foundation tag, and toolchain.

## Canonical documents
| Document | Active version | Working copy | Preserved original | Notes |
|----------|----------------|--------------|--------------------|-------|
| Master Source | **2.10.0** | `../canonical/MASTER_SOURCE.md` | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.10.0.md` | Step 8 Feedback Operations Foundation (§74); idempotent feedback projection + lifecycle, scope-validated assignment, tenant-isolated tags, append-only notes/timeline, private attachments, permission-aware search, bounded bulk, queued secure export |
| Master Source (historical) | 2.9.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.9.0.md` | Step 7 Survey & CSAT Foundation (§73); immutable-versioned surveys, secure public invitation/token/QR, deterministic CSAT/NPS/CES; superseded by 2.10.0 |
| Master Source (historical) | 2.8.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.8.0.md` | SPRINT-SF-05 Notification, Subscription, and Platform Admin Skeletons (§72); tenant-safe notifications, fail-closed entitlement resolver, separate least-privilege platform-admin plane; superseded by 2.9.0 |
| Master Source (historical) | 2.7.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.7.0.md` | Step 6 SaaS Core Foundation (§71); auth/identity, tenant/branch + memberships, fail-closed context, RBAC/audit, isolation; superseded by 2.8.0 |
| Master Source (historical) | 2.6.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.6.0.md` | Step 5 Runtime & Repository Bootstrap (§70); superseded by 2.7.0 |
| Master Source (historical) | 2.5.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.5.0.md` | CICD-CTRL-1 Safe CI Runtime Control governance (§69); superseded by 2.6.0 |
| PRD | **1.3.0** | `../canonical/PRD.md` | `../canonical/source/PRD_AISH_AGENTIC_AI_v1.3.0.md` | Step 4 baseline; unchanged by CICD-CTRL-1 (CI = non-functional release assurance, NFR-CI-001..006 in Master Source §6) |
| Persona & Pilot Use Cases | **1.0.0** | `../product/PERSONA_AND_PILOT_USE_CASES.md` | `../canonical/source/PERSONA_AND_PILOT_USE_CASES_v1.0.0.md` | Canonical Step 2 persona/pilot source (unchanged) |
| Master Source (historical) | 2.4.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.4.0.md` | Historical; superseded by 2.5.0 |
| Master Source (historical) | 2.3.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.3.0.md` | Historical; superseded by 2.4.0 |
| PRD (historical) | 1.2.0 | — | `../canonical/source/PRD_AISH_AGENTIC_AI_v1.2.0.md` | Historical; superseded by 1.3.0 |
| Master Source (historical) | 2.2.0 | — | `../canonical/source/MASTER_SOURCE_AISH_AGENTIC_AI_v2.2.0.md` | Historical; superseded by 2.3.0 → 2.4.0 |
| PRD (historical) | 1.1.0 | — | `../canonical/source/PRD_AISH_AGENTIC_AI_v1.1.0.md` | Historical; superseded by 1.2.0 → 1.3.0 |
| Master Source (historical) | 2.1.1 | — | `../canonical/source/…v2.1.1.md` | Historical; superseded by 2.2.0 → 2.3.0 |
| PRD (historical) | 1.0.1 | — | `../canonical/source/…v1.0.1.md` | Historical; superseded by 1.1.0 |
| Master Source (historical) | 2.0.0 | — | `../canonical/source/…v2.0.0.md.txt` | Historical; superseded by 2.0.1 → 2.1.0 → 2.1.1 → 2.2.0 → 2.3.0 |

## Master Source version lineage (from changelog §6)
`2.0.0` (unified baseline) → `2.0.1` (PRD baseline) → `2.1.0` (Claude docs & rules foundation) →
`2.1.1` (canonical repository established) → `2.2.0` (Persona & Pilot Use Case baseline / Step 2) →
`2.3.0` (Application Architecture & ADR foundation / Step 3) →
`2.4.0` (Domain/Branding/Environment/SaaS-Foundation planning / Step 4) →
`2.5.0` (CICD-CTRL-1 Safe CI Runtime Control governance) →
`2.6.0` (Runtime & Repository Bootstrap / Step 5) →
`2.7.0` (SaaS Core Foundation / Step 6) →
`2.8.0` (SPRINT-SF-05 Notification/Subscription/Platform-Admin skeletons) →
`2.9.0` (Survey & CSAT Foundation / Step 7) →
`2.10.0` (Feedback Operations Foundation / Step 8).
**Active: 2.10.0.** PRD lineage: `1.0.0` → `1.0.1` (normalization) → `1.1.0` (Step 2) → `1.2.0` (Step 3) →
`1.3.0` (Step 4; unchanged by CICD-CTRL-1, Step 5, Step 6, Step 7, and Step 8).
**Active PRD: 1.3.0.**

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

## Step 3 release (Application Architecture & ADR Foundation)
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `docs/step-3-application-architecture-adr-foundation` |
| Scope | Master Source v2.3.0 / PRD v1.2.0; ADRs 0009–0032; AFR-001..072; Claude rule 20; AGENTS chain; Codex config/rules/hooks/skills; MCP governance; Step 3 gates; CI; evidence |
| Claim | Documentation/architecture baseline only — application implementation, deployment, live integration, pilot readiness, pilot runtime, and production readiness remain NOT STARTED |

## Step 4 release (Domain, Branding, Environment & SaaS Foundation Planning)
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-step-4-domain-branding-environment-saas-foundation-planning-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `docs/step-4-domain-branding-environment-saas-foundation-planning` |
| Scope | Master Source v2.4.0 / PRD v1.3.0; ADRs 0033–0041; AFR-073..104; Claude rules 21–27; domain/brand/environment/dependency/SaaS-Foundation planning docs; Step 4 gates; CI; evidence |
| Claim | Planning/documentation baseline only — no domain owned, no package installed, nothing deployed; application implementation, deployment, pilot readiness, pilot runtime, and production readiness remain NOT STARTED |

## CICD-CTRL-1 release (Safe CI Runtime Control)
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-cicd-ctrl-1-safe-ci-runtime-control-v1.0.0-go` (annotated, immutable) |
| Base branch | `main` |
| Feature branch | `chore/cicd-ctrl-1-safe-ci-runtime-control` |
| Scope | Master Source v2.5.0 (PRD unchanged v1.3.0); ADRs 0042–0046; AFR-105..126; Claude rule 28; `pr-ci.yml`/`main-post-merge.yml`/`full-ci-manual.yml`; classifier + local gates + validators; ruleset enforcement; CI docs; evidence |
| Claim | CI/release-process governance only — application implementation, deployment, pilot readiness, pilot runtime, and production readiness remain NOT STARTED. A CI PASS is valid only for the exact tested SHA |

## Step 6 release (SaaS Core Foundation)
| Item | Value |
|------|-------|
| Target GO tag | `aish-agentic-ai-step-6-saas-core-foundation-v1.0.0-go` (annotated, immutable) — NOT yet created |
| Base branch | `main` |
| Consolidates | Canonical SPRINT-SF-01..SF-04 (EPIC-SF-04..09) into one release under a single GO tag |
| Scope | Master Source v2.7.0 (PRD unchanged v1.3.0); ADRs 0051–0053; Claude rule 30; secure auth + identity; tenant/branch lifecycle + memberships; immutable fail-closed tenant context; tenant-scoped RBAC + policies; append-only audit; tenant isolation (DB/cache/queue/storage/log) |
| Status | CODE COMPLETE + TESTED locally; IN PROGRESS toward GO — NOT merged, NOT tagged, NOT CI-green-on-CI, NOT clean-checkout-verified |
| Claim | SaaS-core-foundation readiness only — business modules, deployment, pilot readiness, pilot runtime, and production readiness remain NOT STARTED; no domain owned, nothing deployed |

## Toolchain (Step 3 time)
| Component | Version / status |
|-----------|------------------|
| gh CLI | 2.46.0 (auth: `makemesick91-code`) |
| git | 2.53.0 |
| Codex CLI | NOT INSTALLED — `.codex/` authored + statically validated; `execpolicy`/hooks not runtime-run (OD-07) |
| Limit Saver 1 | External NOT INSTALLED — project fallback active (OD-06) |
| Graphify (branded) | Host binary `0.8.35` present but NOT governance-verified → `BLOCKED-OPTIONAL`, deterministic index used (OD-05) |
| MCP (committed) | `.mcp.json` empty server set + governance |

## Why Master Source was not re-bumped
v2.1.1 already canonically records this Claude Foundation decision (§66) and the canonical repository, and
satisfies the GO criterion "active Master Source ≥ v2.1.1". Re-bumping to record the not-yet-created tag
would risk asserting an artifact before it exists; execution evidence is recorded in `../release/` and
`../evidence/` instead (D-009).
