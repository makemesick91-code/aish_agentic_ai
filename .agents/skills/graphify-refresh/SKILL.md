---
name: graphify-refresh
description: Rebuild the deterministic derived documentation index, enforce exclusions (no secrets/PII), run canonical query-smoke, and record graph metadata. Never commits secrets/oversized data; derived-only.
---

# Skill: graphify-refresh

**Trigger:** After docs/rules/ADRs change; before PR/GO.
**Non-trigger:** No documentation change.
**Inputs:** `graphify.yaml`, docs tree.

## Workflow
```bash
scripts/graphify/build.sh         # deterministic index; excludes secrets/PII; idempotent
scripts/graphify/query-smoke.sh   # canonical queries resolve to canonical files
```
Records `docs/evidence/graphify/build-manifest.json` (counts only) + `query-smoke.txt`.

## Safety boundaries
Derived-only; MUST NOT override canonical docs, index secrets/PII, or send data to a network service. Branded
Graphify (host binary present) is NOT used until governance-verified (OD-05); do not claim it was used.

## Required output
Node/edge counts, drift result (idempotent rebuild), query-smoke pass count.

## Evidence
`docs/evidence/graphify/`, `docs/evidence/step-3/graph/`.

## Failure behavior
If the rebuild is non-deterministic or a query fails to resolve, report FAIL; do not hand-edit the manifest.
