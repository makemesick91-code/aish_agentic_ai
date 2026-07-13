# AGENTS.md — docs/operations/

Area rules for operations. See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/11,13`.

- Observability: structured logs/traces/metrics + prompt/model/token/cost + health; minimum alerts incl.
  tenant-isolation anomaly (ADR 0024; AFR-057,060).
- Backups exist and are encrypted; **restore is tested** before production GO; DR sequence documented (ADR 0027; AFR-055,056).
- Rollback via app redeploy + expand/contract schema; outbox/idempotency prevent duplicate external effects.
- Secrets are re-provisioned via a secret manager on recovery, never restored from repo (AFR-023).
- Architecture decision ≠ runtime evidence. **Deployment: NOT STARTED. Application implementation: NOT STARTED.**
