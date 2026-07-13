---
id: "17"
title: Pilot Invitation, Survey, Consent, and Manual Fallback
domain: product
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.2.0 §16, §34, §35"
  - "PRD v1.1.0 §10.5, §10.6"
  - "Persona and Pilot Use Cases v1.0.0 §6, §7, §16 (UC-P0-16)"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 17 — Pilot Invitation, Survey, Consent, and Manual Fallback

## Purpose
Encode the pilot invitation frequency, survey baseline, consent capture, and the mandatory manual fallback so
the workflow stays lawful, non-spammy, and usable without AI.

## Scope
Invitation eligibility and timing, survey design, consent/opt-out, and AI-unavailable operation.

## Rules
- Invitations **MUST** honor the pilot baseline: trigger `VisitCompleted`; default delay 60 minutes
  (configurable 30–120); sending window 09:00–20:00 Asia/Makassar; primary channel a unique WhatsApp survey
  link; QR **MUST** be available as fallback; email is optional; frequency cap **MUST** be one invitation per
  customer per 14 calendar days; at most one reminder after 24 hours; invitation expiry after 7 days.
- Opt-out **MUST** be honored; opted-out customers **MUST NOT** be re-contacted. Survey tokens **MUST** be
  hard-to-guess, tenant/branch scoped, and expiring.
- The survey **MUST** collect CSAT (1–5), CES (1–5), NPS (0–10), an optional comment, and conditional
  problem-category plus follow-up **consent** when CSAT is low or the customer requests follow-up. It **MUST**
  be mobile-first, require no login, target under two minutes, and **MUST NOT** request a five-star rating or
  medical/diagnosis information.
- Integration **MUST** prefer a signed/authenticated API/webhook from DaengtisiaMS; controlled CSV/manual
  import and on-site QR are temporary fallbacks that **MUST** be shown truthfully in analytics/audit and
  **MUST NOT** be presented as real-time integration success.
- Manual workflow (survey view, manual classification/triage, ticketing, approval, audit) **MUST** remain
  usable when AI or an external provider is unavailable (UC-P0-16); retry **MUST NOT** create duplicate
  invitations, tickets, or replies (idempotency), and a kill switch **MUST** exist.

## Required checks
- `scripts/docs/check-step2-coverage.sh` verifies the invitation baseline and manual-fallback coverage.

## Evidence
- `docs/integrations/WHATSAPP_INVITATION_PILOT_BASELINE.md`,
  `docs/integrations/DAENGTISIAMS_EVENT_CONTRACT_BASELINE.md`, `docs/ai/PILOT_MANUAL_FALLBACK.md`,
  `docs/product/PILOT_USE_CASE_CATALOG.md`.

## Related canonical sections
- Master Source §16.2, §34, §35, §39; PRD §10.5, §10.6; Persona §6, §7, §9.1 (UC-P0-03..05, UC-P0-16).

## Supersession
Frequency, consent, and manual-fallback guarantees are permanent; fully automated delivery is a P2 item
requiring a versioned Master Source update.
