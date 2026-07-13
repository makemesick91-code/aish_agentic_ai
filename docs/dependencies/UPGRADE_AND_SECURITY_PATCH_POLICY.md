# Upgrade & Security-Patch Policy — Aish Agentic AI (Step 4)

**Title:** Step 4 Dependency Upgrade and Security-Patch Policy
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Policy is defined; no upgrade has been executed.
**Rule refs:** `.claude/rules/25` (dependencies & supply chain), `.claude/rules/12` (versioning), `.claude/rules/09` (gates), `.claude/rules/13` (release).
**Canonical:** Master Source v2.4.0 §68 (dependency governance), §34 (core stack), §54 (release gate); PRD v1.3.0.
**AFR refs:** AFR-095, AFR-096, AFR-097, AFR-098.
**Point-in-time research date:** 2026-07-13. **Evidence:** [`../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md`](../evidence/dependencies/DEPENDENCY_VERSION_RESEARCH.md).

## Non-claims

- Nothing is installed; no lock file exists; no upgrade or patch has been applied.
- EOL/version facts are point-in-time research (2026-07-13) and re-verified before each upgrade cycle.
- This policy governs upgrades **once implementation exists**; it is planning, not an execution record.

## Purpose

Define how dependencies are pinned, upgraded on a cadence, and patched under emergency conditions without weakening security, tenant isolation, or release gates. Works with the [Dependency Approval Matrix](DEPENDENCY_APPROVAL_MATRIX.md) and [Supply-Chain Governance](SUPPLY_CHAIN_GOVERNANCE.md).

## Pinning policy

| Layer | Pinning rule |
|-------|--------------|
| Direct PHP dependencies | **Pin** with a caret/constraint that stays within the approved major (e.g. Laravel `^12`); the lock file is authoritative. |
| Transitive dependencies | Locked via `composer.lock`; changes reviewed (SC-4). |
| Platform | **Pin** PHP platform in `composer.json` `config.platform`; pin PostgreSQL/Redis/Nginx/Node majors in provisioning. |
| Build/JS | **Pin** direct npm deps; commit `package-lock.json`. |
| Framework major | Never floats; a major bump (e.g. Laravel 12 → 13) is a governed decision, never an automatic upgrade. |

- Every direct dependency MUST be pinned to an approved range; unpinned/wildcard constraints are prohibited.
- The lock file is committed and is the single source of truth for exactly what would be installed.

## Upgrade classification and cadence

| Class | Example | Default cadence | Approval |
|-------|---------|-----------------|----------|
| Patch | 12.3.1 → 12.3.2; PHP 8.4.x | Rolling / weekly review | Standard PR + gates |
| Minor | 12.3 → 12.4; PostgreSQL 17.x | Monthly review window | Standard PR + gates + regression tests |
| Major (non-framework) | AWS SDK v3 → v4 | Quarterly evaluation | Dependency review + compatibility testing |
| Framework major | **Laravel 12 → 13** | On evaluation only | **ADR + Master Source update** (architecture change) |
| Runtime major | PHP 8.4 → 8.5; PostgreSQL 17 → 18 | On evaluation only | Compatibility + migration + backup/restore test + owner approval |
| Security | any CVE-driven bump | Expedited (see emergency) | Expedited path |

- Cadence reviews re-verify the point-in-time research and update the evidence file and approval matrix.
- A newer major recorded as EVALUATE DURING IMPLEMENTATION (Laravel 13, PostgreSQL 18, PHP 8.5, Node 26 LTS) is only adopted through its stated governed path.

## End-of-life tracking

| Component | Baseline | EOL watch (point-in-time 2026-07-13) | Action trigger |
|-----------|----------|--------------------------------------|----------------|
| PHP | 8.4 | 8.3 active support ends 2026-11-23; 8.4 to 2028-12-31 | Plan minor move before active-support end |
| Laravel | 12 | 12 bug-fix to 2026-08-13, security to 2027-02-24 | Evaluate 13 (ADR) before security-EOL |
| PostgreSQL | 17 | 5-year support per major | Plan major migration ~1 year before EOL |
| Redis | 7.x | maintained; license flagged | Re-review license each major; evaluate Valkey |
| Node.js | 24 LTS | 26 LTS expected Oct 2026 | Evaluate 26 LTS post-release |

A component within its EOL action window is escalated to a scheduled upgrade; letting a component reach EOL in production is prohibited.

## Emergency security-patch process

An **emergency** patch is triggered by a critical/high advisory affecting an in-use dependency (including transitive), an actively exploited vulnerability, or a supply-chain compromise disclosure.

1. **Detect:** advisory from `composer audit` / npm audit / upstream security channel / GitHub advisory.
2. **Assess:** severity, exploitability, exposure (is the vulnerable path reachable; tenant-isolation/PII impact).
3. **Contain:** if actively exploited and no patch, apply a temporary mitigation (config, WAF rule, feature-flag/kill switch) and record it truthfully.
4. **Patch:** bump to the fixed version, keeping within the approved major where possible; update the lock file.
5. **Test:** run the affected test suite, security/isolation checks, and a regression pass; **emergency** speed MUST NOT skip tests, security review, or evidence (`.claude/rules/09`).
6. **Release:** expedited PR → CI gates → review → merge; for production, backup/restore readiness and rollback plan apply.
7. **Evidence:** record advisory, fixed version, SBOM diff, test results, and timeline in release/incident evidence.
8. **Post-incident:** update the approval matrix, evidence file, and (if material) the Master Source.

- **Emergency** patches are out-of-cadence but fully governed: no fake completion, no skipped gate, no unverified success.
- If a fixed version is unavailable, the dependency is pinned to last-known-good behind a documented risk decision and a mitigation until a fix ships.

## Upgrade execution rules

- Upgrades run on a branch via PR; never directly on the protected default branch (`.claude/rules/13`).
- Every upgrade passes the standard gates: build, tests, static analysis, `composer audit`/npm audit, license check, SBOM regeneration, secret scan.
- Multi-tenant isolation and human-approval behaviors MUST be re-validated when an upgrade touches auth, RBAC, queue, storage, or AI paths.
- Rollback of a bad upgrade follows the operations rollback plan; the lock file makes downgrades deterministic.

## Severity-to-timeline (security)

| Advisory severity | Target remediation window | Path |
|-------------------|---------------------------|------|
| Critical (exploited) | Immediate — hours | **Emergency** patch + contain + evidence |
| Critical / High | ≤ 72h | Expedited patch |
| Medium | ≤ 2 weeks | Next patch review |
| Low | Next cadence window | Standard upgrade |

- Windows are baseline targets measured from advisory verification; measured mean-time-to-patch is tracked in supply-chain governance metrics.
- A missed critical window is escalated to the incident runbook.

## Rollback of an upgrade

- If an upgrade regresses, revert the dependency change and restore the previous lock file; the committed lock file makes the downgrade deterministic.
- Re-run tests, `composer audit`, and isolation checks on the rolled-back state before closing.
- Upgrade rollback follows the operations rollback plan; the framework-major governed path also governs a major-version reversal.

## Communication and record

- Every upgrade/patch produces a record: dependency, from/to version, class, advisory (if any), test evidence, SBOM diff.
- Material changes (framework/runtime majors, security posture shifts) trigger a Master Source impact analysis (`.claude/rules/12`).

## Automation

- Automated update proposals (e.g. dependency bots) MAY open PRs, but MUST NOT auto-merge; every change passes review and gates.
- Automation MUST respect pinning, the approval matrix status, and the framework-major governed path.

## Status

Upgrade and security-patch policy documented: **pin** direct dependencies with an authoritative lock file, cadence by upgrade class, EOL tracking, and a governed **emergency** patch path that never skips tests, security review, or evidence. No lock file exists; nothing is installed or upgraded. **PLANNING BASELINE — NOT IMPLEMENTED.**
