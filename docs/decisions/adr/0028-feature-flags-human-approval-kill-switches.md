# ADR 0028 — Feature Flags, Human Approval, and Kill Switches

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** AI Governance / Product Architect
- **Rule:** `.claude/rules/05`, `06`, `18`, `20` (AFR-027..030) · **Canonical:** Master Source v2.3.0 §16, §33; PRD v1.2.0 §13, §16

## Context
Risky and public actions must be human-controlled and reversible; capabilities must roll out safely and be
switchable off without breaking manual operation.

## Decision
- **Human approval** is required for Master Source §33 / PRD §13 triggers (abridged; complete set in the Human
  Approval Matrix): 1–2★ reviews; legal/medical/safety/fraud/discrimination risk; threat; potential viral issue;
  PII; refunds/discounts/compensation; data deletion; legal statements; low AI confidence; policy conflict;
  admission of fault; repeated contact; critical knowledge-base change. Every public Google reply is human-approved.
- **Kill switches** disable AI/external-effect classes without data loss (outbox resumable).
- **Feature flags** gate rollout; flags are auditable; a flag never disables audit, approval, or isolation.
See [AI Guardrail & Approval](../../ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md).

## Alternatives
- **Auto-publish / autonomous risky actions** — rejected: forbidden outside Master Source §16.4 preconditions.
- **Hard-coded toggles** — rejected: not auditable/operable.

## Consequences
Safe, reversible operation with human oversight; requires an approval workflow, flag store, and kill switch.

## Impacts
- **Security:** kill switch limits incident blast radius.
- **Privacy:** approval gate before any public disclosure.
- **Tenant isolation:** flags/approvals tenant-scoped.
- **Database:** approvals + flags auditable.
- **Operational:** controlled rollout + emergency stop.
- **Cost:** kill switch caps runaway AI cost.

## Verification / fitness function
FF-SEC-03, FF-SEC-04, FF-AI-03, FF-AI-05. Implementation: approval-gate, kill-switch, anti-gating tests.

## Related
Requirement: Master Source §16, §33; PRD §13. Application rule: AFR-027..030. ADRs: 0019, 0021.

## Evidence
`docs/ai/AI_GUARDRAIL_AND_APPROVAL_ARCHITECTURE.md`, `docs/ai/HUMAN_APPROVAL_MATRIX.md`.

## Non-claims
No approval workflow, flag, or kill switch runs in Step 3.

## Rollback / supersession
Approval requirements are permanent; relaxations require Master Source §16.4 preconditions + update.
