# Architecture Open Decisions — Aish Agentic AI

**Status:** ARCHITECTURE BASELINE (Step 3) · **Application implementation: NOT STARTED.**
**Canonical:** Master Source v2.3.0 · **Rules:** `.claude/rules/12`, `20`.

Non-blocking follow-ups. Fundamental Step 3 decisions are **Accepted** in ADRs 0009–0032 and are **not** listed
here. Each open item has an owner, target phase, impact, status, and acceptance condition. None blocks the Step 3
GO tag (they are WATCH items, not gates).

| ID | Open decision | Owner | Target phase | Impact | Status | Acceptance condition |
|----|---------------|-------|--------------|--------|--------|----------------------|
| OD-01 | PostgreSQL Row-Level Security as defense-in-depth | Security Architect | Step 4+ (impl.) | Adds DB-enforced isolation on top of app scoping | OPEN (evaluation) | Benchmark + policy design; ADR addendum; not required for row-level ownership baseline |
| OD-02 | Deployment provider selection (VPS/managed) | DevOps | Pre-pilot ops | Fixes hosting/topology specifics | OPEN | Provider chosen, recorded in decision log; secrets via manager |
| OD-03 | AI orchestrator extraction to Python/FastAPI | AI Architect | When ADR 0020 criteria met | Splits AI into a service | OPEN (criteria-gated) | Evidence of ≥1 extraction criterion; new ADR |
| OD-04 | Frontend evolution (SPA/Inertia/API-first) | Frontend Lead | Post-MVP | Richer UX | OPEN | ADR after MVP console proves the Blade baseline |
| OD-05 | Branded Graphify adoption | Tooling Owner | Step 4+ | Replaces deterministic index if governance-verified | OPEN (WATCH) | Verify official package/provenance/license, approval, no secret/PII egress |
| OD-06 | External Limit Saver 1 skill adoption | Tooling Owner | Any | Context efficiency | OPEN (WATCH) | Trusted source verified; project fallback stays until then |
| OD-07 | Codex CLI availability for `execpolicy`/hooks runtime | Tooling Owner | Any | Enables live rule/hook test execution | OPEN (WATCH) | Codex installed; run positive/negative execpolicy tests |
| OD-08 | Google Business Profile production API re-verification | Integration Owner | Pre-integration | Confirms current Google policy/API | OPEN | Re-verify before any real integration; keep mock labelled truthfully |
| OD-09 | RPO/RTO target values | DevOps | Pre-production | Sets backup/DR objectives | OPEN | Measured/agreed targets recorded (not fabricated) |

These are tracked in the decision log and surfaced in the Step 3 GO/NO-GO as WATCH items.
