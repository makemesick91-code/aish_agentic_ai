# Step 9 — Competitive Gap Register

**Status: GOVERNANCE BASELINE**
**Sprint: Step 9**
**Related: STEP_9_COMPETITOR_CAPABILITY_MATRIX, STEP_9_CAPABILITY_INVENTORY, EXPERIENCE_OS_ROADMAP, rule 34**
**Canonical repo: makemesick91-code/aish_agentic_ai**

---

## 1. Purpose and truthful framing

This register enumerates the **competitive capability gaps** between the Aish Agentic AI platform as
currently implemented and the "Experience OS" target positioning (Agentic AI Customer Experience and
Reputation Operating Platform). Every row below is **planned or deferred work** — a gap is never a
delivered capability. Nothing in this document may be read as implemented, deployed, pilot-ready, or
production-ready. Application business-module implementation remains **NOT STARTED** per `CLAUDE.md` §5.

This is a governance artifact, not an implementation plan: it records *what is missing*, *why it matters*,
*where it sits in the build order*, and *what must be true before it is safe to build*. Each gap resolves
into future ADRs, Master Source updates, and per-step GO/WATCH/NO-GO gates.

### Already implemented — explicitly NOT gaps

The following foundations are shipped, merged, and GO-tagged and are therefore **excluded** from this
register (listing them as gaps would be untruthful):

- Multi-tenant / multi-branch isolation, tenant context, RBAC (Spatie teams on `tenant_id`), append-only audit.
- Notification, subscription, entitlement, usage metering, and the platform-admin plane (SF-05).
- Immutable-versioned surveys with deterministic CSAT / NPS / CES (Step 7).
- Feedback operations inbox — projection, lifecycle, assignment, tags, notes, timeline, attachments,
  permission-aware search, bounded bulk operations, and secure export (Step 8, `app/Feedback/**`).

These are the **substrate** the gaps below build on, and each gap cites the specific existing foundation
it extends.

---

## 2. Prioritization method (evidence-based)

Gaps are scored with an explicit, repeatable model so ordering is defensible and not opinion. Each factor
is scored **1 (low) – 5 (high)**; the composite priority is a weighted sum, but **dependency order and
security/privacy risk act as hard constraints** that can override a raw score (a gap cannot be scheduled
before its dependency exists, and an unmitigated privacy risk forces deferral regardless of value).

| Factor | Weight | Direction | Meaning |
|--------|--------|-----------|---------|
| Customer value | ×5 | higher → sooner | Direct value to the pilot/tenant operator and end customer. |
| Strategic differentiation | ×4 | higher → sooner | How much it separates "Experience OS" from a survey/review point tool. |
| Dependency readiness | ×5 | gate | Whether prerequisite foundations exist; a missing dependency defers the gap. |
| Security / privacy risk | ×5 | higher → later / more controls | MED-data, PII, credential, or cross-tenant exposure risk (rules 03/04/05/18). |
| Operational cost | ×3 | higher → later | Build + run cost (S/M/L/XL). |
| Time-to-validate | ×3 | lower → sooner | How fast a hypothesis can be measured in pilot. |

**Wave assignment** is the operational output of the score plus dependency order:

- **Wave 1** — highest customer value, unblocks the core Experience OS loop (identity → event → feedback →
  recovery → reputation → measurement), buildable on existing substrate + the next foundational step.
- **Wave 2** — high value but depends on Wave 1 primitives (channels, AI copilot, knowledge/RAG).
- **Wave 3** — differentiation and extensibility that depend on stable Wave 1–2 automation and data.

Within a wave, gaps are ordered by composite priority, with dependency edges respected.

### Prioritization rationale

The ordering follows the canonical implementation sequence in rule 02 / Master Source §62
(SaaS Foundation → Survey/CSAT → Feedback Inbox → Recovery → Basic AI → Google Review → AI Reply →
Human Approval → Agentic Orchestration → Analytics → Billing). Wave 1 deliberately front-loads the
**data spine** (Customer 360 + event ingestion) because recovery, analytics, and outcome measurement are
all downstream of a unified customer identity and an event ledger — building them first prevents rework
(AFR-099: tenant context and data spine precede business features). AI capabilities are sequenced *after*
the manual feedback/recovery loop is stable (rule 02/05: manual → semi-automated → approved automation →
limited autonomy), and every AI/reputation gap carries permanent guardrail constraints (no MED data to AI,
human approval on risk, Google Review anti-gating). Payment/billing is deferred outright: it is out of MVP
scope (Master Source §48) and the platform preserves the **commercial-≠-payment** invariant (rule 31).

---

## 3. Gap summary table (sorted by wave, then priority)

| ID | Gap | Wave | Resolution | Domain owner | Op cost | Sec/privacy risk |
|----|-----|------|------------|--------------|---------|------------------|
| GAP-09-001 | Customer 360 / unified identity & identity resolution | 1 | BUILD | Customer Profile & Identity Resolution | L | High (PII linkage) |
| GAP-09-002 | Transaction / service-event ingestion | 1 | BUILD + INTEGRATE | Experience Event Ledger | L | Med (source auth) |
| GAP-09-003 | Customer Recovery OS (tickets, SLA, assignment, escalation, playbooks) | 1 | BUILD | Recovery Operations | L | Med (staff access) |
| GAP-09-004 | Basic AI — sentiment/topic/severity/summary | 1 | BUILD | AI Control Plane | M | High (MED exclusion, PII) |
| GAP-09-005 | Google Review & reputation (OAuth, sync, AI reply + approval + publish) | 1 | BUILD + INTEGRATE | Reputation & Google | L | High (OAuth creds, public output) |
| GAP-09-006 | Analytics & outcome measurement (dashboards, ROI, outcome ledger) | 1/2 | BUILD | Analytics & Outcomes | M | Med (aggregation scope) |
| GAP-09-007 | Commercial SaaS hardening (plan/pricing surfaces, entitlement UX) | 1 | BUILD | Commercial Platform | M | Low |
| GAP-09-008 | Omnichannel inbox & conversations (WA/email/chat/social/SMS/voice) | 2 | BUILD + INTEGRATE | Conversation & Channels | XL | High (channel creds, content) |
| GAP-09-009 | AI copilot, human handover & QA | 2 | BUILD | AI Control Plane | L | High (tool permissions) |
| GAP-09-010 | Knowledge base & policy controls + RAG | 2 | BUILD | Knowledge & Retrieval | L | High (tenant-scoped retrieval) |
| GAP-09-011 | Workflow automation & controlled tool actions / Agent Studio | 3 | BUILD | Agentic Orchestration | XL | High (autonomy, side effects) |
| GAP-09-012 | Advanced analytics / branch intelligence / predictive insight | 3 | BUILD | Analytics & Outcomes | L | Med (model governance) |
| GAP-09-013 | Public API, webhooks, integration marketplace | 3 | BUILD + PARTNER | Platform Extensibility | L | High (external surface) |
| GAP-09-014 | Payment / billing / invoicing / tax / dunning | — | DEFER | Commercial Platform | XL | High (financial data) |

---

## 4. Gap detail blocks

### GAP-09-001 — Customer 360 / unified customer identity & identity resolution
- **Problem:** There is no unified customer entity. Feedback items reference their source (survey version)
  but do not resolve to a durable, deduplicated customer across visits, branches, and channels.
- **User/business value:** Operators need "who is this customer, what is their history" to recover
  relationships and measure outcomes; identity is the spine every downstream capability reads.
- **Existing related foundation:** Global user identity model, tenant/branch scoping, and feedback source
  references (`app/Feedback/**`, `app/Models/Feedback*`).
- **Recommended resolution:** BUILD — dedicated Customer Profile domain with deterministic + probabilistic
  identity resolution, immutable merge audit, and consent-aware linkage.
- **Domain owner:** Customer Profile & Identity Resolution.
- **Dependency:** Experience Event Ledger design (feeds identity signals); this is the **next step, Step 10**.
- **Security & privacy risk:** High — cross-record PII linkage; must stay tenant/branch-scoped (rule 03),
  minimize PII (rule 04), and never expose medical/transaction data (rule 18).
- **Migration impact:** Introduces new customer tables; existing feedback gains an optional resolved
  customer reference (additive, backfilled via reconcile — no destructive change).
- **Operational cost:** L.
- **Wave:** 1.
- **Acceptance signal:** A pilot customer with repeat visits resolves to one profile with an auditable,
  reversible merge trail; zero cross-tenant identity bleed in the security matrix.

### GAP-09-002 — Transaction / service-event ingestion
- **Problem:** No canonical intake for `TransactionCompleted` / `VisitCompleted`-class events, so invitation
  timing, recovery triggers, and 360 history have no authoritative source.
- **User/business value:** Event ingestion is what turns the product from a survey tool into an experience
  system — it triggers invitations and feeds recovery and analytics.
- **Existing related foundation:** Internal event/outbox and after-commit domain-event pattern proven by
  Step 8 projection (`app/Events/SurveyResponseCompleted.php`, queued listeners).
- **Recommended resolution:** BUILD the ledger + INTEGRATE signed/authenticated webhook + controlled CSV
  fallback (rule 17: fallbacks shown truthfully, never as real-time integration success).
- **Domain owner:** Experience Event Ledger.
- **Dependency:** Customer 360 (events attach to a customer identity).
- **Security & privacy risk:** Med — source authentication, webhook signature, tenant scoping; no medical
  detail in event payloads by default (rule 18).
- **Migration impact:** New append-only event tables; additive.
- **Operational cost:** L.
- **Wave:** 1.
- **Acceptance signal:** A signed source event ingests idempotently, attaches to the correct customer/branch,
  and drives one invitation with no duplicate side effect on replay.

### GAP-09-003 — Customer Recovery OS (recovery tickets, SLA, assignment, escalation, playbooks)
- **Problem:** Feedback can be triaged and assigned, but there is no recovery workflow with SLA timers,
  escalation, and reusable playbooks to actually close the loop with unhappy customers.
- **User/business value:** Recovery is the core ROI story — turning detractors into retained customers.
- **Existing related foundation:** Feedback assignment, guarded lifecycle, timeline, and notifications in
  `app/Feedback/` (recovery extends, not replaces, these).
- **Recommended resolution:** BUILD a Recovery domain layered on feedback lifecycle; `resolved`/`closed`
  feedback states remain operational only and must not be presented as recovery/refund outcomes (rule 33).
- **Domain owner:** Recovery Operations.
- **Dependency:** Customer 360 + event ingestion (recovery needs customer context and triggers).
- **Security & privacy risk:** Med — branch-scoped staff access, membership-revocation fail-close, no PII
  in audit.
- **Migration impact:** New recovery-ticket + SLA tables referencing feedback items; additive.
- **Operational cost:** L.
- **Wave:** 1.
- **Acceptance signal:** A negative feedback item opens a recovery ticket, escalates on SLA breach, and
  records a full auditable, branch-isolated timeline — usable with AI unavailable.

### GAP-09-004 — Basic AI: sentiment / topic / severity / summary on feedback
- **Problem:** Feedback is operated entirely manually; there is no low-risk AI assist for classification,
  severity suggestion, or summarization.
- **User/business value:** Speeds triage and prioritization at scale without removing human control.
- **Existing related foundation:** Feedback content model and permission-aware content access
  (`feedback.view-content`) in `app/Feedback/**`.
- **Recommended resolution:** BUILD the AI control-plane slice for low-risk, human-supervised tasks only
  (rule 05: only low-risk steps may be automated early); structured output, guardrails, prompt/model
  versioning, cost/trace logging mandatory.
- **Domain owner:** AI Control Plane.
- **Dependency:** Feedback inbox (done); AI provider abstraction + redaction design.
- **Security & privacy risk:** High — MED/medical data **must not** reach the AI provider or public output
  (rules 05/18); customer content must not steer tool calls; human approval for any risky action.
- **Migration impact:** New AI-analysis tables attached to feedback; additive, never mutating source answers.
- **Operational cost:** M.
- **Wave:** 1.
- **Acceptance signal:** AI evaluation baseline passes (no PII/MED leakage on the adversarial dataset,
  valid structured output, cost limit + kill switch active); manual workflow still works with AI off.

### GAP-09-005 — Google Review & reputation (OAuth connection, review sync, AI reply draft + human approval + publish)
- **Problem:** No Google Business Profile connection, review sync, or reply workflow exists.
- **User/business value:** Reputation management is a headline "Experience OS" capability and a primary
  pilot outcome.
- **Existing related foundation:** Notification dispatcher, audit, and human-approval-ready lifecycle
  patterns; survey anti-gating already preserved (rules 06/18/32).
- **Recommended resolution:** BUILD + INTEGRATE Google OAuth/review sync; every reply passes recorded human
  approval before publish (rules 06/18); auto-publish prohibited outside Master Source §16.4 preconditions.
- **Domain owner:** Reputation & Google.
- **Dependency:** Basic AI (reply drafting) + encrypted-credential/OAuth storage.
- **Security & privacy risk:** High — OAuth credentials encrypted at rest and rotatable; **anti-gating is
  permanent** (no routing by CSAT/sentiment); public replies must never disclose PII/medical data; truthful
  external states (no success before provider verification).
- **Migration impact:** New Google-connection/review/reply tables with encrypted token columns; additive.
- **Operational cost:** L.
- **Wave:** 1.
- **Acceptance signal:** A synced review produces a drafted reply that publishes **only** after human
  approval; all eligible customers retain equal review access; publish failures show a truthful failed state.

### GAP-09-006 — Analytics & outcome measurement (owner/branch dashboards, ROI, outcome ledger)
- **Problem:** Metrics exist per-survey (CSAT/NPS/CES) but there is no cross-cutting dashboard, ROI view, or
  outcome ledger tying feedback → recovery → reputation to business results.
- **User/business value:** Owners and branch managers need to see impact and justify the platform.
- **Existing related foundation:** Deterministic metric calculator and tenant/branch scoping (Step 7).
- **Recommended resolution:** BUILD a read-model/outcome-ledger with truthful, non-fabricated metrics
  (rules 10/22: no guaranteed-improvement or fabricated-KPI claims).
- **Domain owner:** Analytics & Outcomes.
- **Dependency:** Experience Event Ledger + recovery data (outcomes derive from events + recovery closure).
- **Security & privacy risk:** Med — tenant/branch aggregation only; branch-restricted users see only their
  branch; no cross-tenant aggregation.
- **Migration impact:** New derived read-model tables; additive.
- **Operational cost:** M.
- **Wave:** 1 (core dashboards) / 2 (ROI + outcome ledger).
- **Acceptance signal:** A branch manager sees only their branch's truthful metrics; ROI numbers trace to
  real events with no fabricated figures.

### GAP-09-007 — Commercial SaaS hardening (plan/pricing surfaces, entitlement UX)
- **Problem:** Subscription/entitlement/usage exist as a backend skeleton but lack tenant-facing plan,
  pricing, and entitlement-management surfaces.
- **User/business value:** Self-serve clarity on plan, limits, and usage; supports commercial motion.
- **Existing related foundation:** Subscription catalog, authoritative entitlement resolver, idempotent
  usage metering (SF-05).
- **Recommended resolution:** BUILD tenant-facing entitlement/plan UX over the existing resolver; **payment
  remains out of scope** and deferred (see GAP-09-014); commercial state ≠ payment state (rule 31).
- **Domain owner:** Commercial Platform.
- **Dependency:** SF-05 subscription/entitlement (done).
- **Security & privacy risk:** Low — no financial data introduced; security suspension still precedes any
  commercial state.
- **Migration impact:** UI/read surfaces over existing tables; minimal schema change.
- **Operational cost:** M.
- **Wave:** 1.
- **Acceptance signal:** A tenant views its plan, entitlements, and current usage truthfully; an ungranted
  entitlement fails closed; no paid/collected state is claimed without provider evidence.

### GAP-09-008 — Omnichannel inbox & conversations (WhatsApp / email / web-chat / social / SMS / voice)
- **Problem:** Only in-app + email notification channels exist; there is no two-way conversational inbox
  across customer channels.
- **User/business value:** Meeting customers on their channel is central to an experience platform.
- **Existing related foundation:** Single notification dispatcher, truthful delivery states, per-channel
  dedup (SF-05); rule 31 already reserves additional channels as deferred.
- **Recommended resolution:** BUILD conversation core + INTEGRATE per-channel adapters behind a channel
  abstraction; provider degradation must not break core workflows (rule 29: manual usable when a provider
  is down).
- **Domain owner:** Conversation & Channels.
- **Dependency:** Channel Adapter design + Customer 360 (conversations attach to a customer).
- **Security & privacy risk:** High — per-channel credentials encrypted; inbound content is untrusted
  (prompt-injection defense); tenant/branch isolation on every channel surface.
- **Migration impact:** New conversation/message/channel-connection tables; additive.
- **Operational cost:** XL.
- **Wave:** 2.
- **Acceptance signal:** A message on one channel routes to the correct tenant/branch conversation with
  truthful delivery states, and a provider outage degrades gracefully without data loss.

### GAP-09-009 — AI copilot, human handover & QA
- **Problem:** No assistive copilot for agents (draft replies, suggest next action) with explicit human
  handover and conversation QA.
- **User/business value:** Raises agent throughput and consistency while keeping a human in control.
- **Existing related foundation:** Basic AI slice (GAP-09-004) and notification/approval patterns.
- **Recommended resolution:** BUILD copilot over the AI control plane with strict tool-permission scoping and
  mandatory handover on risk triggers (rule 05).
- **Domain owner:** AI Control Plane.
- **Dependency:** Basic AI + AI tool-permission design + omnichannel conversations.
- **Security & privacy risk:** High — tool allowlisting; customer content must not determine tool calls; no
  MED data to AI; every risky action human-approved.
- **Migration impact:** Extends AI-run/trace tables; additive.
- **Operational cost:** L.
- **Wave:** 2.
- **Acceptance signal:** Copilot suggestions are always human-gated on risk, tool calls are allowlisted and
  logged, and QA scores are truthful and auditable.

### GAP-09-010 — Knowledge base & policy controls + RAG
- **Problem:** No tenant knowledge base or retrieval layer to ground AI drafts in approved policy content.
- **User/business value:** Consistent, policy-safe, on-brand responses grounded in tenant knowledge.
- **Existing related foundation:** Tenant/branch scoping and audit; AI control plane (GAP-09-004).
- **Recommended resolution:** BUILD tenant/branch-scoped knowledge base + RAG that retrieves only minimum
  relevant, tenant/branch-filtered context (rules 05/07; Master Source §42).
- **Domain owner:** Knowledge & Retrieval.
- **Dependency:** Basic AI + AI copilot.
- **Security & privacy risk:** High — retrieval must be tenant/branch-scoped with no cross-tenant leakage;
  index must not contain secrets/PII/MED data.
- **Migration impact:** New knowledge + embedding/index tables; additive.
- **Operational cost:** L.
- **Wave:** 2.
- **Acceptance signal:** Retrieval returns only the current tenant/branch's approved content; no cross-tenant
  document ever appears in a grounded response.

### GAP-09-011 — Workflow automation & controlled tool actions / Agent Studio
- **Problem:** No configurable automation or supervised multi-agent orchestration for tenant-defined flows.
- **User/business value:** Scales operations via approved automation and limited autonomy over time.
- **Existing related foundation:** Outbox/idempotency patterns; AI control plane; human-approval matrix.
- **Recommended resolution:** BUILD supervised orchestration (supervisor + specialist agents, rule 05) with
  controlled tool actions; **autonomy only after manual/semi-automated flows are stable** (rule 02/05); no
  auto-refund/compensation or fully autonomous handling (Master Source §48).
- **Domain owner:** Agentic Orchestration.
- **Dependency:** AI tool-permission architecture + stable Wave 1–2 workflows.
- **Security & privacy risk:** High — external side effects must be idempotent, outbox-backed, kill-switch
  guarded, and never report success before verification.
- **Migration impact:** New workflow/agent-run tables; additive.
- **Operational cost:** XL.
- **Wave:** 3.
- **Acceptance signal:** A supervised flow executes only allowlisted tools with human approval on risk,
  idempotent retries, and a working kill switch.

### GAP-09-012 — Advanced analytics / branch intelligence / predictive insight
- **Problem:** No predictive/comparative branch intelligence beyond descriptive dashboards.
- **User/business value:** Proactive detection of at-risk branches/customers and trend forecasting.
- **Existing related foundation:** Outcome ledger + analytics read-models (GAP-09-006).
- **Recommended resolution:** BUILD predictive layer with governed models and truthful confidence framing
  (no fabricated KPI or guaranteed-outcome claims, rules 10/22).
- **Domain owner:** Analytics & Outcomes.
- **Dependency:** Analytics/outcome ledger + sufficient event history.
- **Security & privacy risk:** Med — model governance, tenant-scoped features, no cross-tenant training bleed.
- **Migration impact:** Derived model/feature tables; additive.
- **Operational cost:** L.
- **Wave:** 3.
- **Acceptance signal:** Predictions are tenant-scoped, explainable, and labeled with honest confidence,
  never presented as guarantees.

### GAP-09-013 — Public API, webhooks, integration marketplace
- **Problem:** No external developer surface — public API, outbound webhooks, or partner integrations.
- **User/business value:** Ecosystem extensibility and stickiness; partner-driven reach.
- **Existing related foundation:** Internal events/outbox and idempotency patterns (Step 8) that a public
  webhook layer can build on.
- **Recommended resolution:** BUILD public API + webhooks (API key/OAuth, tenant scoping, rate limit,
  idempotency, signatures, versioning — Master Source §39) + PARTNER for marketplace listings; marketplace
  itself is beyond MVP (§48) and stays gated.
- **Domain owner:** Platform Extensibility.
- **Dependency:** Stable domains to expose (Wave 1–2) + external-surface security review.
- **Security & privacy risk:** High — external attack surface; tenant scoping, signature verification, no
  sensitive data in logs, SSRF/abuse controls.
- **Migration impact:** New API-key/webhook-subscription tables; additive.
- **Operational cost:** L.
- **Wave:** 3.
- **Acceptance signal:** An external client authenticates, is tenant-scoped and rate-limited, and receives
  signed idempotent webhooks with no cross-tenant exposure.

### GAP-09-014 — Payment / billing / invoicing / tax / dunning
- **Problem:** No payment collection, invoicing, tax, or dunning.
- **User/business value:** Real revenue collection and financial operations.
- **Existing related foundation:** Subscription/entitlement/usage skeleton (SF-05) — commercial state only.
- **Recommended resolution:** **DEFER.**
- **Domain owner:** Commercial Platform.
- **Dependency:** Commercial SaaS hardening (GAP-09-007) + an approved payment-provider ADR.
- **Security & privacy risk:** High — financial/cardholder data brings PCI-class obligations.
- **Migration impact:** Would introduce financial tables; deferred, not designed here.
- **Operational cost:** XL.
- **Wave:** — (deferred).
- **Explicit deferral reason:** Payment, invoicing, tax, and dunning are **out of MVP scope** (Master Source
  §48) and rule 31 fixes the permanent **commercial-≠-payment** invariant: no paid/collected state may be
  claimed without provider evidence. Building payment before the experience/recovery/reputation loop is
  validated would invert the value order and add PCI risk with no validated demand. Revisit only via a
  dedicated ADR + Master Source update after Wave 1 pilot outcomes justify it.

---

## 5. Governance notes

- Every gap becomes real work only through the standard path: ADR + Master Source update + per-step
  GO/WATCH/NO-GO gate + evidence (rules 12/13). This register grants no implementation authority.
- Permanent invariants apply to all future waves and cannot be traded for speed: tenant/branch isolation
  (rule 03), no secrets committed (rule 04), human approval on risk (rules 05/18), Google Review anti-gating
  (rules 06/18), truthful states (rule 10), and commercial-≠-payment (rule 31).
- Status of all gaps: **PLANNED / DEFERRED**. No gap in this register is implemented. Application
  business-module implementation remains **NOT STARTED**.
