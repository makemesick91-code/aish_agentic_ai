# ADR 0063 — Agentic Experience OS Domain Architecture and Source-of-Truth Boundaries

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 Competitive Gap Audit & Architecture Re-baseline; architecture LOCK only, un-built domains NOT STARTED
- **Owner:** Principal Product Architect / Software Architect
- **Rule:** `.claude/rules/34`, `.claude/rules/03`, `.claude/rules/20` · **Canonical:** Master Source §75, §34–§42, §62; PRD v1.3.0; rules 34, 03, 20, 30

## Context
Aish Agentic AI is expanding from survey + feedback (Steps 7–8) into an Agentic Experience OS (Customer 360, recovery,
reputation, conversations, AI). Without a locked domain map, expansion risks duplicate ownership — e.g. Recovery
mutating feedback state, Feedback creating customer identity, a new ledger replacing the immutable Step 8 timeline, or
Notification owning inbound conversations. The Step 3 `docs/architecture/DOMAIN_MAP.md` (17 modules, module owns its
data — ADR 0010) is the baseline; this ADR refines it for the new domains and locks a single source of truth per
capability.

## Decision
- Adopt the domain ownership table in `docs/architecture/experience-os/DOMAIN_BOUNDARY_MAP.md` as the locked contract:
  exactly one domain owns each aggregate's write path; other domains read via a published interface or react to a
  typed, versioned domain event; no domain writes another domain's tables.
- **Customer identity** is owned only by Customer Profile & Identity Resolution; Survey/Feedback/Recovery/Review/
  Conversation reference a customer id, never create/merge/mutate it.
- **Feedback item lifecycle** is owned only by Feedback Operations; Recovery reacts to feedback events and owns its own
  ticket lifecycle; `resolved`/`closed` are not recovery outcomes.
- The **Feedback Timeline** (`app/Models/FeedbackEvent.php`) stays authoritative; the wider **Experience Event Ledger**
  is additive and does not replace it (ADR 0065).
- Every cross-boundary interaction carries tenant/branch context and crosses only references and minimized non-sensitive
  attributes — never raw free text, MED data, secrets, or tokens.

## Alternatives
- **Let each new feature own overlapping data** — rejected: duplicate ownership causes drift, double writes, and
  cross-domain coupling.
- **One monolithic "experience" table/service** — rejected: violates module ownership (ADR 0010) and tenant-isolation
  clarity.
- **Replace the Step 8 timeline with a generic ledger now** — rejected: destructive and unnecessary; the timeline is
  preserved and the ledger is additive.

## Consequences
Wave 1–3 domains have unambiguous ownership and interfaces; duplicate ownership is prohibited by rule 34; the Step 8
foundation is preserved; and expansion proceeds without reopening fundamentals.

## Impacts
- **Security:** single-writer boundaries reduce cross-domain write paths; tenant/branch context on every event.
- **Privacy:** only minimized non-sensitive references cross boundaries; MED data never crosses into AI/public/analytics.
- **Tenant isolation:** every domain and event is tenant-scoped; no cross-tenant interface.
- **Database:** no schema change in Step 9; new domains add their own additive tables later (ADR 0068).
- **Operational:** clear failure ownership per domain; events are the integration seam.
- **Cost:** none in Step 9 (governance/design only).

## Verification / fitness function
Step 9 verification (`scripts/docs/verify-step-9.sh`) asserts the boundary map exists, references every major domain,
and resolves duplicate ownership; future per-step architecture boundary tests (e.g. `tests/Architecture/Sf10Boundaries*`)
enforce single-writer ownership when domains are built. AFR-211, AFR-212, AFR-213, AFR-214.

## Related
Requirement: Master Source §75, §34–§42, §62; PRD v1.3.0. Rules: 34, 03, 20, 30. ADRs: 0009, 0010, 0016, 0052, 0064,
0065, 0066, 0067, 0068.

## Evidence
`docs/architecture/experience-os/DOMAIN_BOUNDARY_MAP.md`, `docs/architecture/experience-os/README.md`,
`docs/product/capability-inventory/STEP_9_CAPABILITY_INVENTORY.md`; `docs/governance/foundation-coverage-matrix.md`;
`docs/evidence/step-9/`.

## Non-claims
Implements no domain; creates no table, migration, or runtime; does not claim Customer 360, Recovery, Reputation,
Conversations, or AI are built (all NOT STARTED); does not claim deployment/pilot/production readiness.

## Rollback
Single-writer domain ownership, the customer-identity/feedback-lifecycle/timeline boundaries, and
tenant/branch-context-on-every-event are permanent architecture constraints; changing a boundary requires a new ADR and
an owner-approved Master Source update. Historical decisions are preserved, never deleted.
