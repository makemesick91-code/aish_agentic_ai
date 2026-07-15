# Step 9 — Competitor Capability Matrix

**Status:** GOVERNANCE BASELINE
**Sprint:** Step 9
**Benchmark date:** 2026-07-15 (repository-truth-first; external claims are capability references only, not verified vendor commitments)
**Related:** `STEP_9_CAPABILITY_INVENTORY`, `STEP_9_COMPETITIVE_GAP_REGISTER`, rule 34
**Canonical repo:** makemesick91-code/aish_agentic_ai

## Disclaimer and reference scope

The ten benchmark vendors named in this document are a **CAPABILITY REFERENCE ONLY**. Naming a
vendor here is **not** authorization to copy any proprietary design, wording, source code, workflow,
UI arrangement, or trade dress. All external capability descriptions are **generalized industry
knowledge** as of the benchmark date (2026-07-15), are stated at a high level, and **may change**
without notice; they are not verified vendor commitments, contracts, or roadmap guarantees.

Where any statement about a competitor and any repository fact appear to conflict, **repository truth
and Aish product security/privacy/governance requirements always take precedence**. This file is a
strategic gap-audit artifact; it does **not** implement code, authorize an integration, or change any
canonical decision. Aish current-status vocabulary is restricted to: `IMPLEMENTED`, `PARTIAL`,
`FOUNDATION ONLY`, `DOCUMENTED-NOT-IMPLEMENTED`, `MISSING`, `DEFERRED`. Only the Step 5–8 substrate is
`IMPLEMENTED`; all business/AI/reputation/recovery modules remain `MISSING` (NOT STARTED) until
evidenced.

## How to read this matrix

- **Representative competitor strength (generalized)** describes a capability the benchmark market is
  broadly known for — not a claim about any single vendor's specific implementation.
- **Aish equivalent** names the corresponding Aish capability family.
- **Current repo status** uses the truthful vocabulary above and is grounded in the repository
  (Steps 5–8) and the canonical rules.
- **Evidence** references repository paths, Claude rules, or ADRs as inline code spans.
- The audit measures **integrated value**, not raw feature parity; Aish's thesis is one governed,
  tenant-isolated Experience OS, not a collection of point tools.

## Main capability matrix

| Capability family | Representative competitor strength (generalized) | Aish equivalent | Current repo status | Evidence (repo path / rule / ADR) | Gap | Strategic importance | Proposed wave | Dependency | Risk | Cost class | Decision |
|---|---|---|---|---|---|---|---|---|---|---|---|
| CRM service & Customer 360 | Unified customer profile, contact history, segmentation, lifecycle timeline | Customer 360 identity & history over tenant/branch data | MISSING | `.claude/rules/07`; Master Source §36; Step 10 target | No customer/contact/transaction domain yet | High | Wave 1 | Tenant/branch core (Step 6), feedback (Step 8) | Scope creep; PII minimization | L | BUILD |
| CSAT/NPS/CES surveys & Voice of Customer | Survey builder, multi-metric scoring, distribution, VoC dashboards | Immutable-versioned surveys, campaigns, deterministic CSAT/NPS/CES | IMPLEMENTED | `app/Surveys/`, `app/Models/Survey*`; rule 32; Step 7 | Advanced conditional logic and benchmarking deferred | High | Delivered (Step 7) | Public token security, MetricCalculator | Over-claiming parity | — | BUILD |
| Feedback inbox & classification | Central feedback queue with AI sentiment/topic/severity tagging | Feedback inbox (projection, lifecycle, tags, notes, export) | IMPLEMENTED (inbox); MISSING (AI classification) | `app/Feedback/`, `app/Models/Feedback*`; rule 33; Step 8 | AI sentiment/topic/severity not implemented (deferred) | High | Wave 1 (AI classification) | Basic AI runtime, guardrails (rule 05) | Untrusted-input/prompt-injection | M | BUILD |
| Customer recovery / ticketing / SLA / escalation / playbooks | Case management, SLA timers, escalation, resolution playbooks | Recovery OS: tickets, SLA, assignment, escalation | MISSING | Master Source §35 (`RecoveryTicket*`, `SLABreached`); rule 02 | No recovery/ticket/SLA domain yet | High | Wave 1 | Feedback (Step 8), notification (SF-05) | Human-approval gaps | L | BUILD |
| Google Review & reputation workflows | Review sync, AI reply drafting, publish, reputation dashboards | Reputation workflow with human approval, anti-gating | MISSING (integration); DOCUMENTED-NOT-IMPLEMENTED (policy) | rules 06, 18; Master Source §16, §38 | No Google OAuth/sync/reply; governance rules exist | High | Wave 1 | Google OAuth, human approval (rule 05) | Review gating / policy breach | L | BUILD |
| Omnichannel inbox & routing | WhatsApp/email/social conversations, unified inbox, routing rules | Omnichannel conversation inbox and routing | MISSING | rule 02 (out-of-scope early); rule 31 (future channels) | Only in-app + email notification exist (SF-05) | Med | Wave 2 | Channel providers, tenant isolation | Provider lock-in; PII | XL | INTEGRATE |
| AI agent / copilot / human handover / QA | AI assistant, suggested replies, agent-assist, conversation QA | Agentic AI copilot with supervisor/specialist agents | MISSING (runtime); DOCUMENTED-NOT-IMPLEMENTED (governance) | rules 05, 18, 20; Master Source §23–§33 | No AI runtime; multi-agent architecture documented only | High | Wave 1 (basic AI) → Wave 2 (copilot/handover) | Provider abstraction, guardrails, tracing | Autonomy overreach; cost | L | BUILD |
| Knowledge base & policy controls | KB articles, help center, policy-grounded answers, RAG | Tenant/branch-scoped knowledge base + RAG grounding | MISSING | rules 05, 20; Master Source §42 | No KB or retrieval; scoping rules documented | Med | Wave 2 | AI runtime, tenant-scoped retrieval | RAG cross-tenant leakage | M | BUILD |
| Workflow automation & controlled tool actions | Trigger/action automation, macros, controlled external actions | Governed workflow automation with allowlisted tools | MISSING | rules 05, 08; Master Source §35 (events) | No automation engine; event catalog documented | Med | Wave 3 | Event/outbox, tool allowlist, approval | Uncontrolled side effects | L | BUILD |
| Analytics / branch intelligence / ROI / outcome measurement | Dashboards, trend analytics, ROI and outcome attribution | Owner/branch dashboards, ROI and outcome intelligence | MISSING | rule 11; Master Source §51; PRD §19 | No analytics/dashboard surface yet | High | Wave 1 (basic) → Wave 3 (advanced ROI) | Surveys, feedback, recovery data | Vanity metrics; false ROI | M | BUILD |
| API / webhooks / integrations / usage metering & commercial entitlements | Public API, webhooks, marketplace, metered billing plans | Usage metering + entitlement resolver + subscription state | PARTIAL | `app/Models/UsageRecord.php`; rule 31; SF-05 | Public API/webhooks MISSING; billing/payment MISSING | High | Wave 1 (commercial) → Wave 3 (API/marketplace) | Subscription core (SF-05), payment provider | Entitlement bypass; billing truthfulness | L | BUILD/INTEGRATE |

**Status legend applied above:** `IMPLEMENTED` = evidenced in Steps 5–8; `PARTIAL` = some sub-capabilities
implemented, others missing; `FOUNDATION ONLY` = substrate present, business surface absent;
`DOCUMENTED-NOT-IMPLEMENTED` = governance/rules exist, no runtime; `MISSING` = not started; `DEFERRED` =
explicitly scheduled to a later step.

## Cross-cutting substrate (already IMPLEMENTED FOUNDATION)

The following are not a single row above because they underpin every capability family. They are
`IMPLEMENTED FOUNDATION` (Steps 5, 6, SF-05) and are the reason Aish can build integrated capabilities
safely rather than as disconnected tools:

- Multi-tenant + multi-branch isolation, RBAC, immutable fail-closed tenant context, append-only audit —
  `app/Tenancy/`, `app/Audit/`; rules 03, 30; Step 6.
- Notification delivery with truthful states, subscription/entitlement resolver, usage metering, and a
  least-privilege platform-admin plane — `app/Models/UsageRecord.php`, `app/Subscriptions/`, `app/Platform/`;
  rule 31; SF-05.
- Reproducible Laravel 12 runtime, health/readiness probes, queue/scheduler foundation, security baseline —
  `scripts/runtime/`; rule 29; Step 5.

## Per-competitor synthesis (generalized capability reference)

**Barantum.** Generalized as an Indonesia-market CRM and omnichannel sales/service suite known for
pipeline, contact management, and WhatsApp-centric customer engagement. Maps primarily to Aish's *CRM
service & Customer 360* and *Omnichannel inbox & routing* families. Aish differentiates by governing the
customer record inside a tenant-isolated, audited Experience OS rather than as a standalone CRM.

**Qiscus.** Broadly known for conversational/omnichannel customer engagement and multichannel chat
infrastructure (WhatsApp and messaging channels) with agent workspaces. Maps to *Omnichannel inbox &
routing* and *AI agent/copilot*. Aish treats conversations as one governed input into the recovery and
reputation lifecycle, not the whole product.

**TapTalk.io.** Generalized as a messaging/omnichannel and in-app chat SDK provider emphasizing WhatsApp
business messaging and chatbot flows. Maps to *Omnichannel inbox & routing* and *Workflow automation*.
Aish's advantage is that any channel action remains bounded by human approval and tool allowlisting.

**Mekari Qontak.** Broadly known for combined CRM plus omnichannel WhatsApp engagement and sales/service
automation in the Indonesian SME/enterprise market. Maps to *CRM service & Customer 360*, *Omnichannel*,
and *Workflow automation*. Aish differentiates through branch-level intelligence and audited, entitlement-
gated multi-tenancy.

**Kata.ai.** Generalized as a conversational-AI and NLP/chatbot platform for automated customer
interactions in Bahasa Indonesia. Maps to *AI agent/copilot/human handover* and *Knowledge base & RAG*.
Aish's agentic layer is explicitly supervised (supervisor + specialist agents, guardrails, human
approval) rather than autonomous by default.

**SurveySensum.** Broadly known for CX surveys with CSAT/NPS/CES and text/VoC analytics oriented to
closing the loop on feedback. Maps directly to *CSAT/NPS/CES surveys & VoC* and *Feedback inbox &
classification*. Aish already implements deterministic, immutable-versioned surveys and a feedback inbox,
and extends the loop into recovery and reputation.

**SurveySparrow.** Generalized as a conversational-survey and experience-management platform with
omnichannel distribution and reputation/review touchpoints. Maps to *CSAT/NPS/CES surveys & VoC* and
*Google Review & reputation workflows*. Aish's survey engine is already implemented with hashed one-time
tokens and no-enumeration public access.

**SurveyMonkey.** Broadly known as a large-scale general survey platform with extensive question types,
templates, and analytics. Maps to *CSAT/NPS/CES surveys & VoC* and *Analytics*. Aish focuses on a narrower
but deterministic, tenant-isolated CX metric core (single MetricCalculator) rather than general-purpose
survey breadth.

**Freshdesk Omni.** Generalized as an omnichannel customer-service suite combining ticketing, omnichannel
inbox, SLA management, and AI assist. Maps to *Customer recovery/ticketing/SLA/escalation*, *Omnichannel*,
and *AI copilot*. Aish's recovery OS is designed to be feedback-driven and reputation-linked on one
governed platform.

**Zendesk.** Broadly known for enterprise-grade ticketing, SLA/escalation, knowledge base, omnichannel,
and AI agent/QA capabilities. Maps to *Customer recovery*, *Knowledge base & policy controls*,
*Omnichannel*, and *AI agent/copilot/QA*. Aish differentiates by tightly integrating recovery with
CSAT/feedback origin and human-approved reputation actions for multi-branch SMEs.

## Where Aish creates superior integrated value

Aish's thesis is **not feature parity** against any single benchmark vendor — most competitors are strong
in one or two families (surveys, or CRM, or ticketing, or conversational AI). Aish's differentiated value
is the **integrated Experience OS**: a single, continuous, governed loop that runs

`survey → feedback → recovery → reputation → Customer 360 → agentic AI`

on **one tenant-isolated, audited, human-approval-governed platform built for multi-branch businesses**.

The integration is the moat, and it rests on properties already `IMPLEMENTED FOUNDATION`:

- **One tenant/branch isolation boundary** across every surface (DB, cache, queue, storage, search,
  export, notifications) — rules 03, 30 — so cross-capability data never crosses a tenant or branch.
- **Append-only audit and truthful states** across the whole loop — rules 07, 10 — instead of per-tool,
  unreconciled logs.
- **Human approval and anti-gating governance** wired into reputation and high-risk actions from day one —
  rules 05, 06, 18 — instead of bolt-on moderation.
- **A single authoritative entitlement resolver and idempotent usage metering** — rule 31 — so commercial
  packaging spans all capabilities coherently, without conflating commercial state with payment.

A competitor stitching survey + ticketing + review + CRM + AI from separate products inherits seam risk at
every boundary: inconsistent tenancy, duplicated identity, un-audited handoffs, and ungoverned AI actions.
Aish removes those seams by design. The strategic goal is **outcome measurement for multi-branch operators**
(did a branch recover the customer, did reputation improve, at what cost) — an integrated result no single
point tool produces.

## Wave mapping summary

Waves are sequenced so that each capability lands only after its dependencies and governance guarantees
exist. Nothing below is implemented beyond the Steps 5–8 substrate; waves are planning intent.

- **Wave 1 (integrated core + commercial pilot).** Customer 360, Recovery OS (tickets/SLA/escalation),
  basic AI (feedback classification: sentiment/topic/severity), Google Review & reputation workflows with
  human approval, basic analytics/branch dashboards, commercial SaaS packaging (entitlements + metering,
  building on SF-05), and pilot readiness. This completes the core loop `survey → feedback → recovery →
  reputation → Customer 360` on the existing governed substrate.
- **Wave 2 (conversation + assist + knowledge).** Omnichannel inbox and conversation routing, AI
  copilot/agent-assist with human handover and conversation QA, and a tenant/branch-scoped knowledge base
  with RAG grounding. This layers real-time engagement and assisted resolution on top of the Wave 1 loop.
- **Wave 3 (automation + advanced intelligence + ecosystem).** Agentic automation / Agent Studio (governed
  tool actions, controlled autonomy), advanced analytics/ROI and outcome attribution, and a public
  API/webhooks/marketplace and integrations layer. This extends Aish from an operated platform to an
  extensible, outcome-measured ecosystem.

**Governance constraint on all waves:** each wave item MUST preserve tenant/branch isolation, append-only
audit, human approval for public/high-risk actions, Google Review anti-gating, prompt-injection defense on
untrusted feedback, and truthful status — none of these may be weakened for competitive parity. Any wave
item that materially changes scope or architecture requires an ADR and a Master Source update per rules 12
and 20.
