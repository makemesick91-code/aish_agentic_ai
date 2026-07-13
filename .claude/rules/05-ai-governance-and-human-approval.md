---
id: "05"
title: AI Governance and Human Approval
domain: ai-governance
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §15.2, §23-§33, §44"
  - "PRD §12, §13, §16"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 05 — AI Governance and Human Approval

## Purpose
Keep Agentic AI safe, auditable, and human-supervised for risky actions.

## Scope
All AI orchestration, agents, guardrails, tool calls, and approval workflows.

## Rules
- The system **MUST** use a supervisor + specialist agent architecture (Feedback Intake, Sentiment/Topic,
  Severity/Risk, Recovery, Google Review Response, Policy/Privacy Guardrail, Insight, Notification).
  A single agent **MUST NOT** perform all work.
- Human approval **MUST** be required for the actions in Master Source §33 and PRD §13, including: 1-2 star
  reviews, legal/medical/safety/fraud/discrimination risk, PII, refunds/discounts/compensation, data
  deletion, legal statements, low AI confidence, policy conflict, admissions of fault, and repeated customer contact.
- The supervisor **MUST NOT** bypass approval for sensitive actions. A kill switch and controlled retry
  (no duplicate external side-effects) **MUST** exist.
- AI **MUST** produce structured output, run guardrails, and **MUST NOT** let customer content determine
  tool calls (see `.claude/rules/04`, `06`). Tool arguments **MUST** be validated; tools allowlisted.
- AI tracing, prompt versioning, model versioning, tool-call logging, and cost logging **MUST** be recorded.
- The workflow **MUST** remain usable when AI is unavailable; basic functions **MUST NOT** depend on AI.
- Only low-risk steps (sentiment/topic classification, summary, severity suggestion, internal assignment,
  SLA calc, reminders, draft creation, duplicate/spam detection, internal insight) **MAY** be automated early.

## Required checks
- Human Approval Matrix (`docs/ai/HUMAN_APPROVAL_MATRIX.md`) covers every §33/PRD-§13 trigger.
- AI evaluation baseline (`docs/ai/AI_EVALUATION_BASELINE.md`) defines datasets and thresholds (`.claude/rules/09`).

## Evidence
- `docs/ai/AGENTIC_ARCHITECTURE.md`, `HUMAN_APPROVAL_MATRIX.md`, `AI_EVALUATION_BASELINE.md`, `AI_COST_AND_TRACING.md`.

## Related canonical sections
- Master Source §15.2, §23-§33 (agents), §44 (prompt injection); PRD §12, §13, §16.

## Supersession
Approval requirements are permanent; relaxations (e.g. auto-publish) require the §16.4 preconditions and a Master Source update.
