# Agentic Experience OS — PRD Addendum

**Versi:** 1.0.0
**Status dokumen:** Active — governance addendum
**Extends:** `docs/canonical/PRD.md` (v1.3.0, unchanged) — this addendum does not alter the PRD baseline scope; it
records the Experience OS positioning, competitive synthesis, and Wave 1–3 requirement extensions established in Step 9.
**Sprint:** Step 9 — Competitive Gap Audit & Architecture Re-baseline
**Related:** Master Source §75 (v2.11.0), rule 34, `docs/product/EXPERIENCE_OS_ROADMAP.md`,
`docs/product/competitive/STEP_9_COMPETITOR_CAPABILITY_MATRIX.md`,
`docs/planning/STEP_10_CUSTOMER_360_IMPLEMENTATION_CONTRACT.md`
**Canonical repo:** makemesick91-code/aish_agentic_ai

> **Why an addendum and not a PRD version bump:** every step since Step 4 records "PRD v1.3.0 unchanged", and rules
> 28–33 and multiple gates reference that baseline. To keep that invariant stable and avoid destabilizing gates, Step 9
> delivers its product-requirement extensions as this versioned addendum (v1.0.0) rather than a PRD major bump. The PRD
> and Master Source remain synchronized: PRD baseline scope is unchanged; the Experience OS extensions are governed
> here and in Master Source §75. A future implementation step MAY fold this addendum into a PRD v1.4.0.

---

## 1. Positioning (Aish Agentic Experience OS)

Aish Agentic AI is positioned as an **Agentic Experience OS**: an integrated, governed platform spanning survey/CSAT →
feedback operations → customer recovery → reputation → Customer 360 → agentic AI, for multi-branch businesses,
Indonesia-first then global. Positioning constraints of rules 01/22 apply (not a survey/review/chatbot tool; no
misleading autonomy or rating-guarantee claims).

## 2. Competitive synthesis

Benchmarked against ten vendors (Barantum, Qiscus, TapTalk.io, Mekari Qontak, Kata.ai, SurveySensum, SurveySparrow,
SurveyMonkey, Freshdesk Omni, Zendesk) as a **capability reference only** (see the competitor matrix). The strategic
thesis: point tools win single categories; Aish wins by connecting the lifecycle on one tenant-isolated, audited,
human-approval-governed platform — turning feedback into recovery, reputation, and a unified customer view with
governed AI, rather than chasing per-feature parity.

## 3. Current verified implementation status

Steps 5–8 IMPLEMENTED (SaaS core, notification/subscription/platform-admin, survey+CSAT/NPS/CES, feedback operations).
Everything else (Customer 360, recovery, Google Review, AI, omnichannel, knowledge base, analytics, public API,
payment) is **NOT STARTED**. See `docs/product/capability-inventory/STEP_9_CAPABILITY_INVENTORY.md`.

## 4. Wave 1–3 requirement extensions

Governed by `docs/product/EXPERIENCE_OS_ROADMAP.md` (dependency-locked). Wave 1: Customer 360 (next, Step 10),
transaction ingestion, Experience Event Ledger, Recovery OS, basic AI, Google Review, analytics, commercial SaaS +
pilot. Wave 2: omnichannel/conversations, AI copilot/handover/QA, knowledge base + RAG. Wave 3: Agent Studio,
advanced analytics/ROI, public API/marketplace.

## 5. Customer 360 as the next production foundation

Step 10 delivers Customer 360 & identity resolution: a tenant-scoped `Customer` aggregate, deterministic-vs-suggested
identity links with provenance/confidence, human-approved reversible merge/split with immutable audit, consent
history, additive idempotent backfill of Step 8 data, and a Customer 360 read-model — per
`docs/architecture/experience-os/CUSTOMER_IDENTITY_AND_360_ARCHITECTURE.md` and ADR 0064.

## 6. Security & privacy invariants (unchanged, restated)

Tenant/branch isolation on every surface; fail-closed context; human approval for public/high-risk actions; Google
Review anti-gating; MED data never to AI or public output; privacy by design; append-only audit; single entitlement
resolver with security-suspension precedence; secrets never committed; truthful states. These outrank convenience and
automation (Master Source §57).

## 7. Commercial implications

Subscription/entitlement/usage substrate exists (commercial-not-payment). Experience OS enables outcome-based value
(recovery + reputation + 360). Payment/billing/invoicing/tax/dunning remain **DEFERRED** (out of scope §48).

## 8. Success metrics (extends PRD §19/§24; pilot targets are hypotheses until measured — rule 19)

Adoption/response/completion of surveys; feedback triage/SLA; negative-feedback recovery rate; Google review
disposition and reply latency (once implemented); AI structured-output validity and no PII leakage (once implemented);
zero cross-tenant exposure (hard gate). All targets are pilot hypotheses, never reported as achieved without evidence.

## 9. Release & evidence gates

Each wave item follows rule 09/13/28: local gates, exact-SHA CI, review, merge, clean-checkout verification, immutable
GO tag, GitHub Release, post-tag evidence. No fake completion.

## 10. Out of scope for Step 9

Step 9 implements no product feature; it locks architecture, roadmap, and the Step 10 contract. This addendum is a
governance artifact, not an implementation claim.
