# AI Cost and Tracing — Aish Agentic AI

Canonical: Master Source §21 (AI ops), §37 (governance), §51 (observability). Rule: `.claude/rules/05`, `07`, `11`.

## Mandatory logging (Master Source §37, §51)
Every AI run MUST record: prompt version, model version, tool calls, token usage, cost, latency,
guardrail results, approval decisions, and failures — tenant-scoped (`agent_runs`, `agent_steps`,
`agent_tool_calls`, `agent_guardrail_results`, `agent_approvals`, `agent_failures`, `agent_cost_records`
in `../architecture/DOMAIN_MAP.md`).

## Cost governance
- Cost logging is mandatory and per-tenant reconcilable; a **cost limit** and **kill switch** MUST be active
  (Master Source §54 AI gate).
- Usage metering for AI analysis/draft/published-reply/agent-runs is idempotent, auditable, tenant-scoped,
  plan-aware, and overage-aware (Master Source §46; `.claude/rules/07`).
- Alert on high AI cost and on PII-guardrail failure (Master Source §51; `.claude/rules/11`).

## Tracing
OpenTelemetry-compatible agent and tool-call traces; prompt/model version attached to each trace; failures
routed to a dead-letter workflow (`../architecture/SYSTEM_CONTEXT.md`, `EVENT_CATALOG.md`). Traces MUST NOT
contain secrets or unredacted PII (`.claude/rules/04`).

**Status:** cost/tracing baseline documented. Thresholds OPEN (OD-7). Implementation NOT STARTED.
