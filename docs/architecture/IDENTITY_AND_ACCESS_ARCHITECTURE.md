# Identity and Access Architecture — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 §34, §43 · **Rules:** `.claude/rules/03`, `04`, `16`, `20` ·
**ADR:** [0013](../decisions/adr/0013-authentication-and-authorization.md), [0012](../decisions/adr/0012-tenant-and-branch-context-propagation.md).

## 1. Authentication (ADR 0013)
- Web session auth via **Laravel Fortify**; API/token auth via **Sanctum** per interface.
- **MFA-ready** identity: MFA factors modelled from day one; platform admins require MFA.
- OAuth **state** validated on every external connect flow; tokens encrypted, refresh tokens never plaintext,
  rotation supported (ADR 0022).
- Basic auth/RBAC **MUST NOT** depend on AI availability.

## 2. Authorization (ADR 0013)
- **Spatie Permission** for role/permission, wrapped by **tenant + branch scope policies**.
- Effective access = `role permission` ∧ `tenant scope` ∧ `branch scope`. A permission alone never grants
  cross-tenant or cross-branch access.
- Least privilege by default; sensitive/high-risk abilities (publish reply, refund/compensation, data
  deletion, plan change) require explicit permission **and** human approval where mandated (`.claude/rules/05`, `18`).

## 3. Minimum pilot role coverage (`.claude/rules/16`)
Business Owner / Executive Sponsor · Pilot Coordinator / Corporate Admin · Branch Manager · Recovery Assignee /
Customer Service · Reputation Approver. Compatible roles **MAY** be combined but a combination **MUST NOT**
remove meaningful approval on high-risk actions. Dokter/perawat/kasir/Admin Klinik are event sources/
stakeholders, **not** required console operators.

## 4. Scope resolution model
```text
Authenticate → load user + tenant + branch memberships
→ resolve request tenant/branch context (ADR 0012)
→ assert user scope ⊆ requested scope (else 403)
→ policy check ability within scope
→ query layer re-applies tenant/branch filter (defense-in-depth)
```

## 5. Auditing
Role assignment, permission change, MFA enrollment, login anomalies, and every high-risk authorization decision
are recorded to the Audit module (append-only). See [Observability Architecture](OBSERVABILITY_ARCHITECTURE.md).

## 6. Truthful status
No authentication, RBAC, or policy code runs in Step 3. This document is the contract implementation must meet.
