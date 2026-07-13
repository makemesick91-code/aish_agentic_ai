# Environment Naming Standard — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/03`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No resource, database, bucket, or secret path exists.
> No provider is selected (WATCH). These are **planned** names only.

## 1. Purpose

Fix collision-free naming for every environment resource so that no resource in one environment can ever be
mistaken for, or accidentally point at, another environment's resource. All names **MUST** embed the
environment token. The canonical environment tokens are:

`local`, `test`, `ci`, `staging`, `pilot`, `prod`

(`prod` is the resource token for the `production` environment; `ci` for `CI`.)

## 2. Naming conventions (MUST)

- Every environment-scoped resource name **MUST** contain its environment token.
- Names **MUST NOT** collide across environments; a substring match of one environment token **MUST NOT**
  also match another (tokens are distinct words).
- The product prefix is `aish` (or `aish_agentic` where a longer form is conventional).
- Redis logical isolation uses the key prefix `aish:{env}:` and a distinct Redis database index per
  environment; a shared Redis instance across classifications is **NOT** permitted for deployment-class envs.
- Object-storage buckets follow `aish-agentic-{env}`.
- Secret paths follow `secret/aish/{env}/...`.

## 3. Concrete name matrix (PLANNED)

| Resource | local | test | CI | staging | pilot | production |
|----------|-------|------|----|---------|-------|------------|
| Application name | `aish-agentic-local` | `aish-agentic-test` | `aish-agentic-ci` | `aish-agentic-staging` | `aish-agentic-pilot` | `aish-agentic-prod` |
| Database name | `aish_agentic_local` | `aish_agentic_test` | `aish_agentic_ci` | `aish_agentic_staging` | `aish_agentic_pilot` | `aish_agentic_prod` |
| Redis key prefix | `aish:local:` | `aish:test:` | `aish:ci:` | `aish:staging:` | `aish:pilot:` | `aish:prod:` |
| Redis DB index | `0` | `1` | `2` | `3` | `4` | `5` (dedicated instance in prod) |
| Queue name | `aish-local-default` | `aish-test-default` | `aish-ci-default` | `aish-staging-default` | `aish-pilot-default` | `aish-prod-default` |
| Object-storage bucket | `aish-agentic-local` | `aish-agentic-test` | `aish-agentic-ci` | `aish-agentic-staging` | `aish-agentic-pilot` | `aish-agentic-prod` |
| Log stream | `aish/local/app` | `aish/test/app` | `aish/ci/app` | `aish/staging/app` | `aish/pilot/app` | `aish/prod/app` |
| Metrics namespace | `aish.local` | `aish.test` | `aish.ci` | `aish.staging` | `aish.pilot` | `aish.prod` |
| Secret path | `secret/aish/local/` | `secret/aish/test/` | `secret/aish/ci/` | `secret/aish/staging/` | `secret/aish/pilot/` | `secret/aish/prod/` |
| Feature-flag env | `local` | `test` | `ci` | `staging` | `pilot` | `production` |
| Backup artifact prefix | `aish-backup-local-` | `aish-backup-test-` | `aish-backup-ci-` | `aish-backup-staging-` | `aish-backup-pilot-` | `aish-backup-prod-` |

> Note: `local`, `test`, and `CI` share no persistent infrastructure with the deployment class; their Redis DB
> indices differ only to avoid accidental local collision when a developer points a tool at a shared dev Redis.

## 4. Deployment-class resource isolation

For `staging`, `pilot`, and `production`, in addition to distinct names, each environment **MUST** have its own
Unix deployment user, application directory, PHP-FPM pool, Nginx server block, queue worker service, backup
destination, and monitoring namespace. Names for those OS-level resources follow the same `{env}` token, e.g.:

| OS resource | staging | pilot | production |
|-------------|---------|-------|------------|
| Deploy user | `aish-staging` | `aish-pilot` | `aish-prod` |
| App directory | `/srv/aish/staging` | `/srv/aish/pilot` | `/srv/aish/prod` |
| PHP-FPM pool | `aish-staging` | `aish-pilot` | `aish-prod` |
| systemd worker | `aish-staging-worker` | `aish-pilot-worker` | `aish-prod-worker` |
| Nginx server name | `staging.aish.example` | `pilot.aish.example` | `app.aish.example` |

Domain names above are **placeholders** pending provider and DNS selection (WATCH).

## 5. Anti-collision checks

- A future validation script **SHOULD** assert that no two environments share a database name, bucket name,
  Redis prefix, Redis DB index (per instance), queue name, secret path, or deploy user.
- Any new environment-scoped resource **MUST** be added to the matrix in §3 with its `{env}` token.
- Reuse of a production name for a lower environment (or vice versa) is prohibited and is a review blocker.

## 6. Cross-reference

Secrets under these paths are classified in [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md).
Which data may live in each named store is governed by [DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md).
Per-environment attributes are in [ENVIRONMENT_MATRIX.md](ENVIRONMENT_MATRIX.md).
