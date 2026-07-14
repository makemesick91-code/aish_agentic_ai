# SF-05 Independent Security Review — Evidence

**Status:** COMPLETE — ASSURANCE PASS (no CRITICAL/HIGH/MEDIUM findings)
**Purpose:** Satisfy the deferred independent security-reviewer pass for SPRINT-SF-05 (Notification,
Subscription, and Platform Admin Skeletons), which was outstanding due to a prior session usage limit. This
review is a **precondition gate for Step 7** (Survey & CSAT Foundation) per the Step 7 execution mandate §4.

## Method

- **Reviewer:** Independent security-privacy reviewer subagent (read-only; **not** the SF-05 implementer).
- **Reviewed range:** `75cd8c2..ca0bea6` (SF-05 implementation: `ad68a10` feat, `1808fa0` test, `6469a61` ci,
  `42f47bb` docs, `899e888` fix) — the merged, GO-tagged SF-05 release
  (`aish-agentic-ai-sprint-sf-05-notification-subscription-platform-admin-skeletons-v1.0.0-go`, tag object
  `08451100`, peeled `ca0bea6`).
- **Technique:** `git diff 75cd8c2..ca0bea6` plus direct source reading of `app/Notifications`,
  `app/Jobs/Notifications`, `app/Services/Notifications`, `app/Mail`, `app/Subscriptions`, `app/Platform`,
  `app/Http/Controllers/Platform`, `app/Http/Controllers/Tenancy/NotificationInboxController.php`,
  `app/Policies/NotificationDeliveryPolicy.php`, `app/Audit/AuditRecorder.php`, related models, providers,
  middleware, routes, and migrations.
- **Scope (14 attack classes):** platform/tenant plane separation; platform privilege escalation;
  self-escalation; last-Platform-Super-Admin concurrency; notification recipient isolation; notification
  dedup races; notification retry amplification; subscription transition integrity; entitlement bypass;
  usage metering replay; security-suspension precedence; audit redaction; platform→tenant data access;
  impersonation prohibition; queue context leakage.

## Verdict

**No CRITICAL or HIGH findings. No MEDIUM findings.** All rule-31 invariants (plane separation, no
self-escalation, only-Super-Admin-grants-Super-Admin, race-safe last-super-admin protection, recipient
isolation, globally-unique dedup, entitlement fail-closed, security-suspension precedence, append-only
redacted audit, no impersonation, queue tenant handling) are correctly implemented. No committed secret,
token, private key, or `.env` content observed in the reviewed surface.

## Findings (LOW / INFO only — recorded to backlog per §4 handling)

| # | Severity | Location | Summary | Disposition |
|---|----------|----------|---------|-------------|
| 1 | LOW (unproven) | `app/Jobs/Notifications/DeliverNotificationJob.php:58` | `Log::withContext(['tenant_id'])` mutates shared logger context; a stale `tenant_id` could stamp a subsequent different-type job's log lines if the worker does not flush shared context between jobs. Job overwrites value before work, so window is to a different job type only. | Backlog: prefer per-message context or clear in `finally`; confirm worker flush behavior. No cross-tenant *data* leak. |
| 2 | LOW | `app/Platform/PlatformRoleService.php:54-82` (`remove`) | `remove()` does not enforce "only a Super Admin may remove a Super Admin" at the service layer (the symmetric guard exists in `assign`). Currently unreachable — the only caller requires `platform.users.manage`, a Super-Admin-only permission. Defense-in-depth gap, not exploitable. | Backlog: add super-admin-actor check in `remove()` when `$role === SuperAdmin`. |
| 3 | LOW (unproven) | `app/Subscriptions/SubscriptionService.php:136-191` (`transition`) | Reads `$from` from in-memory model without `lockForUpdate`; two concurrent manual transitions from the same source could each pass the state check and both append a `subscription_event` row. Owner notifications protected by dedup; scheduled reconcile protected by `withoutOverlapping()->onOneServer()`. Realistic window: concurrent manual invocation only. | Backlog: wrap read+transition in a row lock; re-read status under lock. |
| 4 | INFO | `app/Services/Notifications/NotificationDispatcher.php:159-175` | No defensive PII/secret redaction layer on caller-supplied `subject`/`body`/`data`; all current callers pass safe minimized content (rule 31 places obligation on callers). | Backlog (optional): allowlist/length guard on `data` keys. |
| 5 | INFO | `app/Http/Controllers/Platform/SupportNoteController.php:27` | Support-note free text has no automated customer/medical content screening (rule 31 makes this operator responsibility). | Noted; no code change required for the foundation. |

## Residual risk

**Acceptable.** No exploitable path. The three LOW items are defense-in-depth hardening (one currently
unreachable, two concurrency/log-hygiene edge cases under narrow manual-invocation windows). They are tracked
for a future hardening sprint and do **not** block Step 7.

## Immutability note

The SF-05 GO tag remains the historical release attestation and is **not** moved by this review. No SF-05
code was changed as part of this assurance pass; the LOW/INFO items are backlog follow-ups, not release
blockers under §4 (which halts only on CRITICAL/HIGH, and permits proceed-with-mitigation on MEDIUM — none of
which were found).

## Final assurance status

`SF-05 INDEPENDENT SECURITY REVIEW — PASS (no CRITICAL/HIGH/MEDIUM). STEP 7 CLEARED TO PROCEED.`
