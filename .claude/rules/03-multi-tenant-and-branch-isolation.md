---
id: "03"
title: Multi-Tenant and Branch Isolation
domain: security
scope: repository-wide
authority: permanent
canonical_refs:
  - "Master Source v2.1.1 §15.1, §17, §37, §50 (multi-tenant test)"
  - "PRD §9, §10.2, §10.3, §14.2, §23.2"
supersede: "Only via a versioned Master Source update explicitly approved by the product owner."
---

# Rule 03 — Multi-Tenant and Branch Isolation

## Purpose
Guarantee that no tenant can ever access another tenant's data, and that branch scoping is enforced.

## Scope
Data model, queries, cache, queue, storage, search, export, API, webhook, analytics, notifications,
AI retrieval, knowledge base, and tenant-visible logs.

## Rules
- Every business data record **MUST** carry `tenant_id`; branch-relevant records **MUST** carry `branch_id`.
- Tenant isolation **MUST** be enforced on **all** of: DB queries, cache, queue jobs, file storage,
  search, export, API, webhook, AI retrieval, knowledge base, analytics, notifications, and tenant-facing logs.
- There **MUST NOT** be any cross-tenant data leakage. Queue jobs **MUST** carry tenant context;
  cache/storage/AI retrieval **MUST** be tenant-scoped.
- Branch-scoped roles (e.g. Branch Manager) **MUST** see only their branch's data.
- Multi-tenant isolation **MUST** be covered by tests (Master Source §50 multi-tenant test; PRD §23.2)
  before any release gate can pass.

## Required checks
- Foundation coverage maps isolation to `docs/security/TENANT_ISOLATION.md` and this rule.
- When application code exists: cross-tenant access tests, search/export leakage tests, queue-context tests.

## Evidence
- `docs/security/TENANT_ISOLATION.md`; future test evidence under `docs/evidence/validation/`.

## Related canonical sections
- Master Source §15.1, §17 (SaaS structure), §37 (data governance), §50 (tests); PRD §9, §14.2, §23.2.

## Supersession
Isolation guarantees are permanent; any change is a security-impacting Master Source update requiring review.
