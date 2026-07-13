# Limit Saver — Aish Agentic AI

Canonical: Master Source §66.6. Rule: `.claude/rules/14`. GO Tag Prompt v1.0.1 (Execution Mode: Limit Saver 1).

## Skill status (this environment)
Detection at foundation time found **no installed skill** named `Limit Saver 1`, `limit-saver-1`,
`usage-limit-reducer`, or a clear equivalent (see `../evidence/inventory/skills-inventory.txt`). Per policy,
an unknown package was **not** silently installed. The **fallback token-saving protocol** below is applied,
and the missing optional dependency is recorded. If such a skill is later installed, invoke its **exact**
installed name — never invent a slash command.

## Fallback token-saving protocol (applied)
- Keep root `CLAUDE.md` concise; detailed rules live in modular `.claude/rules/`, skills, and topic docs.
- One clear objective per phase; batch related reads and tool calls.
- Use focused subagents for large audits; require concise findings + file paths, not full file dumps.
- Prefer targeted search / knowledge-index queries / line-range reads over re-reading whole files.
- Maintain `../status/CURRENT_STATE.md`, `HANDOFF.md`, and `SESSION_CHECKPOINTS.md` after each phase.
- Do not duplicate unchanged canonical content across large files; link and use coverage matrices.

## Hard invariant (Master Source §66.6)
Token saving MUST NOT reduce test coverage, skip security review, drop evidence, remove audit, weaken a
release gate, or cause false completion claims. Efficiency never overrides security or truthfulness.

**Status:** Limit Saver 1 = NOT INSTALLED (optional). Fallback protocol = ACTIVE and documented.

## Step 4 status (2026-07-13)
External `Limit Saver 1` / `usage-limit-reducer` remains **NOT INSTALLED**; the project fallback protocol stays
active (authority-map-first reads, graph/index queries before opening many files, per-phase checkpoints in
`docs/status/`, parallel report-only review). Token saving did not weaken any gate, test, or evidence requirement.
Status: **EXTERNAL NOT INSTALLED — PROJECT FALLBACK ACTIVE**.
