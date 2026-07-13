# Tenant Isolation — Aish Agentic AI

Canonical: Master Source §15.1, §37, §50 (multi-tenant test). Rule: `.claude/rules/03`. PRD §9, §23.2.

## Invariant
No data may leak across tenants. Every business record carries `tenant_id`; branch-relevant records carry
`branch_id`. This is a permanent, security-critical guarantee.

## Surfaces that MUST enforce isolation (Master Source §15.1)
Database queries · cache · queue jobs · file storage · search · export · API · webhook · AI retrieval ·
knowledge base · analytics · notifications · tenant-visible logs.

## Required behaviors
- Queue jobs MUST carry tenant context; cache/storage/AI retrieval MUST be tenant-scoped.
- Branch-scoped roles (e.g. Branch Manager) MUST see only their branch's data (region scoping likewise).
- Knowledge retrieval and agent runs MUST be tenant-scoped and send only minimum relevant context (`§42`).

## Verification (when application code exists — Master Source §50, PRD §23.2)
- Tenant A cannot see Tenant B (direct access, search, export, analytics).
- Branch manager sees only their branch.
- Queue preserves tenant context; cache/storage/AI retrieval tenant-scoped.
- Cross-tenant access, privilege escalation, and IDOR tests pass; results under `../evidence/validation/`.

## Release gate
Tenant-isolation test **PASS** is a mandatory security gate before any product release GO (`.claude/rules/09`).

**Status:** isolation model documented. Test evidence pending implementation (NOT STARTED).
