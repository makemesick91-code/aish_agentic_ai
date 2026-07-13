---
id: "20"
title: Application Architecture and Foundation Rules
domain: architecture
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.3.0 §34-§42 (architecture, data, events, API, AI, RAG)"
  - "PRD v1.2.0 §11, §18.2, §22"
  - "ADRs 0009-0032; docs/architecture/APPLICATION_FOUNDATION_RULES.md (AFR-001..072)"
supersede: "Only via a versioned Master Source update + ADR explicitly approved by the product owner."
---

# Rule 20 — Application Architecture and Foundation Rules

## Purpose
Fix the Step 3 application-architecture contract so implementation does not reopen fundamental decisions, and
bind every permanent architecture decision to an ADR, an AFR, an AGENTS instruction, a fitness function, and
evidence.

## Scope
Application architecture, module boundaries, tenancy, data, events, API, AI, integrations, operations, and the
Application Foundation Rules catalog.

## Rules
- The architecture **MUST** be a **Laravel 12 modular monolith** (ADR 0009). Microservices are **NOT** the
  default; module→service extraction **MUST** require ADR 0020 evidence.
- The 17 modules **MUST** own their data; a module **MUST NOT** write another module's tables; cross-module work
  **MUST** use a contract, application service, or domain event; the Shared Kernel **MUST** stay minimal; there
  **MUST NOT** be an undocumented circular dependency (ADR 0010; AFR-004, AFR-005).
- Tenancy **MUST** be shared-DB/shared-schema/row-level ownership with tenant/branch context propagated to every
  surface incl. jobs and events (ADR 0011, 0012, 0015; AFR-006..020). Tenant isolation **MUST** hold on all
  surfaces in the [Tenant Isolation Control Matrix](../../docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md).
- External side effects **MUST** be idempotent, use a transactional outbox, bounded retry, dead-letter, and
  **MUST NOT** report success before provider verification (ADR 0016, 0017; AFR-031..036).
- AI **MUST** use the provider-abstraction control plane (structured output, redaction, guardrails, human
  approval, prompt/model version, cost/trace, manual fallback, kill switch); customer content **MUST NOT** steer
  tool calls; `MED` data **MUST NOT** reach AI or public output (ADR 0019, 0023, 0028; AFR-041..050).
- The Application Foundation Rules (AFR-001..072) **MUST** remain the canonical, mapped rule catalog; every
  permanent decision **MUST** trace ADR ↔ AFR ↔ AGENTS ↔ Claude rule ↔ fitness function ↔ evidence with **no**
  orphan (AFR-069).
- `AGENTS.md`, `.claude/rules/`, and `.codex/rules/` **MUST** stay in sync (one source of truth, AFR-069).
- Status **MUST** stay truthful: Step 3 attests architecture/documentation readiness only; application
  implementation, deployment, pilot readiness/runtime, and production readiness remain **NOT STARTED** (AFR-068).

## Required checks
- `scripts/docs/check-step3-coverage.sh` (architecture/module/isolation/AFR coverage + truthful status),
  `scripts/docs/check-adr.sh` (ADR structure/sequence), `scripts/codex/check-agents.sh` (AGENTS chain + drift).

## Evidence
- `docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md`, `APPLICATION_FOUNDATION_RULES.md`,
  `docs/decisions/adr/0009`–`0032`, `docs/quality/STEP_3_ARCHITECTURE_TRACEABILITY_MATRIX.md`.

## Related canonical sections
- Master Source §34–§42, §50, §51, §54; PRD §11, §18.2, §22, §23, §24.

## Supersession
Architecture changes are ADR + Master Source events; tenant isolation, human approval, anti-gating, and
truthful-status constraints are permanent.
