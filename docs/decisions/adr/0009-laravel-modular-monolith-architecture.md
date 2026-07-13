# ADR 0009 — Laravel Modular Monolith Architecture

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Owner:** Principal / Laravel Platform Architect
- **Rule:** `.claude/rules/08`, `20` (AFR-003) · **Canonical:** Master Source v2.3.0 §34; PRD v1.2.0 §11, §22

## Context
Step 2 fixed persona/pilot scope; Step 3 must fix the application architecture so implementation does not
reopen fundamentals. The product is a multi-tenant SaaS with queues, external integrations, human approval, and
Agentic AI. Options ranged from a single unstructured Laravel app to microservices from day one.

## Decision
Adopt a **Laravel 12 modular monolith**: one deployable app composed of internally-bounded modules
(`app/Modules/*`) with explicit public interfaces (contracts, application services, domain events). Cross-module
work never mutates foreign tables. See [Application Architecture Baseline](../../architecture/APPLICATION_ARCHITECTURE_BASELINE.md).

## Alternatives
- **Microservices from day one** — rejected: premature; higher ops/security surface; distributed transactions;
  no evidence of a scale/isolation need. Extraction is available later (ADR 0020) with evidence.
- **Unstructured monolith** — rejected: erodes boundaries, tenant isolation, and testability.

## Consequences
Fast, safe path to MVP with consistent DB transactions and simpler operations. Requires discipline: enforced
module boundaries and fitness functions. Service extraction stays possible but gated by a new ADR.

## Impacts
- **Security:** smaller attack surface than distributed services; boundaries aid isolation.
- **Privacy:** single controlled data plane; redaction centralized.
- **Tenant isolation:** row-level ownership across one schema (ADR 0011); enforced on all surfaces.
- **Database:** one PostgreSQL, shared schema; transactional consistency per command.
- **Operational:** one deploy unit + workers/scheduler; simpler runbook.
- **Cost:** lower initial infra/ops cost than microservices.

## Verification / fitness function
FF-MOD-01..05 (module presence, one-writer-per-table, acyclic deps, minimal Shared Kernel). Step 3: doc/coverage
check; implementation: `tests/Architecture`.

## Related
Requirement: Master Source §34; PRD §11. Application rule: AFR-003. ADRs: 0010, 0011, 0020.

## Evidence
`docs/architecture/APPLICATION_ARCHITECTURE_BASELINE.md`, `docs/evidence/step-3/architecture/`.

## Non-claims
Does **not** claim any application code, deployment, integration, pilot, or production runtime exists.

## Rollback / supersession
Supersede via a higher-version Master Source update + ADR. Extraction to services requires ADR 0020 evidence.
