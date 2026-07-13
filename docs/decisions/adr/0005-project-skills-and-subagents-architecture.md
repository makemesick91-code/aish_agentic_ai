# ADR 0005 — Project Skills and Subagents Architecture

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/15` · **Canonical:** Master Source §66.9

## Context
Repeatable governance workflows and independent reviews should be codified, not improvised, and must be safe.

## Decision
Provide four project **skills** (`master-source-update`, `documentation-gate`, `release-evidence`,
`graphify-refresh`) with **no unsafe automatic mutation**, and six least-privilege **review subagents**
(product, architecture, security-privacy, ai-governance, qa-traceability, release-governance). Subagents are
report-only (Read/Grep/Glob; release-governance may run read-only git/gh) and never merge, publish, tag, or
run destructive operations.

## Alternatives considered
- Ad-hoc prompts — rejected: not repeatable or reviewable.
- Autonomous mutating agents — rejected: violates human-in-the-loop and release governance (`.claude/rules/05`, `13`).

## Consequences
Consistent, auditable workflows; independent review before merge/tag. Skills/subagents must stay in sync with rules.

## Security impact
Least privilege; no subagent can perform a destructive or publishing action; guardrails preserved.

## Migration impact
None (greenfield).

## Supersession
Superseded by a Master Source update or later ADR changing the skills/subagents set.
