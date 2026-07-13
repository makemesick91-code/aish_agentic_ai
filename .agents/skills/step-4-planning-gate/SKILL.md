---
name: step-4-planning-gate
description: Run the Step 4 (domain, branding, environment, dependency, SaaS Foundation) documentation gates and report a single pass/fail with evidence. Validation only — never buys domains, mutates DNS, provisions, installs packages, merges, or tags.
---

# Skill: step-4-planning-gate

**Trigger:** Validating Step 4 planning docs before commit/PR, or auditing Step 4 coverage/truthful states.
**Non-trigger:** Application implementation, provisioning, domain registration, dependency installation (all out of scope).
**Inputs:** None (operates on the repository working tree).

## Workflow (validation only)
```bash
scripts/docs/check-step4-coverage.sh      # domain/brand/env/dep/SaaS coverage + truthful states + no orphan
scripts/docs/check-brand-tokens.sh        # brand planning-token JSON structure + planning label
scripts/docs/check-version-consistency.sh # Master Source v2.4.0 / PRD v1.3.0 / identity
scripts/docs/check-adr.sh                 # ADRs 0001–0041 structure + sequence
scripts/graphify/query-smoke.sh           # 46 canonical queries incl. Step 4
scripts/docs/validate.sh                  # aggregate (writes evidence to docs/evidence/validation/)
```

## Safety constraints
- MUST NOT buy a domain, mutate DNS, issue TLS, provision infrastructure, install a package, generate a lock
  file, merge, tag, force-push, or deploy.
- MUST NOT read `.env`, secrets, or credentials.
- Reports findings + evidence paths only.

## Evidence
`docs/evidence/validation/*.log`, `docs/evidence/graphify/query-smoke.txt`.

## Failure behavior
On any gate failure, report the failing gate + offending file/line; do not "fix" by weakening a gate. Fixes go
to the offending document, never to the gate threshold.
