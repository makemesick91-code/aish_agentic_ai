# Local Development Strategy — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED · Step 4 ENVIRONMENT planning · **Application implementation: NOT STARTED.**
**Rule refs:** `.claude/rules/23` (environments), `.claude/rules/24` (configuration & secrets); supporting `.claude/rules/04`, `08`, `09`.
**Canonical:** Master Source v2.4.0 §68; PRD v1.3.0. · **AFR refs:** AFR-087..094.

> **Non-claims.** Nothing here is deployed or provisioned. No runtime, container, or script exists yet. No
> secret is stored or committed. Provider not selected (WATCH). All files named below are **specifications**
> for future creation, not created in Step 4.

## 1. Goal

Give every developer a reproducible `local` environment that matches production versions closely enough to
avoid "works on my machine" defects, while holding only **synthetic** data (see
[DATA_POLICY_BY_ENVIRONMENT.md](DATA_POLICY_BY_ENVIRONMENT.md)).

## 2. Baseline decision

- **RECOMMENDED** baseline: **Docker Compose.** It provides version parity with production (PHP 8.3+,
  PostgreSQL, Redis, S3-compatible storage), reproducibility, and cross-OS support (Linux, macOS, Windows/WSL2).
- **FALLBACK**: **Laravel Sail** for developers who prefer the Laravel-native wrapper; Sail is itself a
  Docker Compose profile, so parity is preserved. The **fallback** exists so a developer blocked on the primary
  path still has a supported route.

Both the recommended and fallback options **MUST** produce the same application behavior; a developer **MUST NOT**
be required to use a divergent stack.

## 3. Options evaluated

| Option | Parity | Reproducibility | Cross-OS | Verdict |
|--------|--------|-----------------|----------|---------|
| Docker Compose | High (pinned images) | High | High | **RECOMMENDED** |
| Laravel Sail | High (Docker under the hood) | High | High | **FALLBACK** |
| Native (host PHP/Postgres/Redis) | Medium (host drift) | Low | Medium | Discouraged; allowed for advanced users at own risk |
| Hybrid (host PHP + containerized services) | Medium–High | Medium | Medium | Allowed for performance; must match service versions |

Native and hybrid setups **MUST** match the pinned production service versions; if they drift, the developer
**MUST** fall back to Docker Compose before trusting local results.

## 4. Planned future files (specifications only — NOT created in Step 4)

| File | Purpose |
|------|---------|
| `.env.example` | Placeholder env keys only; no real secrets (see [CONFIGURATION_AND_SECRET_MATRIX.md](CONFIGURATION_AND_SECRET_MATRIX.md)) |
| `compose.yaml` | App, PostgreSQL, Redis, storage, mail catcher (Mailpit) services, pinned versions |
| `Makefile` / task runner | One-command entry points (`make up`, `make test`, `make doctor`) |
| `scripts/setup` | First-run bootstrap: build, install deps, generate key, migrate, seed synthetic data |
| `scripts/doctor` | Diagnose local environment (versions, ports, services, env keys present) |
| `scripts/test` | Run the local test suite consistently |
| `scripts/validate` | Run documentation/code validation gates locally before pushing |

These are **planned**. Step 4 **MUST NOT** create runtime application code, containers, or executable
setup/test scripts; only these documented specifications.

## 5. Local runtime rules (MUST)

- The `local` environment **MUST** use only synthetic seed data; real PII/medical/production data is prohibited.
- Local secrets **MUST** be developer-personal sandbox values, **MUST NOT** equal organization production
  secrets, and **MUST NOT** be committed (the developer's `.env` stays untracked).
- Email **MUST** route to a local catcher (e.g. Mailpit) or log driver — no real send.
- WhatsApp, Google, and AI providers **MUST** default to mock/disabled adapters locally; a developer using a
  real sandbox AI key **MUST** keep redaction/guardrails on and **MUST NOT** send prohibited MED fields.
- Local Redis, database, queue, and storage **MUST** use the `local` names/prefixes from
  [ENVIRONMENT_NAMING_STANDARD.md](ENVIRONMENT_NAMING_STANDARD.md) to avoid collision with any shared dev
  service.
- The workflow **MUST** remain usable when AI is unavailable (mock adapters); basic functions **MUST NOT**
  depend on a live AI provider (Rule 05).

## 6. Version parity targets (PLANNED)

| Component | Target |
|-----------|--------|
| PHP | 8.3+ |
| Laravel | 12 |
| PostgreSQL | Production-matched major version |
| Redis | Production-matched major version |
| Node (frontend build) | LTS matched to CI |

Exact pinned versions will be fixed in `compose.yaml` when the application is bootstrapped, and **MUST** match
`CI` and the deployment class.

## 7. Developer onboarding (PLANNED sequence)

1. Clone repo; copy `.env.example` to `.env` and fill sandbox placeholders.
2. Run `scripts/setup` to build containers, install dependencies, generate `APP_KEY`, migrate, seed synthetic data.
3. Run `scripts/doctor` to confirm services and versions.
4. Run `scripts/test` and `scripts/validate` before pushing.

Until the application is bootstrapped, only the documentation gates (`scripts/docs/validate.sh`) run locally.

## 8. Cross-reference

Promotion out of local: [ENVIRONMENT_PROMOTION_POLICY.md](ENVIRONMENT_PROMOTION_POLICY.md).
CI counterpart of these jobs: [CI_RUNTIME_PLAN.md](CI_RUNTIME_PLAN.md).
