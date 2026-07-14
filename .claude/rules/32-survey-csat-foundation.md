# Rule 32 — Survey & CSAT Foundation

## Purpose
Keep the Step 7 substrate — survey authoring with immutable versioning, question/answer integrity, secure public
invitation/response, and deterministic CSAT/NPS/CES — tenant-safe, fail-closed, truthful, auditable, privacy-aware,
and free of cross-tenant leakage or token exposure from a clean checkout onward, without weakening any security,
privacy, review-policy, documentation, or release gate.

## Scope
Survey definition and immutable versioning; questions and options; scoring configuration; the survey builder and
preview; campaign lifecycle; invitation/public-link/QR security; public response validation and integrity; CSAT/NPS/
CES calculation and summaries; survey entitlement and usage metering; survey notifications; and survey audit. Applies
to `app/Models/{Survey,SurveyVersion,SurveyQuestion,SurveyOption,SurveyCampaign,SurveyInvitation,SurveyResponse,SurveyAnswer}`,
`app/Surveys/**`, `app/Http/Controllers/{Tenancy/Survey,PublicSurvey}/**`, `app/Http/Requests/Survey/**`,
`app/Policies/Survey*`, `app/Mail/SurveyInvitationMail`, `app/Enums/{Survey*,QuestionType,MetricType,CampaignStatus,InvitationStatus,ResponseStatus,ScoreDirection}`,
related `database/` and `tests/{Feature/Surveys,Feature/Security/Sf07*,Feature/Audit/Sf07*,Unit/Surveys,Architecture/Sf07*}`.
This is platform-core in top-level `app/` namespaces (ADR 0057), not inside `app/Modules/`; other business modules
remain **NOT STARTED**.

## Rules

### Survey ownership and lifecycle
- Every survey **MUST** be tenant-owned; branch scope **MUST** be enforced where applicable; a branch-owned survey
  **MUST NOT** be used by another branch. Cross-tenant survey access is a release blocker.
- Platform roles **MUST NOT** imply survey access; a platform role grants no tenant survey data.
- A published survey **MUST NOT** be hard-deleted; historical responses **MUST** remain readable per permission.

### Immutable versioning
- Published survey versions **MUST** be immutable (question text/order/type, options, required, scoring config,
  locale, response mode). Editing published content **MUST** create a new draft version; it **MUST NOT** mutate a
  published one. Historical responses **MUST** always resolve to the exact answered version.
- Publishing **MUST** be transactional and race-safe (no two current versions) and validated (≥1 question, unique
  order/key, choice ≥2 options, valid CSAT/NPS/CES scales).

### Questions, options, and answers
- Question type and answer type **MUST** match; options **MUST** belong to their question; an answer **MUST NOT**
  reference a question/option from another version.
- Free-text input is untrusted: it **MUST** be length-limited, escaped on output, **MUST NOT** be interpreted as an
  instruction, **MUST NOT** be sent to AI in Step 7, and **MUST NOT** appear in ordinary logs or audit.
- A completed response **MUST** be immutable through the normal workflow; invalidation **MUST** be an authorized,
  reasoned, audited process that never deletes the response. Answers are write-once.

### Public access, token security, and no-enumeration
- Public routes **MUST** use opaque validated access (opaque public id; internal ids alone are insufficient) and
  **MUST NOT** expose draft or preview content. Preview **MUST** require authoring authorization.
- Invitation secrets **MUST** be a cryptographically strong token stored only as a one-way hash (SHA-256+), compared
  in constant time, one-time, expiring, and revocable. The plaintext **MUST NOT** be persisted or appear in logs,
  audit, session, delivery records, or error output; it may exist transiently only to build the delivery link.
- Resolution failures **MUST** be generic (no oracle revealing whether a token maps to a real tenant). The public
  plane **MUST NOT** gain tenant application access, RBAC, or platform access.
- Public submission **MUST** be server-validated, rate-limited (per token and per IP), payload-bounded, transactional,
  and idempotent; a unique invitation **MUST** complete at most once (concurrent duplicates yield one completion).
- A QR code **MUST** contain only the protected public URL — no customer data, tenant secret, unprotected id, or
  health information — and **MUST** be deterministic and require no external service.

### Scoring
- CSAT/NPS/CES formulas **MUST** be deterministic, versioned, and computed only through the single MetricCalculator
  over stored raw answers — never from UI labels and never re-implemented in a controller/view/query.
- Raw counts **MUST** be retained; rounding **MUST** be explicit (2 decimals at the boundary); an empty population
  **MUST** return a truthful null, not a fabricated zero. Metrics **MUST** be tenant/branch/version scoped with no
  cross-tenant aggregation and no answer content in summaries.

### Consent and privacy
- A consent question **MUST** use explicit text, store an accepted/rejected boolean with the consent-text version,
  **MUST NOT** default to accepted or use a pre-checked input, and is required only when configured. Survey
  completion **MUST NOT** be treated as marketing consent. Anonymous responses **MUST NOT** silently create a
  customer identity; an IP address is not a customer identity. Healthcare/medical data **MUST NOT** be requested
  without explicit future governance (rules 18, 04).

### Entitlement, usage, and notification
- Survey entitlement decisions **MUST** use the single authoritative resolver via one guard
  (`App\Surveys\SurveyEntitlements`); an unknown/ungranted key **MUST** fail closed; a commercial state **MUST NOT**
  override a security suspension.
- Usage meters (`survey_invitations.created`, `survey_responses.completed`) **MUST** be tenant-scoped and idempotent;
  a preview or a failed submission **MUST NOT** be metered; a retry **MUST NOT** double-count.
- Survey invitation delivery **MUST** go through a reviewed mail adapter; internal survey notifications **MUST** use
  the approved SF-05 dispatcher; a retry **MUST NOT** create a duplicate logical invitation; a token **MUST NOT**
  enter a delivery record.

### Review-policy foundation (preserved)
- A survey score **MUST NEVER** determine whether Google Review access is shown; review gating remains **prohibited**;
  all eligible customers **MUST** retain equal review access in future implementation (rules 06, 18).

### Audit
- Security-relevant survey actions (survey/version/campaign/invitation lifecycle, response completion, invalidation)
  **MUST** be audited with actor and tenant; audit metadata **MUST** be sanitized and **MUST NOT** contain tokens,
  secrets, free-text answer content, or customer/medical data. A public response uses a safe system/public actor.

## Future foundations — RULE ESTABLISHED, IMPLEMENTATION DEFERRED TO LATER STEP
These remain binding but are scheduled later; Step 7 **MUST NOT** be read as delivering them: feedback analysis,
customer recovery, Google OAuth/Review sync and reply, AI sentiment/severity/summary, agent orchestration, RAG,
billing/payment, WhatsApp/SMS/push delivery, complex conditional survey logic, multilingual authoring, advanced
analytics/benchmarking/predictive insight, and deployment.

## Required checks
- `tests/Feature/Surveys/*`, `tests/Unit/Surveys/*`, `tests/Feature/Security/Sf07CrossTenantMatrixTest.php`,
  `tests/Feature/Audit/Sf07AuditTest.php`, `tests/Feature/Sf07MigrationIntegrityTest.php`,
  `tests/Architecture/{Sf07BoundariesTest,TenancyBoundariesTest,Sf05BoundariesTest}.php`; the consolidated Step-7-style
  GO/WATCH/NO-GO gate; a clean-checkout Step 7 verification on the merged SHA (`scripts/runtime/verify-step-7.sh` /
  `php artisan aish:verify-step-7`); `scripts/docs/secret-scan.sh`; the `backend-runtime-ci` gate (rules 28, 29).

## Evidence
- `app/Models/Survey*`, `app/Surveys/**`, `app/Http/Controllers/{Tenancy/Survey,PublicSurvey}/**`,
  `app/Http/Requests/Survey/**`, `app/Policies/Survey*`; `tests/**/Sf07*`, `tests/Feature/Surveys/**`,
  `tests/Unit/Surveys/**`; `docs/governance/foundation-coverage-matrix.md`; `docs/evidence/step-7/`.

## Related canonical sections
- Master Source §47, §62; §16, §50, §53, §54; PRD v1.3.0 §10.4, §10.5, §10.6, §16, §23, §24; ADRs 0057–0059; ADRs
  0011–0013, 0015, 0016, 0029, 0051–0056; AFR-171..AFR-187; rules 02, 03, 04, 05, 06, 07, 09, 10, 11, 18, 20, 26, 27,
  28, 29, 30, 31.

## Supersession
Permanent for Step 7+. Tenant/branch ownership, immutable published versions, question/answer integrity, hashed
one-time tokens with no-enumeration, completed-response immutability, deterministic versioned CSAT/NPS/CES with
explicit rounding, consent semantics, untrusted-free-text handling, no-response-content-in-logs/audit, the single
authoritative entitlement resolver, idempotent usage metering, the approved notification dispatcher, Google Review
anti-gating, platform-role isolation, and evidence-based release are permanent; superseded only by a higher-version
Master Source update that preserves these guarantees.
