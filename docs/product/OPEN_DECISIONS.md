# Open Decisions — Aish Agentic AI

Canonical: PRD §28. Rule: `.claude/rules/12`. These require an owner decision before or during the next
steps; each resolved decision becomes a Master Source update and a `../decisions/DECISION_LOG.md` entry.

| # | Open decision | Area | Status |
|---|---------------|------|--------|
| OD-1 | Final pilot personas and prioritized pilot use cases (Step 2) | Product | OPEN |
| OD-2 | Application repository architecture (mono vs component repos) — must be a versioned ADR | Architecture | OPEN |
| OD-3 | Domain, branding assets, and public URLs | Product/Brand | OPEN |
| OD-4 | Sprint roadmap breakdown and estimates | Delivery | OPEN |
| OD-5 | Concrete database schema and migrations | Data | OPEN |
| OD-6 | Wireframes / UI information architecture detail | UI/UX | OPEN |
| OD-7 | AI provider selection, model policy, and cost thresholds | AI | OPEN |
| OD-8 | Google Business Profile API access approval and production policy re-verification | Integration | OPEN |
| OD-9 | Deployment target and environment topology | Ops | OPEN |
| OD-10 | Subscription pricing finalization | Commercial | OPEN |

## Resolution process
1. Owner decision recorded (authority order item 1). 2. Master Source update + changelog (`.claude/rules/12`).
3. ADR when architectural (`../decisions/adr/`). 4. Decision-log + version-matrix entry. 5. Coverage/traceability update.

**Status:** open-decisions baseline documented. None resolved in this foundation release (documentation only).
