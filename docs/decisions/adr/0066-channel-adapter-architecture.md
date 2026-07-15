# ADR 0066 — Provider-Neutral Channel Adapter Architecture

- **Status:** Accepted (2026-07-15, Asia/Makassar) — Step 9 architecture LOCK; omnichannel implementation NOT STARTED (Wave 2)
- **Owner:** Principal Architect / Conversation & Channel Adapter
- **Rule:** `.claude/rules/34`, `.claude/rules/04`, `.claude/rules/06`, `.claude/rules/17` · **Canonical:** Master Source §75, §34, §38, §39; PRD v1.3.0; rules 34, 03, 04, 06, 17, 18, 31

## Context
Future omnichannel (WhatsApp, email, web chat, Instagram, Messenger, SMS, voice) must not couple the domain to any
single provider, must never leak across tenants, and must not let one provider's failure break Feedback Operations or
Customer Recovery. Step 5–8 provide an outbound notification foundation (SF-05) but no inbound/conversation model.

## Decision
- Adopt the provider-neutral contract in `docs/architecture/experience-os/CHANNEL_ADAPTER_ARCHITECTURE.md`: tenant-owned
  encrypted `channel_connections`; a normalized provider-independent `conversations`/`messages` model; signature-verified
  webhooks with replay protection; inbound dedup by `(tenant_id, provider, provider_message_id)`; outbound idempotency
  keys; truthful delivery/read/failure/moderation/unknown states; bounded retry + DLQ + reconciliation; private
  content-MIME-validated attachments; per-message cost metering via `app/Models/UsageRecord.php`; quiet-hours + consent
  + anti-spam caps (rule 17); tenant/branch routing; and a per-provider **circuit breaker** that falls back to manual.
- **A single provider failure must not break core Feedback Operations or Customer Recovery.**
- Each adapter ships contract tests against a fake provider; **a mock is not integration success** — real integration
  requires provider verification evidence.

## Alternatives
- **Provider-specific code in the domain** — rejected: vendor lock-in and duplicated logic; the adapter normalizes.
- **Trust unsigned webhooks** — rejected: forgery/replay risk; signatures + replay window are mandatory.
- **Show "sent" as delivered** — rejected: violates truthful states; provider ack is not receipt.
- **Fail hard on provider outage** — rejected: would break core operations; circuit breaker + manual fallback required.

## Consequences
Omnichannel can be added provider-neutrally, safely, and truthfully; providers are swappable; core operations survive
provider degradation.

## Impacts
- **Security:** per-tenant encrypted credentials; signature verification; replay protection; sanitized audit.
- **Privacy:** no message bodies/PII/medical in audit; private attachment storage; consent/quiet-hours honored.
- **Tenant isolation:** connections and routing are tenant-scoped; no cross-tenant message routing.
- **Database:** Wave 2 adds additive `channel_connections`, `conversations`, `messages`; none in Step 9.
- **Operational:** bounded retry, DLQ, reconciliation, circuit breaker, provider-health observability.
- **Cost:** per-message cost metered; none in Step 9.

## Verification / fitness function
`scripts/docs/verify-step-9.sh` asserts the adapter design covers credentials, webhooks, provider states, retry,
reconciliation, rate limits, attachments, cost, consent, audit, and degradation. Wave-2 adds per-adapter contract tests
+ isolation tests. AFR-225, AFR-226, AFR-227, AFR-228, AFR-229.

## Related
Requirement: Master Source §75, §34, §38, §39; PRD v1.3.0. Rules: 34, 03, 04, 06, 17, 18, 31. ADRs: 0016, 0054, 0063.

## Evidence
`docs/architecture/experience-os/CHANNEL_ADAPTER_ARCHITECTURE.md`, `app/Services/Notifications/`,
`app/Feedback/FeedbackAttachmentService.php`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-9/`.

## Non-claims
Creates no adapter, conversation model, credential, or webhook endpoint; claims no live provider integration; a mock is
not integration success; does not claim deployment/pilot/production readiness.

## Rollback
Provider-neutral normalization, per-tenant encrypted credentials, signed+replay-protected webhooks, truthful delivery
states, idempotency, circuit-breaker-with-manual-fallback, and no-mock-integration-claim are permanent; changing any
requires a new ADR + owner-approved Master Source update.
