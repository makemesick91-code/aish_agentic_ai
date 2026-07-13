# CICD-CTRL-1 — Ruleset Rollback

Rule: `.claude/rules/28`, `13`. ADR 0046.

## Before-state (baseline, captured before any change)
- `branch-protection-before.json` → `{"message":"Branch not protected","status":"404"}` (main had **no** classic protection).
- `rulesets-before.json` → `[]` (no repository rulesets).
- `actions-permissions-before.json` → `{"enabled":true,"allowed_actions":"all","sha_pinning_required":false}`.

## Applied change
Repository ruleset `cicd-ctrl-1-main-protection` on the default branch (`main`), enforcement `active`:
- `deletion` blocked, `non_fast_forward` blocked (force-push blocked),
- `pull_request` required (0 required approvals — solo-maintainer, no self-approval deadlock),
- `required_status_checks` requires context `Required Gate` (GitHub Actions app id 15368),
- `bypass_actors: []` (no admin bypass).

Payload: `main-ruleset-payload.json`. After-state stored as `rulesets-after.json` / `main-ruleset-after.json`.

## Rollback procedure
To revert to the baseline (no protection), delete the ruleset:
```
RULESET_ID=$(gh api repos/makemesick91-code/aish_agentic_ai/rulesets --jq '.[] | select(.name=="cicd-ctrl-1-main-protection") | .id')
gh api -X DELETE repos/makemesick91-code/aish_agentic_ai/rulesets/$RULESET_ID
```
This restores the exact before-state (`rulesets-before.json` = `[]`). Never use admin bypass; never force-push or
delete a tag as part of a rollback.
