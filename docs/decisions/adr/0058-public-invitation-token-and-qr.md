# ADR 0058 — Public Invitation, Token, and QR Architecture

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 7 Survey & CSAT Foundation; survey capability IN PROGRESS toward GO
- **Owner:** Principal Architect / Application Security Engineer
- **Rule:** `.claude/rules/32`, `.claude/rules/04`, `.claude/rules/03` · **Canonical:** Master Source §47; PRD v1.3.0 §10.5, §10.6 (FR-CAM-*, FR-RES-*); rules 32, 04, 03, 30

## Context
Surveys are answered by **customers**, not tenant members, over an **unauthenticated** public surface (QR at a
clinic, a link in an email). This surface must resolve tenant-owned data without a tenant session yet never become a
cross-tenant hole, an enumeration oracle, or a way to reach draft content or RBAC. Invitation secrets must never be
recoverable from storage. The failure modes to prevent: sequential-id enumeration, plaintext token storage/logging,
token brute-force/replay, double-completion, public reach into draft/preview, and the public plane gaining tenant
application access.

## Decision
- **Opaque public identifiers.** Campaigns and invitations expose an opaque ULID `public_id` in the URL; internal
  sequential ids are never exposed and the tenant is not inferable.
- **Two link types.** (1) A **public campaign link** `/s/c/{public_id}` (+ QR) for anonymous responses — unguessable
  by ULID, revocable by pausing/ending the campaign, no per-response secret. (2) A **unique invitation link**
  `/s/i/{public_id}/{token}` — the token is a 256-bit random secret; only its **SHA-256 hash** is stored
  (`token_hash`, globally unique, hidden from serialization), compared in constant time (`hash_equals`). The
  plaintext exists transiently only to build the delivery link and is **never** persisted, logged, put in the
  session, in audit, or in a delivery record; it is delivered solely inside the emailed link.
- **Single reviewed gateway.** `App\Surveys\PublicSurveyGateway` is the only public entry point. It resolves a
  campaign/invitation cross-tenant via the **allowlisted** `withoutGlobalScope(TenantScope)` bypass (opaque id +
  constant-time token; a miss returns a generic failure — no enumeration), then performs all work under a
  **membership-less tenant context** (no RBAC, no platform access, no authenticated user).
- **One-time, transactional, idempotent responses.** A partial unique index guarantees at most one completed
  response per unique invitation; concurrent double-submit resolves to a single completion. Anonymous campaign links
  may collect many responses (the QR use case). Responses are metered and audited without answer content.
- **QR content.** The QR encodes **only** the opaque public campaign URL — no customer data, tenant secret,
  unprotected id, or health information — rendered as pure-PHP SVG (`bacon/bacon-qr-code`), deterministic, no
  external service.
- **Abuse controls.** Per-token and per-IP submit rate limits, a per-IP view limit, and a hard payload cap.

## Alternatives
- **Sequential ids / predictable slugs** — rejected: enumerable and tenant-inferable.
- **Storing a reusable plaintext token** — rejected: a store/log leak becomes account-independent survey access;
  hash-at-rest + constant-time compare is mandatory.
- **Laravel signed URLs with embedded expiry** — considered; the opaque-id + campaign-state model gives revocation
  without expiry coupling and no secret in the query string. Signed URLs remain a compatible future option.
- **Establishing a full tenant/RBAC context for the public request** — rejected: the public plane must never hold
  tenant application permissions; a membership-less data-scoping context is the minimum required.
- **An external QR service** — rejected: adds a network dependency and data-exfil surface; local SVG only.

## Consequences
A customer can answer via QR or a unique link with no account; a guessed token reveals nothing about tenant
existence; a stored/logged secret cannot be turned into access; a unique invitation completes once; and the public
plane can never read draft content or reach tenant RBAC.

## Impacts
- **Security:** hashed one-time tokens, constant-time compare, no-enumeration failures, allowlisted single-gateway
  scope bypass (arch-test enforced), rate limits + payload caps.
- **Privacy:** only hashed IP/UA metadata is retained; no answer content in logs/audit; QR carries only the URL.
- **Tenant isolation:** public resolution is explicit and single-sourced; the public plane holds no RBAC/platform
  access; writes stamp the resolved tenant.
- **Database:** adds `public_id`/`token_hash`/`idempotency_key` uniqueness on invitations/campaigns and a partial
  unique index for one completed response per invitation.
- **Operational:** truthful invitation states (`created/sent/opened/completed/expired/revoked/delivery_failed`);
  promotes `bacon/bacon-qr-code` (already resolved via Fortify) to a direct dependency (rule 25).
- **Cost:** negligible; local SVG QR, no external service.

## Verification / fitness function
`tests/Feature/Surveys/PublicSurveyFlowTest.php`, `SurveyHttpTest.php`,
`tests/Feature/Security/Sf07CrossTenantMatrixTest.php`, `tests/Architecture/{Sf07BoundariesTest,TenancyBoundariesTest}.php`,
`tests/Feature/Sf07MigrationIntegrityTest.php` assert hashed one-time tokens, generic no-enumeration failures,
one-time completion, public draft inaccessibility, allowlisted single-gateway bypass, QR-URL-only, and rate/payload
limits. AFR-176..AFR-180.

## Related
Requirement: Master Source §47; PRD v1.3.0 §10.5, §10.6. Rules: 32, 04, 03, 30. ADRs: 0011, 0012, 0053, 0057.

## Evidence
`app/Surveys/PublicSurveyGateway.php`, `app/Surveys/SurveyInvitationService.php`,
`app/Http/Controllers/PublicSurvey/*`, `app/Http/Controllers/Tenancy/Survey/SurveyInvitationController.php`,
`app/Providers/AppServiceProvider.php` (rate limiters), `routes/web.php`; `docs/evidence/step-7/`.

## Non-claims
Does not implement WhatsApp/SMS delivery, a production email provider, or Google Review flows (all NOT STARTED); does
not reveal reusable tokens through any API; does not claim deployment/pilot/production readiness.

## Rollback
Hashed one-time tokens, opaque ids, no-enumeration, single-gateway public resolution, one-time completion, and
QR-URL-only are permanent; loosening any requires an owner-approved Master Source update.
