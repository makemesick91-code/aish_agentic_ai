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

## Step 2 — Persona and Pilot Use Cases — MERGED and GO TAGGED (`aish-agentic-ai-step-2-persona-pilot-v1.0.0-go`, peeled `abf1d00`)
Establishes the pilot persona model, pilot use cases (UC-P0-01..16), invitation/survey baseline, healthcare
data boundary, human-approval and anti-gating rules, manual fallback, pilot metrics (as hypotheses), and
GO/WATCH/NO-GO. Canonical: `PERSONA_AND_PILOT_USE_CASES.md` (v1.0.0), Master Source v2.2.0, PRD v1.1.0.
Delivered via branch `docs/step-2-persona-pilot-use-cases` and the annotated
`aish-agentic-ai-step-2-persona-pilot-v1.0.0-go` tag. It does **not** claim application implementation,
deployment, pilot readiness, or pilot runtime.

## Step 3 — Repository Application Architecture and ADR Foundation — ARCHITECTURE BASELINE (this release)
Establishes the canonical application architecture contract: Laravel modular-monolith boundaries (17 modules),
shared-schema row-level multi-tenancy + context propagation, auth/authz, database ownership + migration
governance, queue/cache/storage/search/export/analytics isolation, domain events + outbox/idempotency/retry/
dead-letter, API/webhook contracts, AI service boundary + guardrails/approval, Google/DaengtisiaMS integration
boundaries, environment/deployment topology, observability/audit, backup/restore/rollback/DR, architecture
fitness functions, and ADRs 0009–0032 + Application Foundation Rules (AFR-001..072). Canonical: Master Source
v2.3.0, PRD v1.2.0. Delivered via branch `docs/step-3-application-architecture-adr-foundation` and the annotated
`aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go` tag. It introduces **no** application code — only
empty scaffold markers — and does **not** claim implementation, deployment, or pilot/production readiness.

## Step 4 — Domain, Branding, Environment, and SaaS Foundation Implementation Planning (next)
After Step 3 is merged and GO-tagged: the next step is implementation **planning** for the SaaS foundation
(domain/branding, environment provisioning strategy, and the first implementation slice per Master Source §62),
beginning only after Step 3 is GO-tagged. Step 3 introduces no feature implementation within its own release.

## Default implementation order (Master Source §62)
SaaS Foundation → Survey/CSAT → Feedback Inbox → Recovery Ticket → Basic AI → Google Review Integration →
AI Reply Draft → Human Approval → Agentic Orchestration → Analytics → Billing → Pilot → Production.
Autonomous agents are not built before basic workflows are stable.

## Next recommended action
After Step 3 reaches GO: **Step 4 — Domain, Branding, Environment, and SaaS Foundation Implementation
Planning**, unless a newer owner decision or updated canonical roadmap says otherwise.

**Status:** roadmap baseline documented. Application implementation NOT STARTED.
