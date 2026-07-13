# CI Change Classification — CICD-CTRL-1

Canonical: Master Source v2.5.0 §69.3. Rule: `.claude/rules/28`. ADR 0043. AFR-113,114,125.
Realised by `scripts/ci/classify-changes.sh` (unit-tested by `scripts/ci/test-change-classifier.sh`).

## Why internal
A top-level `paths:` filter on a **mandatory** workflow can leave the required check permanently "pending" (never
reported), which deadlocks branch protection. Classification is therefore performed **inside** the workflow
(CI-PRINCIPLE-09); the required gate always reports.

## Categories and path mapping
| Path glob | Category |
|-----------|----------|
| `.github/workflows/**`, `scripts/ci/**`, `scripts/release/**` | workflow |
| `.claude/**`, `.agents/**`, `.codex/**`, `AGENTS.md`, `CLAUDE.md`, `*/AGENTS.md`, `scripts/docs\|graphify\|codex\|hooks/**` | governance |
| `docs/ci/**` | documentation + workflow |
| `docs/security/**`, `SECURITY.md` | documentation + security |
| `docs/ai/**` | documentation + ai |
| `docs/integrations/**` | documentation + integration |
| `docs/canonical\|decisions\|architecture/**` | documentation + governance |
| `docs/**` (other) | documentation |
| `app/**`, `routes/**` | backend |
| `config/**` | backend + security |
| `database/**`, `**/migrations/**` | database |
| `resources/**` | frontend |
| `package*.json`, lockfiles | frontend + dependency |
| `composer.json\|lock` | backend + dependency |
| `tests/Security/**` | security + test |
| `tests/**` | test |
| `infrastructure/**`, `deploy/**`, `Dockerfile`, `docker-compose*.yml` | infrastructure |
| anything else | **unknown** |

## Fail-closed rule (CI-PRINCIPLE-10)
`full_safe_suite=true` when any category is `unknown`, `security`, `backend`, `database`, `dependency`,
`integration`, `infrastructure`, or `release`, **or** when ≥3 categories are present (mixed). An empty/unresolvable
diff is treated as `unknown`.

## Runtime routing (NOT-YET-AVAILABLE)
`backend`, `frontend`, and `database` routes are recognised but their runtime suites do not exist yet (application
implementation NOT STARTED). CI records them as routed-but-unavailable — there is **no fake Laravel runtime gate**
(AFR-093, AFR-125). Today the concrete suites are the documentation-as-code gates and workflow-security validators.

## Output
`categories`, `full_safe_suite`, `run_documentation`, `run_workflow_security` (to `$GITHUB_OUTPUT`), plus a
deterministic JSON at `docs/evidence/cicd-ctrl-1/change-classifier/last-classification.json`. No file content or
PII is emitted.
