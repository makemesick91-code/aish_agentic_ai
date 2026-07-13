# API and Webhook Standards — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §39 · PRD v1.2.0 §18.2 · **Rules:** `.claude/rules/03`, `04`, `08`, `20` ·
**ADR:** [0017](../decisions/adr/0017-public-api-and-webhook-contracts.md).

## 1. Public API baseline
- Prefix **`/api/v1`**; versioned; deprecation is announced, dual-run, then retired (never silently broken).
- Authentication: API key / OAuth (Sanctum). Every request is **tenant-scoped** (and branch-scoped where
  relevant); scope derives from the credential, never from a client-supplied `tenant_id`.
- **Idempotency-Key** header required for unsafe (POST/PUT/PATCH/DELETE) operations.
- Pagination is cursor/opaque; rate limiting per tenant + credential; consistent error envelope.
- **Request-Id** and **Correlation-Id** on every request/response; audit for state-changing calls; **no
  sensitive data in logs** (redaction, ADR 0024).

## 2. Error envelope
```json
{ "error": { "code": "string.machine_readable", "message": "human readable",
  "request_id": "…", "details": [] } }
```
Errors never leak internal identifiers, stack traces, or another tenant's existence (uniform 404 vs 403 to
resist IDOR enumeration).

## 3. Webhooks (inbound & outbound)
Owned by the Integration module. Every webhook is **signed, versioned, replay-protected, retry-safe, idempotent,
audited, and tenant-scoped**. Inbound payloads are **untrusted input**: they never determine tool/behaviour
(prompt-injection / tool-abuse defense, `.claude/rules/04`, `05`).

Webhook delivery states: `Pending → Delivering → Delivered | Retry Scheduled | Failed | Dead Lettered |
Cancelled`. Signature verification precedes any processing; failures are logged as security events.

## 4. External-call truthfulness
An external action is reported successful **only after** the provider response is verified (ADR 0016;
`.claude/rules/10`). A mock/unavailable integration is labelled truthfully and may be `BLOCKED`, never a fake
success.

## 5. Standards summary (checklist)
Auth · tenant scope · branch scope · pagination · error envelope · rate limit · idempotency key · versioning ·
deprecation · request id · correlation id · audit · sensitive-log redaction · webhook signature · replay
protection · retry safety.

## 6. Truthful status
No route, controller, or webhook endpoint exists or runs in Step 3. This is the contract implementation must meet.
