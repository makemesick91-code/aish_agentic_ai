# Personas Baseline — Aish Agentic AI

Canonical: Master Source §14, PRD §7. Rule: `.claude/rules/01`.

| Persona | Core needs |
|---------|-----------|
| Business Owner | Executive summary, branch ranking, rating trend, top complaints, SLA compliance, recovery rate, recommendations. |
| Corporate Admin | Manage tenant, branches, users, roles, surveys, integrations, subscription, permissions. |
| Regional Manager | Regional comparison, problem branches, overdue tickets, regional rating, escalation. |
| Branch Manager | Branch feedback & reviews, ticket assignment, SLA, action recommendations, replies pending approval. |
| Customer Experience Manager | Feedback inbox, ticket triage, customer recovery, root cause, SLA management, escalation. |
| Customer Service | Task list, response drafts, customer history, resolution checklist. |
| Reputation Manager | Review inbox, reply drafts, approval, publish, rating trend, response time. |
| Platform Admin | Tenant management, subscription, integration health, AI usage, failure monitoring, support tools, audit. |

## Platform & tenant roles
Platform roles: Super Admin, Admin, Support, Finance, Compliance, Auditor, AI Operations, Read-only.
Tenant roles: Business Owner, Corporate Admin, Regional/Branch Manager, CX Manager, Customer Service,
Reputation Manager, Ticket Assignee, Reviewer, Approver, Analyst, Finance Admin, Integration Admin,
Read-only (Master Source §18). Permissions follow the §18 permission groups with branch/region scoping.

## Note
The pilot tenant (Klinik Gigi Daengtisia) must not narrow the generic core (`.claude/rules/01`).
Detailed persona/pilot use cases are the subject of the next product step (Roadmap → Step 2).

**Status:** persona baseline documented. Implementation NOT STARTED.
