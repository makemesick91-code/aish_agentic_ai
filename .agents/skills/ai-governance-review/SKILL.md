---
name: ai-governance-review
description: Review AI guardrails, human-approval coverage, structured output, redaction, prompt/model versioning, cost/trace logging, manual fallback, kill switch, and extraction criteria. Read-only.
---

# Skill: ai-governance-review

**Trigger:** Changes to AI boundary, guardrails, approval, or agent architecture.
**Non-trigger:** Non-AI edits.
**Inputs:** `docs/ai/*`, AI ADRs (0019,0020,0023,0028).

## Workflow
1. Confirm supervisor+specialist architecture; single agent MUST NOT do all work.
2. Confirm structured output, redaction, guardrails, human approval upstream of public action.
3. Confirm prompt/model version, token+cost logging, trace correlation, manual fallback, kill switch.
4. Confirm customer content never steers tool calls; extraction criteria (ADR 0020) recorded, not triggered.

## Safety boundaries
Read-only. MUST NOT enable auto-publish/autonomy or weaken approval.

## Required output
Findings with file + AFR reference; or "AI governance complete".

## Evidence
`docs/ai/AI_RUNTIME_CONTROL_PLANE.md`, `AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md`, `AI_OBSERVABILITY_AND_COST_ARCHITECTURE.md`.

## Failure behavior
Missing approval/guardrail/fallback is a BLOCKER → NO-GO until fixed.
