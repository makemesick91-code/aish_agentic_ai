---
id: "02"
title: MVP Scope and Roadmap
domain: product
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §47, §48, §49, §62"
  - "PRD §5, §10, §27"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 02 — MVP Scope and Roadmap

## Purpose
Prevent scope creep and preserve the foundation-first build order.

## Scope
Planning, sprint definition, and any implementation prompt.

## Rules
- MVP scope **MUST** be limited to the 36 items in Master Source §47 (multi-tenant, multi-branch,
  user/role/permission, simple survey builder, CSAT/NPS/CES, QR & link & WhatsApp & email invitation,
  feedback inbox, AI sentiment/topic/severity/summary, recovery ticket + SLA + assignment + escalation,
  Google connection/mapping/sync, AI reply draft + human approval + publish, owner/branch dashboards,
  audit log, usage metering, basic subscription, platform admin, basic knowledge base, security
  foundation, backup/restore, basic observability).
- Out-of-scope items (Master Source §48) — e.g. AI auto-refund/compensation, fully autonomous complaint
  handling, auto-publish of all replies, voice agent, integration marketplace — **MUST NOT** be built
  without a new versioned decision.
- The default implementation order (Master Source §62) **MUST** be followed: SaaS Foundation → Survey/CSAT
  → Feedback Inbox → Recovery Ticket → Basic AI → Google Review → AI Reply Draft → Human Approval →
  Agentic Orchestration → Analytics → Billing → Pilot → Production.
- Autonomous agents **MUST NOT** be built before basic workflows are stable (manual before automation).
- Any scope change **MUST** produce a Master Source update (`.claude/rules/12`).

## Required checks
- Roadmap/scope statements in derived docs trace to Master Source §47-§49; verified in the coverage matrix.

## Evidence
- `docs/product/MVP_SCOPE.md`, `docs/product/ROADMAP.md`, `docs/quality/FOUNDATION_COVERAGE_MATRIX.md`.

## Related canonical sections
- Master Source §45 (plans), §46 (metering), §47-§49 (scope/roadmap), §62 (order); PRD §5, §10, §27.

## Supersession
Superseded only by a higher-version Master Source update recording the scope/roadmap change.
