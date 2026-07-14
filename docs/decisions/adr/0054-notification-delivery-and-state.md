# ADR 0054 — Notification Delivery and State

- **Status:** Accepted (2026-07-14, Asia/Makassar) — SPRINT-SF-05 Notification foundation; delivery/state model IN PROGRESS toward GO, business/module features NOT STARTED
- **Owner:** Principal Architect / Security & Privacy Lead
- **Rule:** `.claude/rules/31`, `.claude/rules/03` · **Canonical:** Master Source §72; §40, §51, §53; PRD v1.3.0 §16; rules 31, 03, 04, 10, 11

## Context
The SaaS core (Step 6) established fail-closed tenant context, membership, RBAC, audit, and isolation. Platform
features now need to notify users (security events, membership/invitation changes, tenant status changes) without
leaking across tenants, without lying about delivery, and without depending on an AI or a business module. A naive
notifier that fans a "notification" out from any code path, marks it "sent" on enqueue, or re-sends on every retry
would (a) risk cross-tenant delivery, (b) show a false success state, and (c) duplicate external side effects. The
foundation must be a single, tenant-safe, truthful, idempotent delivery substrate with a bounded channel set —
in-app and email only — leaving richer channels (WhatsApp/SMS/Slack/Teams/push/webhook) as later, separately
governed work.

## Decision
- **One dispatcher, tenant-safe.** All notifications are enqueued through a single dispatcher; ad-hoc mail/notify
  calls are not permitted. The recipient's active membership of the current tenant is verified before dispatch; a
  tenant **cannot** notify another tenant's members. Only two channels exist: **in-app** and **email**.
- **Exactly-once logical delivery.** A logical event produces exactly one delivery per recipient per channel,
  keyed by a **globally-unique dedup key**. Duplicate dispatch and retries are a no-op on a terminal delivery;
  retries are **bounded** and a failed delivery carries an explicit state and a **sanitized** failure code. Mail is
  sent only through the mail channel adapter (`App\Mail\FoundationNotificationMail`, queued).
- **Truthful state machine.** Delivery state is one of `pending | queued | sending | sent | failed | cancelled |
  suppressed`. `queued` is **not** `sent`; `sent` means the channel adapter accepted the message (for email:
  accepted by the mail transport) and is **never** a proven end-user receipt.
- **Preferences and critical bypass.** Tenant/user preferences and **timezone-aware** quiet hours are honored;
  critical security notifications **cannot** be silenced by a preference. Suppressed deliveries are recorded
  truthfully as `suppressed`.
- **Inbox ownership.** The in-app inbox is scoped to the current tenant and acting recipient; mark-as-read
  **re-verifies** ownership (no recipient-swap / delivery IDOR).
- **Minimal content.** Notification content and delivery records minimize PII and **must not** contain tokens,
  secrets, passwords, or customer/medical content.

## Alternatives
- **Ad-hoc `Notification::send()` from any caller** — rejected: no single choke point for tenant verification,
  dedup, or truthful state; duplicates and cross-tenant leakage become likely.
- **Mark "sent" on enqueue** — rejected: violates truthful states (§53); an enqueued message is `queued`, not
  delivered.
- **Prove end-user receipt as "sent"** — rejected: read/receipt proof is out of scope and provider-specific;
  claiming it would be a false success.
- **Ship all channels now (WhatsApp/SMS/push/webhook)** — deferred: each needs its own provider, consent, rate,
  and abuse governance; foundation ships in-app + email only.
- **Per-tenant dedup key** — rejected in favor of a globally-unique key so a retry across workers cannot create a
  second logical delivery.

## Consequences
Every notification flows through one tenant-safe, idempotent path with a truthful, auditable state; retries never
duplicate a delivery or a side effect; critical security messages are never silently dropped; later channels layer
on the same dispatcher without re-implementing tenant safety or dedup.

## Impacts
- **Security:** membership verification before dispatch + critical-bypass prevents both cross-tenant delivery and
  silent suppression of security alerts; failure codes are sanitized.
- **Privacy:** content and delivery metadata minimize PII and exclude tokens/secrets/medical content.
- **Tenant isolation:** dispatch, inbox, and preferences are tenant-scoped; no cross-tenant notify or inbox read.
- **Database:** adds notification, delivery, and preference tables with a globally-unique dedup key; no business
  module tables.
- **Operational:** bounded retry + explicit failed state + sanitized failure code give a truthful, debuggable
  delivery log and a kill-switch-compatible surface.
- **Cost:** one queued job per delivery; dedup avoids duplicate sends; negligible vs. correctness/isolation gains.

## Verification / fitness function
`tests/Feature/Notifications/*` and `tests/Feature/Security/Sf05CrossTenantMatrixTest.php` assert: cross-tenant
notify blocked; one delivery per recipient per channel (dedup); retry/duplicate dispatch is a no-op on terminal
state; `queued` ≠ `sent`; quiet-hours honored and timezone-aware; critical notifications bypass preferences;
suppressed recorded truthfully; inbox mark-read re-verifies ownership. AFR-155, AFR-156, AFR-157, AFR-158, AFR-159;
SC-22, SC-23, SC-24, SC-25, SC-26.

## Related
Requirement: Master Source §72; §40, §51, §53; PRD v1.3.0 §16. Application rules: AFR-155..AFR-159, AFR-169.
Rules: 31, 03, 04, 10, 11. ADRs: 0011, 0012, 0015, 0016, 0029, 0051, 0052, 0053.

## Evidence
`app/Notifications/*`, `app/Jobs/Notifications/*`, `app/Services/Notifications/*`, `app/Mail/FoundationNotificationMail.php`,
`app/Models/{Notification,NotificationDelivery,NotificationPreference}*` (forthcoming under SPRINT-SF-05);
`docs/governance/foundation-coverage-matrix.md`; `docs/evidence/sprint-sf-05/` (forthcoming).

## Non-claims
This ADR does not create any business/feature module (`app/Modules/*` remains **NOT STARTED**), does not add a
production email provider, and does not add WhatsApp/SMS/Slack/Teams/push/webhook channels. It does not claim proven
end-user receipt, deployment, pilot, or production readiness, and does not assert the SPRINT-SF-05 release is merged,
tagged, CI-green, or clean-checkout-verified — those remain **PLANNED** until evidenced.

## Rollback
Tenant-safe dispatch, exactly-once logical delivery, truthful delivery states, critical-bypass, and inbox-ownership
re-verification are permanent guarantees; loosening any of them requires an owner-approved Master Source update.
Additive changes (new channel adapters, new preference keys) are recorded decisions under this ADR.
