# Frontend Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §34, §52, §53 · PRD v1.2.0 §16, §21 · **Rules:** `.claude/rules/10`, `20` ·
**ADR:** [0018](../decisions/adr/0018-frontend-architecture.md).

## 1. Initial stack (ADR 0018)
**Blade + Tailwind CSS + Alpine.js**, server-rendered, progressive enhancement. Rationale: fastest safe path to
a usable enterprise MVP console; no SPA build/ops burden for pilot; accessible and mobile-responsive by default.
A richer SPA/Inertia/API-first frontend is a **future option** requiring an ADR — not a Step 3 decision.

## 2. Required UI states (Master Source §52; `.claude/rules/10`)
Every screen provides **empty, loading, failure, and permission-denied** states. Professional, clean,
enterprise-ready, SME-usable, mobile-responsive, accessible, multi-language capable.

## 3. Truthful states (Master Source §53; PRD §16)
The UI **MUST NOT** show success before the underlying/external action is verified. It uses the canonical
vocabularies:
- **Connection:** not connected → connecting → connected → error → disconnected.
- **AI:** idle → queued → running → succeeded → failed → killed.
- **Reply:** no draft → draft generated → under review → changes requested → approved → publishing → published
  → publication failed → moderation pending → policy issue → removed.
- **Ticket:** open → assigned → in progress → escalated → resolved → SLA breached.

Fabricated/sample data **MUST NOT** be shown as real; timelines are audit-friendly.

## 4. AI-independence
Basic UI functions **MUST NOT** depend on AI availability. Draft/approval screens degrade to manual entry when
AI is down (`.claude/rules/05`, `10`).

## 5. Tenant/branch awareness
The UI reflects the caller's tenant/branch scope; a branch-scoped user never sees another branch's data. The
frontend is a projection of authorized server state, not an independent trust boundary.

## 6. Truthful status
No views, components, or assets are implemented in Step 3. This is the contract implementation must satisfy.
