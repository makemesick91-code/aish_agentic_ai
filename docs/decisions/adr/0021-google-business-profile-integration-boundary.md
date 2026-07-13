# ADR 0021 — Google Business Profile Integration Boundary

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Integration / Security Architect
- **Rule:** `.claude/rules/06`, `18`, `20` (AFR-051, AFR-052) · **Canonical:** Master Source v2.3.0 §16, §38; PRD v1.2.0 §11.2, §17

## Context
Google Review management is core, policy-sensitive (no gating), privacy-sensitive (no PII/medical), and depends
on an external API whose current policy must be re-verified before production.

## Decision
Isolate Google Business Profile behind the **Integration module** with an adapter and the Reputation module for
domain logic. All review sync and reply publication go through the outbox (provider-verified, idempotent). Every
public reply requires recorded **human approval**; **no gating**; **no PII/medical** disclosure; sensitive cases
routed to a private channel. Mock/unavailable integration is labelled truthfully and may be `BLOCKED`. See
[Google Business Profile Architecture](../../integrations/GOOGLE_BUSINESS_PROFILE_ARCHITECTURE.md).

## Alternatives
- **Direct Google API calls from Reputation** — rejected: couples domain to provider; no single choke point.
- **Auto-publish replies** — rejected: forbidden outside Master Source §16.4 preconditions.

## Consequences
Swappable, testable integration with policy safety enforced at the boundary; requires OAuth token security (ADR 0022).

## Impacts
- **Security:** OAuth tokens encrypted; signature-verified; kill switch.
- **Privacy:** replies never disclose personal/medical/sensitive-transaction data.
- **Tenant isolation:** connections/locations/reviews tenant+branch scoped.
- **Database:** google_connections/locations/reviews/reply_* tables (Reputation).
- **Operational:** Google sync + OAuth health alerts (ADR 0024); truthful reply states.
- **Cost:** provider quota tracked.

## Verification / fitness function
FF-SEC-03 (approval), FF-SEC-04 (no gating), FF-REL-06 (provider-verified). Implementation: approval + anti-gating tests.

## Related
Requirement: Master Source §16, §38; PRD §17. Application rule: AFR-051, AFR-052. ADRs: 0016, 0022, 0028.

## Evidence
`docs/integrations/GOOGLE_BUSINESS_PROFILE_ARCHITECTURE.md`, `docs/integrations/google/GOOGLE_REVIEW_POLICY.md`.

## Non-claims
No Google OAuth, review sync, or reply publish runs in Step 3; Google readiness is unverified (OD-08).

## Rollback / supersession
Anti-gating and approval are permanent; policy relaxations require verified external policy change + Master Source update.
