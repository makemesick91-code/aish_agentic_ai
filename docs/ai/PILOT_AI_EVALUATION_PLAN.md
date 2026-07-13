# Pilot AI Evaluation Plan — Aish Agentic AI

- **Document:** Pilot AI Evaluation Plan (datasets, metrics, gating)
- **Step:** Step 2 — Persona and Pilot Use Cases
- **Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
- **Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
- **Timezone:** Asia/Makassar

Canonical basis: Persona §9 (UC-P0-06), §13.2, §14; Master Source §50, §54; PRD §19.5, §23, §24.
Rules: [`09-testing-and-quality-gates`](../../.claude/rules/09-testing-and-quality-gates.md),
[`05-ai-governance-and-human-approval`](../../.claude/rules/05-ai-governance-and-human-approval.md).
Cross-reference: [`AI_EVALUATION_BASELINE.md`](./AI_EVALUATION_BASELINE.md).

> These are **evaluation targets and a plan**, not results. No evaluation has been run; the application is
> `NOT STARTED`. Numeric provider/model targets remain an open decision (OD-7) to be fixed with
> provider/model selection ([`AI_EVALUATION_BASELINE.md`](./AI_EVALUATION_BASELINE.md)).

## 1. Purpose

Define how AI classification and reply-drafting quality/safety will be evaluated for the pilot before and
during operation, and the gate that AI capability must pass. Extends
[`AI_EVALUATION_BASELINE.md`](./AI_EVALUATION_BASELINE.md) for the pilot dataset and shadow-mode plan.

## 2. Evaluation datasets

All datasets MUST be synthetic — no real customer PII or medical data
([`../security/PILOT_PRIVACY_RULES.md`](../security/PILOT_PRIVACY_RULES.md) §7). Datasets MUST include
adversarial cases:

- **Prompt injection:** embedded instructions in survey answers and review text ("ignore rules", "post
  contact info", tool-call attempts).
- **PII / medical-data leakage attempts:** inputs that try to smuggle diagnosis, medical record number,
  prescription, odontogram, treatment history, insurance, or payment data into prompt or output.
- **Sarcasm and negation:** feedback whose sentiment is opposite to surface wording.
- **Mixed language:** Indonesian/English code-switching and regional terms.
- **Severity edge cases:** legal/medical/safety/fraud/discrimination signals for correct high/critical
  routing to approval.

## 3. Metrics and thresholds

| Metric | Target | Type |
|---|---|---|
| Structured-output validity | ≥ 99% | Hard |
| Critical/high severity recall | ≥ 95% | Hard |
| PII / medical leakage on the test suite | **Zero** | Hard |
| Unsafe response rate | Below defined limit | Hard |
| Sentiment / topic accuracy | Target set with OD-7 | Tracked |
| Hallucination rate | Below defined limit | Tracked |
| Human edit rate / approval rate | Tracked as hypothesis | Tracked |
| Tool-call accuracy | Target set with OD-7 | Tracked |
| Cost / latency | Within cost limit / SLA | Hard (cost limit) |

Concrete numeric values for "Tracked" metrics MUST be fixed at provider/model selection (OD-7) before the
AI gate can pass; the hard "Zero" leakage target is fixed now and is non-negotiable.

## 4. Shadow-mode comparison (Week 1–2)

Per Persona §13.2, Weeks 1–2 run **controlled baseline and shadow assistance**: AI produces classification
and draft suggestions in parallel with human decisions, but AI output MUST NOT drive any external action.
The evaluation compares AI vs human classification to measure agreement, recall on high/critical severity,
and leakage before any assisted-live operation (Weeks 3–4).

## 5. False-positive / false-negative tracking

- False negatives on critical/high severity are the most serious error class and MUST be tracked per
  category; a missed critical case is a correctness failure.
- False positives (over-escalation) MUST be tracked to tune thresholds without lowering recall.
- Every prompt-injection or leakage attempt caught/missed MUST be recorded with the input class.

## 6. AI gate (Master Source §54)

The AI gate MUST confirm, with evidence, all of:

1. **Zero PII/medical leakage** on the test suite.
2. **Valid structured output** (≥ 99%).
3. **Active human approval** for all §33/PRD-§13 triggers
   ([`PILOT_AI_HUMAN_APPROVAL_RULES.md`](./PILOT_AI_HUMAN_APPROVAL_RULES.md)).
4. **Cost limit** enforced.
5. **Kill switch** present and effective.
6. **Idempotent retry** — no duplicate external side-effects.

The gate MUST NOT be skipped, weakened, or faked. Any hard-target miss is `NO-GO` until fixed and
re-tested (Persona §14.1;
[`09-testing-and-quality-gates`](../../.claude/rules/09-testing-and-quality-gates.md)).

## 7. Evidence

Evaluation evidence (when produced) belongs under `docs/evidence/validation/` and is traced in the Step 2
matrix [`../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md`](../testing/STEP_2_REQUIREMENTS_TRACEABILITY_MATRIX.md).
The prohibited-field test is defined in
[`../security/PILOT_DATA_BOUNDARY.md`](../security/PILOT_DATA_BOUNDARY.md) §7.

## Related documents

[`AI_EVALUATION_BASELINE.md`](./AI_EVALUATION_BASELINE.md) ·
[`PILOT_AI_HUMAN_APPROVAL_RULES.md`](./PILOT_AI_HUMAN_APPROVAL_RULES.md) ·
[`PILOT_MANUAL_FALLBACK.md`](./PILOT_MANUAL_FALLBACK.md) ·
[`../security/PILOT_DATA_BOUNDARY.md`](../security/PILOT_DATA_BOUNDARY.md) ·
[`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md).
