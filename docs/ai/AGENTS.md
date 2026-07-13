# AGENTS.md — docs/ai/

Area rules for AI. See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/05,18`.

- Supervisor + specialist agents; a single agent MUST NOT do all work (Master Source §23–§33).
- AI runs via a provider abstraction with structured output, timeout, retry, idempotency, redaction, guardrails,
  human approval, prompt+model version, token+cost logging, trace, manual fallback, kill switch (ADR 0019; AFR-041..045).
- Customer content MUST NOT determine tool calls; tools allowlisted, arguments validated (AFR-049).
- `MED`/PII MUST be redacted/excluded before AI input (AFR-046,048). Retrieval is tenant-scoped, minimal (ADR 0023).
- Human approval is mandatory for §33/PRD-§13 triggers and every public reply (AFR-027).
- **Application implementation: NOT STARTED.** No AI provider runs.
