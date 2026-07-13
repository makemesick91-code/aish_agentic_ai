# DaengtisiaMS `VisitCompleted` Event Contract — Baseline

**Document:** DaengtisiaMS Event Contract Baseline
**Step:** Step 2 — Persona and Pilot Use Cases
**Status:** STEP 2 DOCUMENTATION BASELINE — APPLICATION IMPLEMENTATION NOT STARTED
**Canonical source:** Master Source v2.2.0, PRD v1.1.0, Persona v1.0.0
**Timezone:** Asia/Makassar

---

## 0. Nature of this document

This is an **unimplemented baseline contract** — a design agreement for the `VisitCompleted`
event that DaengtisiaMS is intended to emit into Aish Agentic AI. No endpoint, webhook receiver,
queue consumer, database table, or credential described here exists yet. Nothing in this document
may be read as a claim that an integration is live, deployed, or runtime-verified.

- Canonical Step 2 source: [`../product/PERSONA_AND_PILOT_USE_CASES.md`](../product/PERSONA_AND_PILOT_USE_CASES.md) §7 (invitation/eligibility), §8 (minimum data), §4.10 (System Persona — DaengtisiaMS), UC-P0-03.
- Event model: [`../architecture/EVENT_CATALOG.md`](../architecture/EVENT_CATALOG.md) (Master Source §35).
- Rule authority: [`../../.claude/rules/08-architecture-and-event-workflows.md`](../../.claude/rules/08-architecture-and-event-workflows.md) (architecture/events), [`../../.claude/rules/04-security-privacy-and-secrets.md`](../../.claude/rules/04-security-privacy-and-secrets.md) (security/PII), [`../../.claude/rules/03-multi-tenant-and-branch-isolation.md`](../../.claude/rules/03-multi-tenant-and-branch-isolation.md) (tenant/branch), [`../../.claude/rules/07-data-governance-and-audit.md`](../../.claude/rules/07-data-governance-and-audit.md) (audit).
- Canonical map: Master Source §35 (canonical events), §39 (integration/API rules), §36 (data model), §43/§44 (security), §16.2 (equal review access, referenced downstream).

Truthful status vocabulary (`CLAUDE.md` §5) applies. The canonical event name is
`VisitCompleted` (an alias of `TransactionCompleted` / `ServiceCompleted` in the generic core;
Master Source §35). The clinical term is confined to the DaengtisiaMS integration boundary and
MUST NOT narrow the generic core domain (Persona §17 note; Rule 01).

---

## 1. Purpose and trigger

The `VisitCompleted` event is the **primary pilot trigger** for the survey-invitation workflow.
When a visit/service at the pilot branch (Daengtisia Pusat) reaches `completed` status,
DaengtisiaMS is intended to emit exactly one authenticated event to Aish Agentic AI, which then
evaluates eligibility (Persona §7.1) and schedules an invitation (Persona §7.2, UC-P0-04).

Downstream (per Event Catalog): campaign engine selects survey → `SurveyInvitationCreated` →
`SurveyInvitationSent`.

---

## 2. Integration modes (shown truthfully)

Per Persona §7.3, three intake modes exist with a strict truthfulness rule. The system MUST NOT
present a fallback as real-time integration success.

| Mode | Description | Truthful surface requirement |
|------|-------------|------------------------------|
| **Target (primary)** | Signed and authenticated API/webhook from DaengtisiaMS | Labelled as live integration only after provider verification |
| **Controlled fallback** | CSV / manual import containing only minimum pilot fields | Labelled `Manual import` in analytics and audit; never shown as real-time integration |
| **On-site fallback** | QR survey recording branch/campaign attribution without exposing customer identifier | Labelled `On-site QR`; no per-customer identity captured |

All three MUST appear distinctly in analytics and audit (Persona §7.3). The intake source MUST be
recorded on every resulting invitation so reconciliation can distinguish integrated from manual origin.

---

## 3. Authentication and transport (baseline contract)

- Transport MUST be TLS; plaintext is prohibited (Master Source §43; Rule 04).
- The webhook/API MUST be **authenticated server-side**. Baseline mechanism: an HMAC-SHA256
  signature over the raw request body using a per-tenant shared secret delivered in a signature
  header, plus a timestamp header. Bearer/API-key or mTLS MAY substitute or supplement, subject to
  a Step 3 ADR; the chosen method is an open decision (Persona §20 item 5).
- **Replay protection** MUST be enforced: reject requests whose timestamp is outside an accepted
  skew window (baseline ±5 minutes) and reject a previously seen `event_id` (see §5 idempotency).
- Secrets/tokens MUST be referenced from a secure secret store via environment variables and MUST
  NOT be committed (Rule 04). This document contains no real secrets.
- OAuth `state` validation applies to the Google surface, not this event surface; see
  [`google/OAUTH_AND_TOKEN_SECURITY.md`](google/OAUTH_AND_TOKEN_SECURITY.md).

---

## 4. Payload — minimum fields only (data minimization)

Payload carries **only** the minimum eligible fields (Persona §8.1). Field names below are baseline
proposals, not an implemented schema.

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `event_id` | string (UUID/ULID) | Yes | Globally unique per business event; idempotency anchor |
| `event_type` | string | Yes | Fixed value `VisitCompleted` |
| `event_time` | RFC 3339 timestamp | Yes | Service-completion time; interpreted in Asia/Makassar for windowing |
| `tenant_ref` | string | Yes | Maps to Aish `tenant_id` via configured mapping (never a raw internal PK trusted blindly) |
| `branch_ref` | string | Yes | Maps to Aish `branch_id`; MUST resolve to the pilot branch |
| `customer_ref` | string (external ref or pseudonymous ID) | Yes | External reference / pseudonymous customer ID only (Persona §8.1) |
| `service_category` | string (generic code) | Optional | Generic service category code only — NOT a diagnosis or procedure name |
| `contact_channel_hint` | enum | Optional | e.g. `whatsapp`/`email` availability flag; no raw destination required in event |
| `guardian_flag` | boolean | Optional | Signals minor → guardian/contact routing (Persona §7.1) |
| `idempotency_key` | string | Yes | Equals or derives from `event_id`; see §5 |

`tenant_ref`/`branch_ref` are **claims** that MUST be validated against configured mappings; the
receiver MUST NOT trust tenant/branch scope from the payload alone (Rule 03).

---

## 5. Idempotency and exactly-once semantics

- Intake MUST be **idempotent** with exactly-once effect (Persona UC-P0-03; Master Source §39).
- The receiver MUST persist processed `idempotency_key`/`event_id` values and MUST treat a repeat
  as a no-op that returns the original acknowledgement — no duplicate invitation, ticket, or
  downstream side-effect (Persona §14.1 hard gate: no duplicate action from retry).
- Retries from DaengtisiaMS MUST be safe: the same `event_id` retried any number of times yields
  at most one invitation.

---

## 6. Tenant/branch mapping and validation

- `tenant_ref` → `tenant_id` and `branch_ref` → `branch_id` MUST resolve through explicit,
  audited mapping configuration owned by the Pilot Coordinator (Persona §4.3).
- An event whose branch does not resolve to an active pilot branch MUST be rejected/quarantined,
  never silently attributed to another tenant/branch (Rule 03, zero cross-tenant exposure).
- Validation rules (reject on failure, with audited reason code):
  - Missing/blank required field.
  - `event_type` ≠ `VisitCompleted`.
  - Unresolvable or inactive `tenant_ref`/`branch_ref`.
  - Signature/timestamp/replay failure (§3).
  - Malformed timestamp or `event_time` implausibly in the future beyond skew.
  - Presence of any prohibited field (§7).

---

## 7. Prohibited-field rejection (healthcare privacy)

Per Persona §8.2/§4.10 and the authoritative union in Master Source §67.5 / Rule 18 /
`../security/PILOT_DATA_BOUNDARY.md` §3, the event MUST NOT carry any of: diagnosis, clinical notes,
medical record number, prescription/medication details, odontogram, clinical photos/scans,
treatment-plan narrative, **treatment history**, insurance details, payment-card/bank data, or
unredacted internal incident notes.

- If any prohibited field (by known key or by a configured detector) is present, the receiver MUST
  **reject or strip-and-quarantine** the event, record a `prohibited_field_detected` security/audit
  event, and MUST NOT forward the prohibited content to storage, analytics, the AI provider, or any
  downstream consumer (Rule 04; Rule 05 untrusted-input handling).
- This is a **hard gate** (Persona §14.1): no PII/medical leakage. Acceptance test coverage:
  prohibited-field rejection (see [`../testing/PILOT_ACCEPTANCE_TEST_CATALOG.md`](../testing/PILOT_ACCEPTANCE_TEST_CATALOG.md)).

---

## 8. Acknowledgement, durability, retry, and failure handling

- The System Persona expects a **durable acknowledgement** (Persona §4.10). Baseline: accept →
  persist to a durable store (or queue) within tenant context → return a success acknowledgement
  only after durable capture; a `2xx` MUST NOT be returned for an event that was not durably stored.
- Heavy/downstream work (eligibility, scheduling) MUST run on the queue with tenant context
  (Rule 08; Rule 03), not inline in the request path.
- On transient receiver failure, DaengtisiaMS SHOULD retry with backoff; idempotency (§5) makes
  retries safe. Persistent failures MUST surface on integration health (truthful state), never as
  a false success (Persona §14.1; Master Source §53).
- A rejected event (validation/prohibited-field/auth) MUST return a truthful non-success response
  and be recorded; it MUST NOT create an invitation.

---

## 9. Emitted / audited events

Intake participates in the canonical event flow (Event Catalog / Master Source §35):

- Successful intake → internal `VisitCompleted` recorded → campaign engine → `SurveyInvitationCreated`.
- Every intake outcome (accepted, duplicate no-op, rejected, quarantined) MUST be **audited** with
  timestamp, tenant/branch, `event_id`, source mode, and reason code (Rule 07; audit history is
  non-deletable). Audit entries MUST NOT contain prohibited fields or raw secrets.

---

## 10. CSV/manual and QR fallbacks (contract detail)

- **CSV/manual import:** accepts only the minimum pilot fields (§4). Each imported row MUST carry
  the same validation, prohibited-field rejection, tenant/branch mapping, and audit as the API path,
  and MUST be tagged `Manual import` on every downstream surface. It MUST NOT be represented as a
  real-time or authenticated integration (Persona §7.3).
- **On-site QR:** records branch/campaign attribution only; MUST NOT capture or expose a customer
  identifier (Persona §7.3). Resulting responses are attributed to branch/campaign, not to an
  integrated customer record.

---

## 11. Baseline acceptance expectations (for Step 3 / pilot readiness)

These map to UC-P0-03 acceptance (Persona §9.1) and are validated by planned tests, not yet run:

1. Authenticated intake with signature + replay protection.
2. Exactly-once effect under retry (no duplicate invitation).
3. Correct tenant/branch mapping with zero cross-tenant attribution.
4. Full validation and audited reason codes on rejection.
5. Prohibited-field rejection with no medical data reaching storage/AI/public.
6. Durable acknowledgement semantics and truthful failure/health states.
7. Truthful labelling of CSV/manual and QR fallbacks.

Open dependency: DaengtisiaMS webhook/API contract and authentication method are unresolved
(Persona §20 item 5) and require a Step 3 ADR under `docs/architecture/adr/` before implementation.

**Status:** Baseline contract documented. Implementation NOT STARTED. No integration is live.
