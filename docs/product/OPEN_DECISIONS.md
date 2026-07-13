# Open Decisions — Aish Agentic AI

Canonical: PRD §28. Rule: `.claude/rules/12`. These require an owner decision before or during the next
steps; each resolved decision becomes a Master Source update and a `../decisions/DECISION_LOG.md` entry.

| # | Open decision | Area | Status |
|---|---------------|------|--------|
| OD-1 | Final pilot personas and prioritized pilot use cases (Step 2) | Product | RESOLVED — Step 2 baseline (Persona v1.0.0; ADR 0008; D-011..D-015) |
| OD-2 | Application repository architecture (mono vs component repos) — must be a versioned ADR | Architecture | OPEN (Step 3) |
| OD-3 | Domain, branding assets, and public URLs | Product/Brand | OPEN |
| OD-4 | Sprint roadmap breakdown and estimates | Delivery | OPEN |
| OD-5 | Concrete database schema and migrations | Data | OPEN |
| OD-6 | Wireframes / UI information architecture detail | UI/UX | OPEN |
| OD-7 | AI provider selection, model policy, and cost thresholds | AI | OPEN |
| OD-8 | Google Business Profile API access approval and production policy re-verification | Integration | OPEN |
| OD-9 | Deployment target and environment topology | Ops | OPEN |
| OD-10 | Subscription pricing finalization | Commercial | OPEN |

## Step 2 → Step 3 / pilot-readiness open items (Persona §20)

These do not block Step 2 documentation completion; each must close before the relevant implementation or
pilot gate. See `PERSONA_AND_PILOT_USE_CASES.md` §20 and `PILOT_READINESS_CHECKLIST.md`.

| # | Open item | Area | Status |
|---|-----------|------|--------|
| OD-11 | Repository application structure and bounded modules (Step 3) | Architecture | OPEN |
| OD-12 | Blade/Alpine vs Inertia/React frontend | UI/UX | OPEN |
| OD-13 | Single service vs separated AI orchestrator for MVP | Architecture | OPEN |
| OD-14 | Infrastructure topology and environment strategy | Ops | OPEN |
| OD-15 | DaengtisiaMS webhook/API contract + authentication method | Integration | OPEN |
| OD-16 | Named pilot users and final role combination | Product | OPEN |
| OD-17 | Final confirmation that Daengtisia Pusat is the pilot branch | Product | OPEN |
| OD-18 | Google Business Profile ownership/access readiness | Integration | OPEN |
| OD-19 | Approved Daengtisia privacy notice, recovery policy, compensation policy | Policy | OPEN |
| OD-20 | Monetary authority thresholds (if any) | Policy | OPEN |
| OD-21 | Baseline volume and current Google metrics | Product | OPEN |
| OD-22 | Provider decisions: email, WhatsApp, LLM, storage, monitoring, backup | Ops/AI | OPEN |

## Resolution process
1. Owner decision recorded (authority order item 1). 2. Master Source update + changelog (`.claude/rules/12`).
3. ADR when architectural (`../decisions/adr/`). 4. Decision-log + version-matrix entry. 5. Coverage/traceability update.

**Status:** open-decisions baseline documented. OD-1 resolved by the Step 2 persona/pilot baseline
(documentation only); OD-11..OD-22 track Step 3 and pilot readiness. Application implementation NOT STARTED.
