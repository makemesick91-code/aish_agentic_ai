# Google Review Policy — Aish Agentic AI

Canonical: Master Source §16, §29. Rule: `.claude/rules/06`. PRD §11.2, §17. **Verify current Google policy/API before production.**

## Prohibited (no gating, no manipulation — Master Source §16.1)
The product MUST NOT:
- route only satisfied customers to Google Review;
- hide reviews from unhappy customers;
- block review access based on CSAT;
- request five stars or a specific rating;
- incentivize positive reviews;
- buy reviews or create fake reviews;
- coerce staff/family to review;
- set fake review targets;
- remove review access based on sentiment.

## Allowed flow (Master Source §16.2)
```
Transaction completed → all eligible customers receive CSAT → feedback analyzed →
negative feedback creates a recovery ticket → all customers keep EQUAL access to Google Review
```

## Reply rules (Master Source §16.3, §29)
Replies MUST be professional, polite, concise, relevant, non-defensive, MUST NOT attack the reviewer, MUST
NOT disclose personal/medical data or sensitive transactions, MUST NOT make unauthorized promises or admit
legal liability without approval, and MUST route sensitive cases to a private channel.

## Approval (Master Source §16.4)
On MVP, **every** reply requires human approval before publication. Auto-publish is prohibited unless all
§16.4 preconditions are met (`docs/ai/HUMAN_APPROVAL_MATRIX.md`).

## Query-smoke answer
*"What is prohibited in Google Review workflows?"* → this file (`§16.1` prohibitions) + `.claude/rules/06`.

**Status:** policy documented. Enforcement at implementation; Google policy re-verified before production (NOT STARTED).
