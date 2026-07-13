---
name: documentation-gate
description: Run the documentation-as-code gates (markdown, links, version consistency, coverage, traceability, secret scan, graph drift/smoke, ADR, Codex, AGENTS) and report a single pass/fail with evidence. Read/validate only.
---

# Skill: documentation-gate

**Trigger:** Before commit/PR/GO; after any doc/rule/ADR change.
**Non-trigger:** N/A (always safe to run).
**Inputs:** Repository docs, scripts, evidence dirs.

## Workflow
```bash
scripts/docs/validate.sh          # aggregates all gates incl. Step 3
scripts/codex/check-agents.sh
scripts/codex/check-codex.sh
```
Captures each gate's output + exit code to `docs/evidence/validation/` and `docs/evidence/step-3/validation/`.

## Safety boundaries
Read/validate only. No repository mutation beyond writing evidence logs. Never weakens a gate.

## Required output
Single PASS/FAIL with per-gate results and evidence paths.

## Evidence
`docs/evidence/validation/*.log`, `docs/evidence/step-3/validation/*.log`.

## Failure behavior
On any gate failure, report the failing gate + log path; block PR/GO.
