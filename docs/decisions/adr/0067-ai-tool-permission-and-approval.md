# ADR 0067 — AI Tool Permission, Human Approval, Cost Ceiling, Tracing, and Kill-Switch Architecture

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 architecture LOCK; AI runtime NOT STARTED (Wave 1 basic AI → Wave 3 Agent Studio)
- **Owner:** Principal Architect / AI Orchestration & Tool Actions
- **Rule:** `.claude/rules/34`, `.claude/rules/05`, `.claude/rules/18`, `.claude/rules/04` · **Canonical:** Master Source §75, §33, §34, §44; PRD v1.3.0 §13; rules 34, 04, 05, 06, 07, 18

## Context
Future AI (basic classification, copilot, and eventually Agent Studio tool actions) must be bounded: customer content
must never steer tool calls, high-risk actions must require human approval, MED data must never reach a provider or
public output, costs must be capped, every run must be traceable, and a kill switch must exist. Rule 05 and the planned
AI control plane (ADRs 0019/0023/0028) establish these; this ADR locks the bounded-action model ahead of any AI build.

## Decision
- Adopt the bounded-action contract in `docs/architecture/experience-os/AI_TOOL_PERMISSION_AND_APPROVAL_ARCHITECTURE.md`:
  provider abstraction + structured output; immutable prompt/policy versions; agent identity+version; an explicit
  allowlisted **tool registry**; per-tool permission + tenant/branch scope + data minimization; a **forbidden-action**
  set (public reply, refund/compensation, data deletion, legal statements, admission of fault — never auto); confidence
  + human-approval thresholds with **mandatory approval** for §33/PRD-§13 high-risk triggers; per-tenant/agent/run
  **cost ceilings** + token/outcome metering; bounded timeout/retry with **duplicate-action prevention** (idempotency
  keys); correlation + trace projected to the Experience Event Ledger; input/output **guardrails** +
  prompt-injection defense; PII/MED redaction; a global/per-tenant/per-agent **kill switch**; rollback/compensating
  actions; human-correction capture; and an adversarial **evaluation dataset** + quality gates.
- **Manual workflows remain fully operable when AI is disabled or unavailable.**
- Autonomy follows manual → semi-automated → approved automation → limited autonomy; Agent Studio is Wave 3.

## Alternatives
- **Let AI publish/refund/delete autonomously** — rejected: violates human-approval rules 05/06/18.
- **Feed raw customer content directly to tools** — rejected: prompt-injection/tool-abuse; content cannot steer tools.
- **Unbounded cost/retry** — rejected: cost abuse + duplicate external actions; ceilings + idempotency required.
- **No kill switch** — rejected: no safe stop; kill switch mandatory.

## Consequences
AI can be built incrementally and safely; risky actions are human-gated; costs are bounded; runs are traceable; and the
system remains usable without AI.

## Impacts
- **Security:** allowlisted tools, per-tool permission, scope checks, injection defense, kill switch.
- **Privacy:** data minimization; MED never to provider/public; PII redaction; sanitized traces.
- **Tenant isolation:** every tool call carries validated tenant/branch context; RAG tenant/branch-scoped.
- **Database:** Wave 1+ adds additive `agent_runs`, `agent_steps`, `tool_calls`, cost/trace tables; none in Step 9.
- **Operational:** trace + cost metering + guardrail results + kill switch + evaluation gates observable.
- **Cost:** hard ceilings per tenant/agent/run; metered via `app/Models/UsageRecord.php`; none in Step 9.

## Verification / fitness function
`scripts/docs/verify-step-9.sh` asserts the AI design covers allowlists, tool permissions, approvals, high-risk gates,
confidence, cost ceilings, traces, guardrails, kill switch, and duplicate prevention. Wave-1 AI adds the evaluation
gate (rule 09) with no PII leakage, valid structured output, active approval, cost limit, kill switch, idempotent retry.
AFR-230, AFR-231, AFR-232, AFR-233, AFR-234.

## Related
Requirement: Master Source §75, §33, §34, §44; PRD v1.3.0 §13. Rules: 34, 04, 05, 06, 07, 18. ADRs: 0019, 0023, 0028,
0063, 0065.

## Evidence
`docs/architecture/experience-os/AI_TOOL_PERMISSION_AND_APPROVAL_ARCHITECTURE.md`,
`.claude/rules/05-ai-governance-and-human-approval.md`, `docs/ai/HUMAN_APPROVAL_MATRIX.md`,
`docs/ai/AI_EVALUATION_BASELINE.md`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-9/`.

## Non-claims
Creates no agent, tool, model call, or approval runtime; performs no AI over any data; does not AI-process feedback free
text; does not claim AI is implemented or deployment/pilot/production readiness.

## Rollback
Allowlisted tools, per-tool permission + scope, forbidden-action set, mandatory high-risk approval, cost ceilings,
duplicate-action prevention, tracing, guardrails, kill switch, and manual-works-without-AI are permanent; changing any
requires a new ADR + owner-approved Master Source update.
