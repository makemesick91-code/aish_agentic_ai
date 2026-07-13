# CICD-CTRL-1 — Traceability Matrix

Rule: `.claude/rules/28`, `.claude/rules/12`. Every permanent CI decision traces Master Source → ADR → AFR →
Claude rule → AGENTS instruction → workflow/validator → actual GitHub run evidence. **No critical orphan.**

Canonical: Master Source v2.5.0 §69; ADRs 0042–0046; AFR-105..126; PRD v1.3.0 (unchanged).

| AFR | Master Source | ADR | Claude rule | AGENTS | Workflow / Validator | Evidence |
|-----|---------------|-----|-------------|--------|----------------------|----------|
| AFR-105 exact-SHA evidence | §69.1(1) | 0042 | 28 | root "CI runtime control" | `pr-ci.yml` (per-PR head), `required-gate-decision.sh` | `docs/evidence/cicd-ctrl-1/ci/` |
| AFR-106 local-first | §69.1(2) | 0042 | 28 | root | `fast-local.sh`, `full-local.sh` | `docs/evidence/cicd-ctrl-1/local-validation/` |
| AFR-107 draft-first fast CI | §69.1(3) | 0042 | 28 | root | `pr-ci.yml` `draft-fast-ci` | draft run on this PR |
| AFR-108 one final full CI | §69.1(4) | 0042 | 28 | root | `pr-ci.yml` `full-documentation-ci` | ready run on this PR |
| AFR-109 re-run on SHA change | §69.1(5) | 0042 | 28 | root | `pr-ci.yml` (pull_request synchronize) | run history |
| AFR-110 no duplicate push+PR | §69.1(6) | 0042 | 28 | root | `validate-ci-topology.sh` | `fast-*ci-topology.log` |
| AFR-111 cancel stale runs | §69.1(7) | 0042 | 28 | root | `pr-ci.yml` concurrency | run history |
| AFR-112 stable required gate | §69.1(8) | 0043/0046 | 28 | root | `pr-ci.yml` `Required Gate`, `test-required-gate.sh` | ruleset evidence |
| AFR-113 internal routing | §69.1(9) | 0043 | 28 | root | `classify-changes.sh` (no top-level paths) | `change-classifier/` |
| AFR-114 fail closed | §69.1(10) | 0043 | 28 | root | `classify-changes.sh`, `test-change-classifier.sh` | classifier test log |
| AFR-115 post-merge lightweight | §69.1(11) | 0044 | 28 | root | `main-post-merge.yml`, `validate-ci-topology.sh` | post-merge run |
| AFR-116 post-tag lightweight | §69.1(12) | 0044 | 28 | root | `verify-immutable-tag.sh` | `release/tag-verification.*` |
| AFR-117 no evidence-only full CI | §69.1(13) | 0044 | 28 | root | GitHub Release flow | release artifacts |
| AFR-118 no skip directive | §69.1(14) | 0043 | 28 | root | `validate-workflow-security.sh` | wf-security log |
| AFR-119 security never optimized away | §69.1(15) | 0043/0045 | 28,04 | root | `secret-scan.sh`, `validate-workflow-security.sh` | validation logs |
| AFR-120 pinned actions | §69.1(16) | 0045 | 28,25 | root | `validate-workflow-security.sh` | wf-security log |
| AFR-121 least privilege | §69.1(17) | 0045 | 28 | root | `validate-workflow-security.sh` | wf-security log |
| AFR-122 no untrusted privileged exec | §69.1(18) | 0045 | 28,04 | root | `validate-workflow-security.sh` | wf-security log |
| AFR-123 ruleset enforcement | §69.5 | 0046 | 28,13 | root | `main` ruleset | `ruleset/*-after.json` |
| AFR-124 budget ≠ green | §69.1(20) | 0042/0046 | 28,09 | root | run-budget doc + reviewer | `CI_RUN_BUDGET.md` |
| AFR-125 runtime routed NOT-YET-AVAILABLE | §69.3 | 0043 | 28,23 | root | `classify-changes.sh` | classification json |
| AFR-126 evidence over assertion | §69.1(19) | 0042 | 28,27 | root | `audit-ci-runs.sh` | baseline + run evidence |

## Orphan check
- Every AFR-105..126 maps to a Master Source clause, an ADR (0042–0046), Claude rule 28, an AGENTS instruction,
  and an evidence path. Enforcement is by either a **validator script** (`scripts/ci/*`, unit-tested and
  self-passing) or **workflow-enforced behavior with live run evidence** (e.g. AFR-107/108 via the `pr-ci.yml`
  draft/full jobs, cross-checked by `CI-DRAFT-01`/`CI-FULL-01`/`CI-GATE-01`). No critical orphan.
