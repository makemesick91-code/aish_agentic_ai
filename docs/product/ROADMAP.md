# Roadmap — Aish Agentic AI

Canonical: Master Source §49 (phases), §62 (default implementation order), §64 (next action). Rule: `.claude/rules/02`. PRD §27.

## Phase sequence (Master Source §49)
0. Product Discovery (PRD) — **COMPLETE (documentation baseline)**.
1. SaaS Foundation · 2. Survey Foundation · 3. Feedback Operations · 4. Customer Recovery ·
5. Basic AI · 6. Google Integration · 7. AI Review Response · 8. Agentic Orchestration ·
9. Analytics · 10. Commercial SaaS · 11. Pilot (3–5 tenants) · 12. Production Launch (only after all release gates pass).

## Documentation & Claude Rules Foundation (prerequisite) — COMPLETE
Per Master Source §64.2 and the GO Tag Prompt v1.0.1, this step established the canonical repository
documentation, Claude rules, tooling governance, CI, evidence, merge, and the annotated
`aish-agentic-ai-docs-foundation-v1.0.0-go` tag (MERGED and GO TAGGED). It does **not** claim application
implementation.

## Step 2 — Persona and Pilot Use Cases — DOCUMENTATION BASELINE (this release)
Establishes the pilot persona model, pilot use cases (UC-P0-01..16), invitation/survey baseline, healthcare
data boundary, human-approval and anti-gating rules, manual fallback, pilot metrics (as hypotheses), and
GO/WATCH/NO-GO. Canonical: `PERSONA_AND_PILOT_USE_CASES.md` (v1.0.0), Master Source v2.2.0, PRD v1.1.0.
Delivered via branch `docs/step-2-persona-pilot-use-cases` and the annotated
`aish-agentic-ai-step-2-persona-pilot-v1.0.0-go` tag. It does **not** claim application implementation,
deployment, pilot readiness, or pilot runtime.

## Step 3 — Repository Application Architecture and ADR Foundation (next)
After Step 2 is merged and GO-tagged: repository application structure, Laravel modular-monolith boundaries,
tenant-isolation architecture, auth/authz, database ownership, queue/cache/storage tenancy, API/webhook
architecture, AI service boundary, event contracts, environment strategy, CI/CD application foundation,
deployment topology, backup/restore, observability, and Architecture Decision Records. Step 3 introduces no
application code within the Step 2 release — only this roadmap pointer.

## Default implementation order (Master Source §62)
SaaS Foundation → Survey/CSAT → Feedback Inbox → Recovery Ticket → Basic AI → Google Review Integration →
AI Reply Draft → Human Approval → Agentic Orchestration → Analytics → Billing → Pilot → Production.
Autonomous agents are not built before basic workflows are stable.

## Next recommended action
After this foundation reaches GO: **Step 2 — Persona and Pilot Use Cases** (Master Source §64.3), unless a
newer owner decision or updated canonical roadmap says otherwise.

**Status:** roadmap baseline documented. Implementation NOT STARTED.
