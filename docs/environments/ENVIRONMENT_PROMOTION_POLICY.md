# Environment Promotion Policy — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/09`, `11`, `13`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No promotion has occurred. No provider is selected
> (WATCH). This is a planning specification for a future deployment pipeline.

## 1. Promotion flow (MUST)

Change **MUST** flow only in this order:

```text
local → test / CI → staging → pilot → production
```

- Every change **MUST** originate on a feature branch, be tested in `test`/`CI`, and merge only via reviewed PR
  (Rule 13).
- There is **no direct** deploy from an unreviewed branch, a developer laptop, or a local build to `pilot` or
  `production`. Restated: an **unreviewed** branch **MUST NOT** be deployed to `pilot` or `production`, and no
  environment may be skipped upward.
- A promotion **MUST** deploy an artifact/commit that has already passed the environment below it in the flow.
- Rollback flows downward-equivalent (revert to the last known-good release); it **MUST NOT** be simulated.

## 2. Promotion gates

Each upward promotion **MUST** pass all applicable gates before the deploy proceeds. A failed gate is a
blocker; gates **MUST NOT** be skipped, weakened, or faked (Rule 09).

| Gate | test/CI → staging | staging → pilot | pilot → production |
|------|-------------------|-----------------|--------------------|
| Approval | PR review approved, CI green | Release approval + QA sign-off | Owner approval (Rule 19 GO) |
| Migration gate | Migrations run clean on synthetic DB | Migrations reversible, dry-run on staging | Backup-verified, tested, reversible |
| Backup gate | n/a (ephemeral) | Pre-deploy snapshot | Verified backup + tested restore |
| Smoke test | Automated smoke passes | Smoke + UAT scenarios pass | Post-deploy smoke + health verified |
| Rollback | Documented, rehearsed | Rehearsed on staging | Rehearsed, one-command, evidence |
| Evidence | CI logs/artifacts | UAT + smoke evidence | Full release-gate evidence (Rules 09, 11, 13) |
| Tag/release relationship | Commit SHA recorded | Release candidate tag | Immutable annotated release tag on merged commit |

## 3. Tag and release relationship

- A promotion to `production` **MUST** correspond to an immutable annotated release tag created only on the
  merged default-branch commit (Rule 13). `git tag -f`, tag deletion/moving, and history rewriting are
  prohibited.
- `staging` and `pilot` deploys **SHOULD** use a release-candidate reference (e.g. `-rc` tag) that traces to
  the same commit later promoted to `production`.
- The deployed artifact's commit SHA **MUST** be recorded per environment for auditability.

## 4. Migration gate detail

- Schema migrations **MUST** be forward-only-safe or explicitly reversible with a tested `down` path.
- Destructive migrations (drop/rename column, data backfill) **MUST** have a backup taken first and a rollback
  plan, and **MUST NOT** run against `pilot`/`production` without the backup gate passing.
- Migrations **MUST** run under the environment's own database credential and **MUST NOT** touch another
  environment's database.

## 5. Backup and rollback gate detail

- Before any `pilot`/`production` deploy, a fresh backup **MUST** be taken and its restore **MUST** be tested
  at least on a defined cadence (Rule 11).
- A rollback **MUST** be possible without duplicate external side effects (idempotency; Rules 05, 08).
- A deploy that cannot be rolled back **MUST NOT** proceed to `production`.

## 6. External side-effect safety during promotion

- Promotions into `pilot`/`production` enable real email/WhatsApp/Google/AI behavior; the deploy process
  **MUST NOT** trigger bulk external actions as a side effect of migration or warm-up.
- Human approval gates for public replies and high-risk actions (Rules 05, 06, 18) **MUST** remain enforced
  after any promotion; a promotion **MUST NOT** disable them.
- External success **MUST NOT** be reported before provider verification (Rule 18).

## 7. Roles and authority

| Promotion | Who may approve |
|-----------|-----------------|
| → staging | PR reviewer + pipeline (automated on green) |
| → pilot | DevOps + QA lead + Pilot Coordinator |
| → production | DevOps + Product Owner (Rule 19 GO decision) |

No single actor may both author and solely approve a `pilot`/`production` promotion; meaningful independent
approval on high-risk deploys **MUST NOT** be removed.

## 8. Cross-reference

Data allowed in each target environment: [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md).
Environment attributes and gates context: [ENVIRONMENT_MATRIX.md](ENVIRONMENT_MATRIX.md).
Target-environment specifics: [STAGING_PLAN.md](STAGING_PLAN.md),
[PILOT_ENVIRONMENT_PLAN.md](PILOT_ENVIRONMENT_PLAN.md),
[PRODUCTION_ENVIRONMENT_PLAN.md](PRODUCTION_ENVIRONMENT_PLAN.md).
