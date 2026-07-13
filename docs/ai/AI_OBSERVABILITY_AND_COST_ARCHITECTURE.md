# AI Observability and Cost Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §46, §51 · PRD v1.2.0 §15.3 · **Rules:** `.claude/rules/05`, `07`, `11`, `20` ·
**ADR:** [0024](../decisions/adr/0024-observability-audit-correlation-redaction.md), [0019](../decisions/adr/0019-ai-provider-abstraction.md).

## Traced per AI run
`agent_runs` / `agent_steps` / `tool_calls` capture: prompt version, model version, input/output (redacted),
tokens, cost, latency, retries, guardrail outcomes, correlation id, tenant/branch. Traces are tenant-scoped and
redacted (no `MED`/PII/secret).

## Cost governance
- Per-run and per-tenant **token + cost** logged; feeds Billing usage metering (idempotent, ADR 0016/§46).
- **High AI cost** alert (Master Source §51); a spend threshold triggers the kill switch (ADR 0028).
- Cost limit is a release-gate check for the AI gate (Master Source §54).

## Health signals & alerts
Agent failure spike · AI cost spike · PII guardrail failure · provider latency/errors. Correlation id threads
request→run→tool-call→external effect for end-to-end tracing (ADR 0024).

## Assertion
No AI run, trace, or cost record exists in Step 3. This is the planned AI observability/cost contract.
