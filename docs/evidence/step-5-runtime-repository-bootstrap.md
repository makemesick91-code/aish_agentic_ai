# Step 5 — Runtime & Repository Bootstrap: Evidence

Canonical: Master Source v2.6.0 §70; rule
[29](../../.claude/rules/29-runtime-bootstrap-and-operations.md); ADRs 0047–0050; AFR-127..133.

Truthful status: runtime foundation **CODE COMPLETE** and **RUNTIME VERIFIED** locally. Business/module
implementation, deployment, pilot, and production: **NOT STARTED**.

## Local quality gates (authoring machine)
| Gate | Command | Result |
|------|---------|--------|
| Composer validate | `composer validate --strict` | `./composer.json is valid` |
| PHP dependency audit | `composer audit` | No advisories |
| JS dependency audit | `npm audit --audit-level=high` | 0 vulnerabilities |
| Formatting | `vendor/bin/pint --test` | passed |
| Static analysis | `vendor/bin/phpstan analyse` (level 6) | No errors |
| Test suite | `php artisan test` | 24 passed, 0 failed, 0 risky |
| Frontend build | `npm run build` | manifest generated |

## Runtime verification (real PostgreSQL 17 + Redis 7)
`scripts/runtime/verify-runtime.sh` — all steps PASS. Raw evidence: `docs/evidence/step-5/runtime/`.

| Step | Result |
|------|--------|
| docker-compose services healthy | PASS |
| `migrate:fresh` | PASS |
| `aish:preflight` | PASS |
| server boot + `/live` 200 `alive` | PASS |
| `/ready` 200 `ready` | PASS |
| queue dispatch + worker processing | PASS |
| scheduler heartbeat + `schedule:list` | PASS |
| negative readiness (`/ready` 503, `/live` 200 with Redis down) | PASS |
| frontend asset manifest present | PASS |

## Governance transition
The Step-4 planning "no dependency install" safeguard (`scripts/hooks/guard-dangerous-commands.sh`, AFR-096) was
superseded for the implementation phase: package **install** is now permitted; **publish**, cloud
provisioning/deploy, and DNS mutation remain blocked. The guard test (`scripts/hooks/test-guard.sh`) was updated
accordingly. Recorded in the Master Source v2.6.0 update (§70) and rule 25 supersession.

## Release evidence

- **Code PR #11** (Step 5 body): final head `6d6d264`, authoritative full CI run `29302066914` success
  (`Required Gate` green), merge commit `a0f0ca906a6755149799e09184e4a35b67c5efcd`.
- **Fix PR #12** (`.env.example` docker-port alignment, found by clean-checkout verification): final head
  `ea11923`, authoritative full CI run `29303547776` success, merge commit
  `77f9005d9565ecd2090f97a3ad16ddcb6984eba8` (GO-tag target).
- **Post-merge integrity** (`main-post-merge.yml`): run `29303602217` success.
- **Clean-checkout runtime verification** on the exact merged SHA `77f9005…`: all PASS (see the table above).
- **Annotated GO tag** `aish-agentic-ai-step-5-runtime-repository-bootstrap-v1.0.0-go`: tag object
  `c3a5a9fa04907b530bcb9ae394b1b9f64f977839`, peeled commit `77f9005d9565ecd2090f97a3ad16ddcb6984eba8`;
  local == remote == `main` HEAD. Not moved by this post-tag sync.
- **Truthful CI history** (rule 28 AFR-126): PR #11's first ready run failed (test-env leak) and was fixed by a
  corrective commit; each new head triggered a fresh full CI — no reused result.

Full record: [STEP_5_RELEASE_REPORT.md](../release/STEP_5_RELEASE_REPORT.md),
[STEP_5_TAG_VERIFICATION.md](../release/STEP_5_TAG_VERIFICATION.md).
