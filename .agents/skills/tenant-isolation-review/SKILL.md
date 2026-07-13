---
name: tenant-isolation-review
description: Review that every isolation surface (DB, cache, queue, storage, search, export, analytics, AI/knowledge retrieval, notifications, API, webhooks, audit, logs) has a documented control, fitness function, AFR, and planned test. Read-only.
---

# Skill: tenant-isolation-review

**Trigger:** Any change touching tenancy, data, events, API, AI retrieval, or a new surface.
**Non-trigger:** Pure prose edits with no isolation impact.
**Inputs:** `docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md`, dependency/ownership matrices.

## Workflow
1. For each of the 14 surfaces, confirm control + FF-TEN id + AFR + ADR + planned test are present.
2. Confirm no surface is missing and no reporting path bypasses isolation.
3. Cross-check the threat model covers cross-tenant/IDOR/queue-context/cache/storage/export/search/AI leakage.

## Safety boundaries
Read-only review. MUST NOT mutate code or weaken any control.

## Required output
Findings list (BLOCKER/HIGH/MEDIUM/LOW) with surface + missing element; or "all surfaces covered".

## Evidence
`docs/security/TENANT_ISOLATION_CONTROL_MATRIX.md`, `docs/security/STEP_3_THREAT_MODEL.md`.

## Failure behavior
On a missing surface control, classify BLOCKER and require a fix before GO.
