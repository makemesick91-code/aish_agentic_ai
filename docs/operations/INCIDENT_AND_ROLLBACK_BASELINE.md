# Incident and Rollback Baseline — Aish Agentic AI

Canonical: Master Source §43 (incident log, kill switch), §54 (operational gate). Rule: `.claude/rules/11`, `13`. PRD §24, §25.

## Incident response
- Maintain an incident runbook and support workflow (operational gate, Master Source §54).
- Security/AI incidents are logged (`security_events`, `agent_failures`); a **kill switch** halts AI
  workflows on guardrail/cost/safety failure (`.claude/rules/05`).
- Alerts (`OBSERVABILITY_BASELINE.md`) trigger the runbook; truthful states are shown (never fake success).

## Rollback
- A rollback plan MUST exist before a production release (operational gate).
- Application/deployment rollback follows the established deployment strategy (OPEN — OD-9); external side
  effects (e.g. published review replies) are handled via truthful state + audit, not silent reversal.

## Documentation-foundation rollback (this release)
Because this release is documentation/config only, rollback is low-risk:
1. The change is delivered on `chore/aish-agentic-ai-documentation-foundation` via PR — revert the merge
   commit with a new commit (never force-push, never rewrite history — `.claude/rules/13`).
2. The immutable GO tag is never moved; a superseding decision + Master Source update records any reversal.
3. The `main` bootstrap commit remains as the PR base.

**Status:** incident/rollback baseline documented. Production runbooks at implementation (NOT STARTED).
