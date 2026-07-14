# SPRINT-SF-05 — Evidence Index

Foundation readiness evidence for SPRINT-SF-05 (Notification, Subscription, and Platform Admin Skeletons).
This attests **foundation readiness only** — not deployment, pilot, or production.

## Release identifiers
- Code PR: **#17** · Final code head: `899e888` · Merge commit: `ca0bea6`
- Authoritative Full CI: run `29326645691` (head `899e888`) — success; Required Gate green.
- Prior Full CI: run `29326179423` (head `42f47bb`) — failure (Vite ordering); corrected and re-run green.
- Main post-merge: lightweight integrity success on `ca0bea6`; no full CI on `main`.
- GO tag: `aish-agentic-ai-sprint-sf-05-notification-subscription-platform-admin-skeletons-v1.0.0-go`
  (object `08451100…`, peeled `ca0bea6…`; local == remote == `main`).
- GitHub Release: `Aish Agentic AI — SPRINT-SF-05 v1.0.0 GO`.

## Runtime verification (clean checkout, merged SHA `ca0bea6`)
`scripts/runtime/verify-sf-05.sh` against real PostgreSQL 17 + Redis 7 — **PASS**. Steps and results (raw logs are
written locally to `docs/evidence/sprint-sf-05/runtime/` — git-ignored, like the Step 5/6 runtime logs):
- `migrate:fresh` — PASS
- `aish:verify-saas-core` (Step 6 tenant-isolation regression, real infra) — PASS
- `aish:verify-sf-05` (notification delivery over real Redis queue, entitlement fail-closed, usage idempotency,
  security-state precedence, platform/tenant plane separation, last-super-admin protection, no impersonation) — PASS
- no secret values in verification logs — PASS
- hermetic `php artisan test` — PASS (182 passed)

## Local gates
- 182 tests (813 assertions); Pint clean; PHPStan level 6 no errors; composer + npm audits clean.
- `scripts/docs/validate.sh`: ALL GATES PASSED.

## Related documents
- Release report: [`../../release/SPRINT_SF_05_RELEASE_REPORT.md`](../../release/SPRINT_SF_05_RELEASE_REPORT.md)
- Tag verification: [`../../release/SPRINT_SF_05_TAG_VERIFICATION.md`](../../release/SPRINT_SF_05_TAG_VERIFICATION.md)
- Rule: [`.claude/rules/31-notification-subscription-platform-admin-foundation.md`](../../../.claude/rules/31-notification-subscription-platform-admin-foundation.md)
