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

## Release evidence (completed post-merge)
The following are recorded in the post-tag evidence sync once the code PR is merged and the annotated GO tag is
created (this section is intentionally pending until those SHAs exist — evidence-based completion, rule 19):

- Code PR number and final head SHA
- Authoritative `pr-ci` full run ID on the final head + `pr-ci / Required Gate` conclusion
- Merge commit SHA + lightweight `main-post-merge` result
- Fresh clean-checkout runtime verification on the merged SHA
- Annotated tag `aish-agentic-ai-step-5-runtime-repository-bootstrap-v1.0.0-go`: tag object SHA + peeled commit SHA
- Local/remote tag exact-match verification

See `docs/status/CURRENT_STATE.md` for the live status and
`docs/evidence/step-5/` for CI/release artifacts.
