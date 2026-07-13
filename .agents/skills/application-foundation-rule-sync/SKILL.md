---
name: application-foundation-rule-sync
description: Keep the AFR catalog, ADRs, AGENTS.md chain, Claude rules, and Codex rules in sync so every permanent decision maps end-to-end with no orphan and no second source of truth.
---

# Skill: application-foundation-rule-sync

**Trigger:** Adding/changing an AFR, ADR, rule, or AGENTS instruction.
**Non-trigger:** Prose-only doc tweaks with no rule impact.
**Inputs:** AFR catalog, ADRs, `.claude/rules/`, `AGENTS.md` chain, `.codex/rules/`.

## Workflow
1. For each changed decision, verify the mapping: Canonical → ADR → AFR → AGENTS → Claude rule → (Codex rule) →
   fitness function → evidence.
2. Run `scripts/codex/check-agents.sh` (AGENTS chain + drift) and `scripts/docs/check-step3-coverage.sh`.
3. Ensure no two sources of truth: AGENTS/Claude/Codex point to the same canonical decision (AFR-069).

## Safety boundaries
Read/validate + edit governance docs only. MUST NOT merge, tag, deploy, or read secrets.

## Required output
Confirmation that the mapping is complete with no orphan, or a list of gaps.

## Evidence
`docs/architecture/APPLICATION_FOUNDATION_RULES.md`, `docs/quality/STEP_3_ARCHITECTURE_RULE_COVERAGE.md`.

## Failure behavior
On a missing link, add it or report it; never leave a permanent decision orphan.
