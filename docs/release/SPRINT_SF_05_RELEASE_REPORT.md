# SPRINT-SF-05 — Notification, Subscription, and Platform Admin Skeletons — Release Report

**Status:** MERGED and GO TAGGED. **Foundation readiness only** — not deployed, not pilot-ready, not production-ready.

## Summary
SPRINT-SF-05 adds three platform-core foundations on top of the Step 6 SaaS core, without regressing it:
a tenant-safe **notification** foundation, a **subscription & entitlement** skeleton, and a separate least-privilege
**platform-admin** plane. Master Source bumped to **v2.8.0** (§72); PRD unchanged at **v1.3.0**.

## What was delivered
- **Notification foundation** — single dispatcher (membership-verified recipients; no cross-tenant notify), one delivery
  per `(recipient, channel)` with a globally-unique dedup key (retry/duplicate → one logical delivery), truthful state
  machine (`pending → queued → sending → sent | failed | cancelled | suppressed`; `sent` = accepted-by-transport),
  bounded idempotent retry, in-app + email only, timezone-aware preferences/quiet hours, critical-notification bypass,
  ownership-checked in-app inbox, queued `FoundationNotificationMail`.
- **Subscription & entitlement skeleton** — plan `(code, version)` catalog (draft/active/retired), typed allowlisted
  entitlements, guarded subscription state machine, one authoritative **fail-closed** `EntitlementResolver`, idempotent
  tenant-scoped usage metering, idempotent `aish:subscription-reconcile`. Commercial state ≠ payment; security
  suspension takes precedence.
- **Platform-admin plane** (`/platform-admin/*`) — platform roles separate from tenant roles (no `Gate::before` bypass),
  per-permission authorization, secure `aish:platform-admin-provision`, reason-required audited tenant
  suspend/reactivate/mark-deletion-pending, append-only support notes, truthful metrics; impersonation prohibited;
  self-escalation blocked; last-Super-Admin protected.

## Governance
- Master Source **v2.8.0** (§72) + `MASTER SOURCE UPDATE` block + v2.8.0 source snapshot & checksum.
- ADRs **0054** (notification delivery & state), **0055** (subscription & entitlement model), **0056** (platform-admin
  trust boundary); **AFR-155..170**; Claude **rule 31**; foundation coverage matrix rows (100% coverage).
- `CLAUDE.md`, `AGENTS.md`, `VERSION_MATRIX`, `CHANGELOG`, and status docs synced.

## Quality gates (all passed)
- **182 tests** (813 assertions): notification, subscription, platform, cross-tenant attack matrix, audit, migration, commands, architecture fitness.
- **Pint** clean; **PHPStan** (level 6, Larastan) no errors; **composer audit** + **npm audit** clean.
- `scripts/docs/validate.sh`: **ALL GATES PASSED** (version consistency, rule frontmatter, ADR, coverage, links, secret scan, AGENTS chain, Graphify).
- **Authoritative Full CI** green on `899e888` (Required Gate); `backend-runtime-ci` on real PostgreSQL 17 + Redis 7.
- **Clean-checkout verification** on merged SHA `ca0bea6` — PASS (see `SPRINT_SF_05_TAG_VERIFICATION.md`).

## Security findings fixed
- Postgres `FOR UPDATE`+aggregate bug in last-Super-Admin protection (masked by sqlite; caught by real-infra verification) — fixed to lock rows, not the aggregate.
- Vite-manifest ordering caused the first Full CI to fail; fixed by disabling Vite in the test harness (`withoutVite`) — corrective commit `899e888`, re-run green (truthful CI reporting).

## Explicitly out of scope (NOT STARTED)
Survey/CSAT/NPS/CES, feedback, recovery, Google OAuth/reviews, AI/RAG, payment gateway/invoicing/tax/dunning,
WhatsApp/SMS/Slack/Teams/push, production email provider, deployment/cloud/DNS, MFA/SSO, impersonation, advanced
support console, tenant business-data browsing.

## Next recommended step
Proceed to the next SaaS Foundation sprint (SPRINT-SF-06 per ADR 0039 sequence) only after this release is confirmed
merged and GO-tagged. Do not move any prior GO tag.
