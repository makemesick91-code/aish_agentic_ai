# ADR 0045 — CI Security: Pinned Actions, Least Privilege, No Untrusted Privileged Execution

- **Status:** Accepted (2026-07-13, Asia/Makassar) — CICD-CTRL-1; CI governance CONFIGURED, application NOT STARTED
- **Owner:** DevSecOps Engineer
- **Rule:** `.claude/rules/28` (AFR-119..122) · **Canonical:** Master Source v2.5.0 §69; §43; rules 04, 25

## Context
The prior workflow used floating action tags (`actions/checkout@v4`, `actions/setup-python@v5`), had no per-job
`timeout-minutes`, and repository Actions permissions allow all actions with SHA pinning not required. Floating tags
are mutable supply-chain surfaces; broad token scope and untrusted privileged execution are the highest CI risks.

## Decision
All third-party/official actions **MUST** be pinned to an immutable 40-hex commit SHA (checkout pinned to
`08c6903cd8c0fde910a37f88322edcfb5dd907a8`, v5.0.0). Default `permissions:` is `contents: read`; write is granted
only to a job that needs it. `pull_request_target` is prohibited (no untrusted PR code with a privileged token). No
`curl|sh`/`wget|bash` remote-script execution; no untrusted PR title/body/ref interpolated into a shell; every job
declares `timeout-minutes`; secret scan and workflow-security gates cannot be optimized away. `setup-python` is
dropped in favour of the runner's preinstalled `python3`, reducing the action surface to one.

## Alternatives
- **Floating action tags** — rejected: mutable supply-chain surface (AFR-120).
- **`write-all` / broad token** — rejected: violates least privilege (AFR-121).
- **`pull_request_target` for fork PRs** — rejected: privileged execution of untrusted code (AFR-122).

## Consequences
Actions are auditable and immutable; a compromised upstream tag cannot silently change behaviour. Action upgrades
require a recorded SHA bump. A workflow-security validator enforces the baseline in CI.

## Impacts
- **Security:** immutable actions, least privilege, no untrusted privileged execution — the core hardening.
- **Privacy:** no secret/token is printed; no environment dump.
- **Tenant isolation:** unaffected (CI-level control).
- **Database:** none.
- **Operational:** `timeout-minutes` bounds hung jobs; cache keys (future runtime) include lock hashes, never secrets.
- **Cost:** negligible; bounded timeouts prevent runaway runner minutes.

## Verification / fitness function
`scripts/ci/validate-workflow-security.sh` (pinned SHA, permissions present, no `write-all`, no
`pull_request_target`, no `curl|sh`, timeout per job, no skip-ci). CI-SEC-03/04/05.

## Related
Requirement: Master Source v2.5.0 §69, §43; PRD v1.3.0. Application rules: AFR-119..122. Rules: 28, 04, 25.
ADRs: 0031 (supply-chain governance), 0042, 0043.

## Evidence
`.github/workflows/*.yml` (pinned), `scripts/ci/validate-workflow-security.sh`; `docs/ci/CI_SECURITY_BASELINE.md`.

## Non-claims
This governs workflow security only; it does not claim application security controls are implemented, nor that any
runtime dependency is installed or scanned.

## Rollback
Action SHAs may be advanced via a recorded decision (verify the new SHA against the upstream tag first). Unpinning an
action or enabling `pull_request_target` privileged execution is prohibited without an owner-approved Master Source update.
