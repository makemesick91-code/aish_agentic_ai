# SPRINT-SF-05 — Notification, Subscription, and Platform Admin Skeletons — Tag Verification

## GO tag
- Name: `aish-agentic-ai-sprint-sf-05-notification-subscription-platform-admin-skeletons-v1.0.0-go`
- Type: **annotated**
- Date: 2026-07-14 (Asia/Makassar)
- Attests: notification/subscription/platform-admin **foundation readiness only** — NOT deployment, pilot, or production.

## Exact-match verification

| Location | Tag object | Peeled commit |
|----------|------------|---------------|
| Local | `08451100d9a72b23665ba7d65ba2b0c1c8b1e5b7` | `ca0bea6ec56a0d796699f8db265aa2d224d0b613` |
| Remote (`origin`) | `08451100d9a72b23665ba7d65ba2b0c1c8b1e5b7` | `ca0bea6ec56a0d796699f8db265aa2d224d0b613` |
| `main` HEAD | — | `ca0bea6ec56a0d796699f8db265aa2d224d0b613` |

Local tag object == remote tag object; local peeled == remote peeled == `main` HEAD. The tag is immutable and was never moved.

## Release lineage
- Code PR: **#17** (`feature/sprint-sf-05-notification-subscription-platform-admin-skeletons`)
- Final code head: `899e888875e25112e43f27b32f82cb4461dfb157`
- Authoritative Full CI: run `29326645691` on `899e888` — **success**; `pr-ci / Required Gate` **green**.
- Prior Full CI: run `29326179423` on `42f47bb` — **failure** (Vite-manifest ordering); corrected in `899e888` and re-run green (truthful CI reporting; rule 28).
- Merge commit: `ca0bea6ec56a0d796699f8db265aa2d224d0b613` (merge of PR #17 into `main`).
- Main post-merge: `main-post-merge` lightweight integrity **success** on `ca0bea6`; **no full CI ran on `main`**; ruleset `18890571` remained enforced.
- GitHub Release: `Aish Agentic AI — SPRINT-SF-05 v1.0.0 GO`, associated with the GO tag.

## Clean-checkout verification (merged SHA `ca0bea6`)
`scripts/runtime/verify-sf-05.sh` against real PostgreSQL 17 + Redis 7 — **PASS**:
migrate:fresh → `aish:verify-saas-core` (Step 6 regression) → `aish:verify-sf-05` (SF-05 real-infra) → no secret values in logs → hermetic `php artisan test`. Evidence: `docs/evidence/sprint-sf-05/runtime/`.

## Scope
This tag attests the readiness of the notification, subscription/entitlement, and platform-admin **foundations** only.
It is **not** a claim that the application is deployed, pilot-ready, or production-ready, and no domain is owned or
infrastructure provisioned. Business/module implementation, payment, AI, and Google integrations remain **NOT STARTED**.

## Immutability
`git push --force`, `git tag -f`, tag deletion/moving, and history rewriting were not used. Prior GO tags were not moved.
