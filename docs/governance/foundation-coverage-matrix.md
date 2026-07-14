# Foundation Coverage Matrix — Aish Agentic AI

Maps permanent product/engineering foundations to the rule that governs them, how they are enforced at runtime
and in tests/CI, current implementation status, and evidence. Derived, auditable, non-authoritative (authority
order: [CLAUDE.md](../../CLAUDE.md) §2). Truthful-status vocabulary per CLAUDE.md §5.

Status legend: `RULE ESTABLISHED — LATER STEP` = permanent rule recorded now, implementation scheduled in a later
step; `IMPLEMENTED (FOUNDATION)` = runtime foundation exists and is verified; `NOT STARTED` = not begun.

## Step 5 runtime foundation (AFR-127..133; rule 29; ADRs 0047–0050)

| Foundation | Source | Decision | Rule | Runtime enforcement | Test/CI enforcement | Status | Evidence | Gap |
|-----------|--------|----------|------|---------------------|---------------------|--------|----------|-----|
| Pinned runtime versions | MS §70.1; AFR-127 | Laravel 12 / PHP 8.4(^8.3) / PG17 / Redis7 / Node22, identical local/CI/docs | [29](../../.claude/rules/29-runtime-bootstrap-and-operations.md), 25 | `composer.json` platform pin; `preflight.sh` PHP≥8.3 | `backend-runtime-ci` PHP 8.4; `composer validate` | IMPLEMENTED (FOUNDATION) | ADR [0047](../decisions/adr/0047-runtime-version-and-support-policy.md); `composer.lock` | — |
| Reproducible bootstrap | MS §70.2; AFR-128 | Idempotent, fail-fast, no-root, no-`.env`-overwrite, no-secret | 29, 24 | `scripts/runtime/bootstrap-local.sh` | clean-checkout verify on merge SHA | IMPLEMENTED (FOUNDATION) | ADR [0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md) | — |
| Env contract / no secrets | MS §70.3; AFR-131 | `.env.example` placeholders only; no debug in prod | 29, 04, 24 | `RuntimePreflight`; `ConfigurationHealthCheck` | `secret-scan.sh`; `PreflightCommandTest` | IMPLEMENTED (FOUNDATION) | `.env.example`; `secret-scan.log` | — |
| Truthful health/readiness | MS §70.4; AFR-129 | `/live` no external dep; `/ready` 503 on failure; no leak | 29, 10, 11 | `Liveness/ReadinessController`, `config/health.php` | `tests/Feature/Health/*`; `verify-runtime.sh` neg path | IMPLEMENTED (FOUNDATION) | ADR [0049](../decisions/adr/0049-health-and-readiness-contract.md); `runtime/live.json`,`ready.json` | — |
| Proven connectivity | MS §70.5; AFR-131 | `select 1`, cache round-trip, queue dispatch+process | 29, 08 | health checks; `RuntimeSmokeJob` | `DatabaseConnectivityTest`,`CacheConnectivityTest`,`QueueSmokeJobTest`; `verify-runtime.sh` | IMPLEMENTED (FOUNDATION) | `docs/evidence/step-5/runtime/` | — |
| Queue/scheduler foundation | MS §70.5; AFR-132 | Foundation-only; retry no dup; failed-job path | 29, 05, 02 | `routes/console.php` heartbeat; smoke job | `SchedulerTest`; `verify-runtime.sh` | IMPLEMENTED (FOUNDATION) | ADR [0048](../decisions/adr/0048-local-development-and-bootstrap-strategy.md) | — |
| Security baseline | MS §70.5; AFR-131 | Security headers, trust-none proxy, prod-safe errors | 29, 04 | `SecurityHeaders` middleware; `bootstrap/app.php` | `SecurityHeadersTest` | IMPLEMENTED (FOUNDATION) | `app/Http/Middleware/SecurityHeaders.php` | — |
| Real runtime CI gate | MS §70.6; AFR-130 | Real PG+Redis job, required on ready PRs, no fake gate | 29, 28 | `.github/workflows/pr-ci.yml` | `validate-ci-topology.sh`, `test-required-gate.sh` | IMPLEMENTED (FOUNDATION) | ADR [0050](../decisions/adr/0050-backend-runtime-ci-under-cicd-ctrl-1.md) | — |
| Runtime-evidence-before-claims | MS §70.7; AFR-133 | Clean-checkout verify on merged SHA before GO tag | 29, 13, 27 | `verify-runtime.sh` | clean-checkout run; release evidence | IMPLEMENTED (FOUNDATION) | `docs/evidence/step-5/` | — |

## Permanent product foundations (rules established; product implementation scheduled later)

These permanent decisions are governed by rules today; their **application** implementation is scheduled in the
SaaS Foundation and later steps. Recorded here so no permanent decision is orphaned.

| Foundation | Rule | Status | Note |
|-----------|------|--------|------|
| Multi-tenant / branch isolation on every surface | [03](../../.claude/rules/03-multi-tenant-and-branch-isolation.md), 20 | RULE ESTABLISHED — LATER STEP | Tenant context precedes business features (AFR-099); no tenant data yet |
| Security, privacy, PII minimization, encrypted tokens | [04](../../.claude/rules/04-security-privacy-and-secrets.md) | RULE ESTABLISHED — LATER STEP (baseline started) | Step 5 adds secret hygiene + headers; full controls later |
| Human approval for public/high-risk actions | [05](../../.claude/rules/05-ai-governance-and-human-approval.md), 18 | RULE ESTABLISHED — LATER STEP | No public actions exist yet |
| Google Review anti-gating | [06](../../.claude/rules/06-google-review-policy.md), 18 | RULE ESTABLISHED — LATER STEP | No review flows exist yet |
| Supervisor + specialist agent architecture | [05](../../.claude/rules/05-ai-governance-and-human-approval.md), 20 | RULE ESTABLISHED — LATER STEP | No AI runtime yet |
| Data governance / audit / metering | [07](../../.claude/rules/07-data-governance-and-audit.md) | RULE ESTABLISHED — LATER STEP | No business data yet |
| Truthful system states | [10](../../.claude/rules/10-ui-ux-and-truthful-states.md), 27 | IMPLEMENTED (FOUNDATION) | Health probes + bootstrap surface are truthful |
| CI/CD safe runtime control | [28](../../.claude/rules/28-safe-ci-runtime-control.md) | CONFIGURED + extended | Step 5 adds the real backend runtime gate |
| Documentation living source / versioning | [12](../../.claude/rules/12-documentation-living-source-versioning.md) | ONGOING | Master Source v2.6.0 |

No orphan permanent decision. Gaps: none for Step 5 scope; business/module foundations remain `NOT STARTED` by
design (see [CLAUDE.md](../../CLAUDE.md) §5, rule 27).
