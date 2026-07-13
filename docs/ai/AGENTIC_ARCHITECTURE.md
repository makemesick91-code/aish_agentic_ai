# Agentic AI Architecture — Aish Agentic AI

Canonical: Master Source §23–§32, §34 (AI boundary), §35. Rule: `.claude/rules/05`, `08`. PRD §12.

## Principle
No single agent does all work. Use a **supervisor + specialist** design (Master Source §23).

```
Event → Supervisor Agent
        ├── Feedback Intake Agent
        ├── Sentiment and Topic Agent
        ├── Severity and Risk Agent
        ├── Recovery Agent
        ├── Google Review Response Agent
        ├── Policy and Privacy Guardrail Agent
        ├── Insight Agent
        └── Notification Agent
```

## Agent responsibilities (Master Source §24–§32)
- **Supervisor:** route workflow, select agents/tools, manage handoff, decide approval, stop unsafe work,
  handle retry, dead-letter failures, preserve tenant/branch/permission context. MUST NOT bypass approval.
- **Feedback Intake:** clean/validate input, detect language, link customer/transaction/branch, flag spam/dup, structured output.
- **Sentiment & Topic:** polarity, emotion, topic/subtopic, confidence, summary, follow-up need.
- **Severity & Risk:** severity/priority, medical/legal/safety/fraud/discrimination/PII/reputation risk, escalation, SLA, `requires_human`.
- **Recovery:** recovery plan, PIC, SLA, internal + customer-contact drafts, corrective/preventive actions. MUST NOT promise refund/discount/compensation or admit legal fault without authorization.
- **Google Review Response:** tone-aware draft using knowledge, no PII/medical, route sensitive cases to private channel, confidence + rationale.
- **Policy & Privacy Guardrail:** block unsafe output; require approval when in doubt (`PROMPT_INJECTION_DEFENSE.md`).
- **Insight:** compare branches/periods, trends, anomalies, root cause, executive summary, recommendations.
- **Notification:** recipient/channel/urgency, dedup, escalation, digest, quiet hours, tenant/branch scope.

## Cross-cutting requirements
Structured output (e.g. Pydantic), guardrails, allowlisted tools, tracing, prompt/model versioning, cost
logging, controlled retry (no duplicate side-effects), kill switch. Basic workflow MUST work without AI
(`.claude/rules/05`). Only low-risk steps automated early (Master Source §33).

**Status:** agentic baseline documented. Implementation NOT STARTED.
