# Human Approval Matrix — Aish Agentic AI

Canonical: Master Source §33, §16.4. Rule: `.claude/rules/05`, `06`. PRD §13.

Human approval is **mandatory** before the action for every trigger below. Low-risk items MAY be automated
early. This matrix MUST be complete against Master Source §33 (verified by the AI-governance reviewer).

## Requires human approval (Master Source §33)
| Trigger | Why |
|---------|-----|
| 1-star review reply | Reputation/policy risk |
| 2-star review reply | Reputation risk |
| Legal risk / legal statement | Liability |
| Medical risk | Healthcare privacy |
| Personal data present | Privacy |
| Fraud allegation | Legal/reputation |
| Threat | Safety |
| Discrimination | Legal/safety |
| Safety issue | Safety |
| Potential viral issue | Reputation |
| Refund / discount / compensation | Financial authorization |
| Data deletion | Irreversible/governance |
| Admission of fault | Liability |
| Low AI confidence | Correctness |
| Policy conflict | Compliance |
| Repeated customer contact | Harassment risk |
| Critical knowledge-base change | Governance |
| Any Google Review reply publication (MVP) | Public action (`.claude/rules/06`) |

## May be automated early (Master Source §33)
Sentiment classification · topic classification · summary · severity suggestion · internal assignment ·
SLA calculation · reminders · draft creation · duplicate detection · spam detection · internal insight.

## Auto-publish preconditions (Master Source §16.4)
Auto-publish of review replies is **prohibited** unless ALL hold: explicit tenant consent, AI-evaluation
targets met, stable guardrails, complete audit, kill switch, rate limit, controlled templates, and
risky-review exclusion. Until then, every reply requires approval.

**Status:** approval matrix documented. Enforcement at implementation (NOT STARTED).
