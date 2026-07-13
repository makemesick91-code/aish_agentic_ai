# ADR 0019 — AI Provider Abstraction

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** AI Systems Architect
- **Rule:** `.claude/rules/04`, `05`, `18`, `20` (AFR-041..046) · **Canonical:** Master Source v2.3.0 §15.2, §23–§34, §44; PRD v1.2.0 §12, §13, §16

## Context
AI must be safe, auditable, and human-supervised, treat customer content as untrusted, and never leak medical
data — while remaining optional (manual fallback).

## Decision
Call AI **from Laravel via a provider abstraction on a durable queue**, only with structured output, timeout,
bounded retry, idempotency, prompt/model versioning, redaction, guardrails, human approval upstream of public
action, token+cost logging, trace correlation, failure states, manual fallback, and a kill switch. Supervisor +
specialist agents; customer content never determines tool calls; tools allowlisted and arguments validated. See
[AI Service Boundary](../../architecture/AI_SERVICE_BOUNDARY.md) and [AI Runtime Control Plane](../../ai/AI_RUNTIME_CONTROL_PLANE.md).

## Alternatives
- **Direct provider SDK calls inline** — rejected: no durability, guardrails, or fallback.
- **Single monolithic agent** — rejected: violates supervisor/specialist governance (Master Source §23–§33).

## Consequences
Safe, swappable, observable AI; requires guardrail + trace + cost infrastructure (AI module).

## Impacts
- **Security:** prompt-injection defense; tool allowlisting; kill switch.
- **Privacy:** redaction strips PII; `MED` data never sent (`.claude/rules/18`).
- **Tenant isolation:** retrieval tenant-filtered (ADR 0023); runs tenant-scoped.
- **Database:** agent_runs/steps/tool_calls/costs/versions tables.
- **Operational:** trace + cost + token logging; manual fallback keeps workflow alive.
- **Cost:** logged and capped; kill switch on overspend.

## Verification / fitness function
FF-AI-01..05, FF-SEC-05. Implementation: schema, redaction, guardrail, cost, fallback, prompt-injection tests.

## Related
Requirement: Master Source §23–§34, §44; PRD §12, §13. Application rule: AFR-041..046. ADRs: 0020, 0023, 0028.

## Evidence
`docs/architecture/AI_SERVICE_BOUNDARY.md`, `docs/ai/AI_RUNTIME_CONTROL_PLANE.md`, `AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md`.

## Non-claims
No AI provider, agent, guardrail, or retrieval runs in Step 3.

## Rollback / supersession
AI governance is permanent; auto-publish/autonomy relaxations require Master Source §16.4 preconditions + update.
