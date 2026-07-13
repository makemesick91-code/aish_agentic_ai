---
id: "09"
title: Testing and Quality Gates
domain: quality
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §50, §54, §59"
  - "PRD §23, §24, §30"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 09 — Testing and Quality Gates

## Purpose
Define the testing strategy and the release gates that must pass with evidence before any GO.

## Scope
Functional, multi-tenant, AI evaluation, security, and performance testing; release gating.

## Rules
- Testing **MUST** cover the Master Source §50 categories: functional, multi-tenant isolation, AI
  evaluation (with the full adversarial dataset incl. prompt injection, PII, sarcasm, mixed language),
  security (broken access control, cross-tenant, privilege escalation, OAuth leakage, CSRF/XSS/SQLi,
  file upload, webhook forgery, rate-limit bypass, prompt injection, IDOR/SSRF), and performance.
- Release gates (Master Source §54; PRD §24) — functional, security, data, AI, integration, operational —
  **MUST** pass with evidence before a product release GO. AI gate **MUST** confirm no PII leakage on the
  test suite, valid structured output, active human approval, cost limit, kill switch, and idempotent retry.
- A feature **MUST NOT** be called done unless the Definition of Done (Master Source §59; PRD §30) is met,
  including tests passing, security/AI evaluation as relevant, audit, docs, CI, evidence, and truthful status.
- For **this documentation foundation**, the applicable gates are the documentation-as-code gates in
  `.claude/rules/13` and `docs/quality/RELEASE_GATES.md` (source checksums, version consistency, markdown,
  links, rule frontmatter, foundation coverage, traceability, contradiction, secret scan, Graphify smoke, CI).
- Gates **MUST NOT** be skipped, weakened, or faked; a token-saving skill **MUST NOT** reduce coverage.

## Required checks
- `scripts/docs/validate.sh` aggregates all documentation gates; CI runs them on PR and `main`.

## Evidence
- `docs/quality/TEST_STRATEGY.md`, `RELEASE_GATES.md`, `REQUIREMENTS_TRACEABILITY_MATRIX.md`;
  `docs/evidence/validation/` and `docs/evidence/ci/`.

## Related canonical sections
- Master Source §50 (testing), §54 (release gate), §59 (DoD); PRD §23, §24, §30.

## Supersession
Gates are permanent; adding gates is allowed, removing/weakening a gate requires documented owner approval.
