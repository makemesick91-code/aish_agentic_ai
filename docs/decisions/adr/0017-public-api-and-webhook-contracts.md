# ADR 0017 — Public API and Webhook Contracts

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Principal Software Architect
- **Rule:** `.claude/rules/03`, `04`, `08`, `20` (AFR-037..040) · **Canonical:** Master Source v2.3.0 §39; PRD v1.2.0 §18.2

## Context
The API and webhooks are the app's trust boundary with tenants and external systems and must be secure,
versioned, tenant-scoped, idempotent, and truthful.

## Decision
Public API under **`/api/v1`**: credential-derived tenant scope, `Idempotency-Key` on unsafe ops, cursor
pagination, uniform error envelope, per-tenant rate limits, request/correlation ids, audit, redacted logs,
controlled deprecation. Webhooks are **signed, versioned, replay-protected, retry-safe, idempotent, audited,
tenant-scoped**; inbound payloads are untrusted and never determine behaviour. See
[API and Webhook Standards](../../architecture/API_AND_WEBHOOK_STANDARDS.md).

## Alternatives
- **Unversioned API** — rejected: breaks clients on change.
- **Client-supplied tenant id** — rejected: trivial cross-tenant escalation; scope must come from the credential.

## Consequences
Stable, secure external surface; requires idempotency + signature infrastructure (Integration module).

## Impacts
- **Security:** IDOR-resistant (opaque ids, uniform 404/403); signature + replay protection.
- **Privacy:** no sensitive data in logs/errors.
- **Tenant isolation:** scope from credential; FF-TEN-11/12.
- **Database:** idempotency + webhook_events tables.
- **Operational:** deprecation policy; correlation ids for tracing.
- **Cost:** low.

## Verification / fitness function
FF-API-01..04, FF-REL-06. Implementation: version, idempotency, scope-spoofing, signature/replay tests.

## Related
Requirement: Master Source §39; PRD §18.2. Application rule: AFR-037..040. ADRs: 0016, 0021, 0024.

## Evidence
`docs/architecture/API_AND_WEBHOOK_STANDARDS.md`.

## Non-claims
No route, controller, or webhook endpoint exists or runs in Step 3.

## Rollback / supersession
Contract rules are permanent; superseded only by an API ADR + Master Source update.
