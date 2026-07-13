# Step 4 Rollback Plan — Aish Agentic AI

**Title:** Step 4 Rollback Plan
**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Rollback procedures are planned; no deployment or rollback has occurred.
**Rule refs:** `.claude/rules/11` (observability/backup/operations), `.claude/rules/13` (git/release, no history rewrite), `.claude/rules/10` (truthful states), `.claude/rules/18` (external-action safety).
**Canonical:** Master Source v2.4.0 §51 (operations), §54 (operational gate), §43 (kill switch); PRD v1.3.0.
**AFR refs:** AFR-095..098 (ops/dependency governance context).

## Non-claims

- Nothing is deployed; no rollback has been executed.
- This plan extends the Step 3 `INCIDENT_AND_ROLLBACK_BASELINE.md` into a Step 4 planning baseline.
- Rollback targets/RTO are planning hypotheses until a drill measures them.

## Purpose

Define how a bad application release, migration, configuration, dependency upgrade, or external-action failure is reversed safely, without rewriting git history, without silent false-success, and without duplicate external side effects. Provider is NOT selected; steps are described as planned capabilities.

## Rollback triggers

| Trigger | Example | Response |
|---------|---------|----------|
| Failed deployment | App fails health checks post-deploy | Rollback to previous release |
| Regression | Critical bug/perf regression detected | Rollback release |
| Bad migration | Schema change causes errors/data risk | Migration rollback / restore |
| Bad dependency upgrade | Upgrade breaks a critical path | Downgrade via lock file |
| Guardrail/safety failure | PII leak risk, AI misbehavior | **Kill switch** + rollback |
| Tenant-isolation anomaly | Cross-tenant signal | Halt + rollback + incident |
| External-action failure | Google reply publish fails | Truthful failed state, no silent reversal |

## Application / deployment rollback (planned)

1. Detect via alerts/health checks (see observability plan) and enter the incident runbook.
2. Halt the rollout; stop new traffic to the bad release if a blue/green or versioned release is in use.
3. **rollback** to the previous known-good application version (immutable artifact / previous release directory / previous image).
4. Restart PHP-FPM pool and queue workers on the previous version within the isolated pilot environment.
5. Verify health checks, tenant scoping, and critical smoke flows before declaring the **rollback** complete.
6. Record evidence (trigger, from/to version, timeline, checks) — no fake success.

- Releases are designed to be reversible: immutable build artifacts, a retained previous version, and externalized config.
- The rollback RTO baseline target is ≤ 30 minutes at pilot scale; this is a hypothesis until drilled.

## Database migration rollback (planned)

- Migrations are written to be reversible where possible; destructive changes are staged (expand → migrate → contract) so a **rollback** does not lose data.
- If a migration is not safely reversible, recovery uses the backup/restore plan (restore to a clean target, replay WAL to pre-migration point).
- Migration rollbacks preserve tenant/branch scoping and audit-log integrity; audit history is never deleted (`.claude/rules/07`).
- A migration with no safe down-path is gated behind a backup + tested restore before it may run in production.

## Dependency-upgrade rollback (planned)

- The committed lock file makes downgrades deterministic: revert the dependency change, restore the previous lock file, rebuild.
- Re-run `composer audit` / tests to confirm the rolled-back state is safe.
- Ties to the [Upgrade & Security-Patch Policy](../dependencies/UPGRADE_AND_SECURITY_PATCH_POLICY.md) emergency path when the upgrade was security-driven.

## Configuration rollback (planned)

- Configuration and secrets are versioned/externalized; a bad config change is reverted to the previous known-good value from the secret manager/config store.
- No secrets are committed to the repo; config **rollback** never exposes credentials (`.claude/rules/04`).

## External-action rollback (truthful state, not silent reversal)

- External side effects (e.g. a published Google Review reply, a sent WhatsApp invitation) **MUST NOT** be silently reversed or faked.
- A failed external publish keeps a truthful `Publication failed` (or equivalent) state (`.claude/rules/10`, `.claude/rules/18`).
- Retries **MUST** be idempotent — a **rollback** or retry MUST NOT create a duplicate external action (`.claude/rules/17`, `.claude/rules/20`).
- Where an external action must be undone, it is a new, audited, human-approved corrective action, not a hidden rollback.

## Kill switch

- A kill switch halts AI workflows on guardrail/cost/safety failure (Master Source §43, `.claude/rules/05`); it is part of the rollback toolkit for AI-driven incidents.
- The manual workflow remains usable when AI is halted (`.claude/rules/05`, `.claude/rules/17`).

## Git / release rollback discipline

- Reverting a merged change uses a new revert commit via PR; `git push --force`, `git tag -f`, tag deletion, and history rewrite are prohibited (`.claude/rules/13`).
- Immutable GO tags are never moved; a superseding decision + Master Source update records any release reversal.

## Roles and decision authority

| Action | Who decides | Who executes |
|--------|-------------|--------------|
| Application **rollback** | On-call lead | Operations |
| Migration rollback / restore | Ops Architect | Operations |
| Kill switch (AI halt) | On-call / AI-governance | Automated + operator |
| External corrective action | Reputation Approver (human approval) | Operator |
| Release reversal record | Product owner | Release governance |

- Rollback authority is defined in advance so an incident does not stall on who may act.

## Rollback targets (planned hypotheses)

| Scenario | Target RTO |
|----------|-----------|
| Application version **rollback** | ≤ 30 min |
| Config rollback | ≤ 15 min |
| Migration restore-to-point | Per backup/restore RTO (≤ 4h pilot) |
| Dependency downgrade | ≤ 1h incl. rebuild + audit |

- Targets are hypotheses until a drill measures them; they are not reported as achieved without evidence.

## Post-rollback review

- Every executed **rollback** produces an incident record and a post-incident review: trigger, timeline, root cause, and preventive follow-up.
- Material rollbacks trigger a Master Source impact analysis (`.claude/rules/12`); superseded decisions are recorded, never deleted.

## Rollback drill (required before pilot)

| Drill | Frequency | Evidence |
|-------|-----------|----------|
| Application version **rollback** | Before pilot, then quarterly | Measured RTO + smoke checks |
| Migration rollback / restore-to-point | Before pilot | Recovery verified, no data loss |
| Dependency downgrade via lock file | Before pilot | Rebuild + audit pass |
| Kill-switch + manual-fallback | Before pilot | AI halted, manual flow works |

- A production release gate requires a rehearsed **rollback** path with evidence; an unrehearsed rollback path is NO-GO for pilot.

## Status

Rollback plan documented as a Step 4 planning baseline: triggers, application **rollback**, migration rollback, dependency downgrade, configuration rollback, truthful external-action handling with no duplicate side effects, kill switch, git discipline, and mandatory rollback drills before pilot. Nothing is deployed or rolled back yet. **PLANNING BASELINE — NOT IMPLEMENTED.**
