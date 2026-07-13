# Integration Readiness — Aish Agentic AI

Canonical: Master Source §38, §39. Rule: `.claude/rules/08`, `06`. PRD §18.

## Integration priorities (Master Source §39)
1. Google Business Profile · 2. WhatsApp Business Platform · 3. Email provider · 4. Public API · 5. Webhook ·
6. POS · 7. CRM · 8. ERP · 9. Clinic Management System · 10. Hotel Management System · 11. E-commerce ·
12. Slack · 13. Microsoft Teams · 14. SMS provider. **MVP priority (PRD §18.1):** Google, WhatsApp link, email, public API/webhook.

## Public API rules (Master Source §39; PRD §18.2)
API key/OAuth · tenant scoping · rate limit · idempotency · validation · audit · pagination · versioning ·
webhook signature · retry-safe design · consistent error response · **no sensitive data in logs**.
Minimum endpoints: `POST /api/v1/customers`, `/transactions`, `/service-events`, `/survey-invitations`;
`GET /api/v1/feedback`, `/reviews`, `/recovery-tickets`; `POST /api/v1/webhooks`.

## Google review sync (Master Source §38)
Periodic + incremental sync, idempotency, retry, rate-limit handling, sync cursor, last-synced timestamp,
error logs, reauthorization, event notification when available.

## Readiness gate (Master Source §54 integration gate)
OAuth production-ready · Google sync stable · token refresh stable · rate limit handled · API failure
handled · publish reply audited · reauthorization tested — all with evidence before a product release GO.
Google API approval and production policy re-verification are OPEN (OD-8) and a known risk (PRD §25.1).

**Status:** integration readiness documented. Implementation NOT STARTED.
