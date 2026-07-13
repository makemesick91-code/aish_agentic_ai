# Decision Log — Aish Agentic AI

Rule: `.claude/rules/12`, `00`. Chronological record of material decisions. Times in Asia/Makassar.
Superseded decisions are marked, never deleted.

| # | Date | Decision | Type | Authority | ADR / Ref | Status |
|---|------|----------|------|-----------|-----------|--------|
| D-001 | 2026-07-13 | Canonical repository is `makemesick91-code/aish_agentic_ai`; no alternative repo | Governance | Owner + Master Source §66.2 | Master Source v2.1.1 | Active |
| D-002 | 2026-07-13 | Documentation & Claude Rules Foundation delivered via branch → PR → CI → merge → annotated GO tag | Release | Master Source §66.10–§66.11 | ADR 0006, 0007 | Active |
| D-003 | 2026-07-13 | Canonical documentation hierarchy + authority order adopted | Governance | Master Source §66.3 | ADR 0001 | Active |
| D-004 | 2026-07-13 | Persistent Claude context via concise `CLAUDE.md` + modular `.claude/rules/` | Tooling | Master Source §66.4–§66.5 | ADR 0002 | Active |
| D-005 | 2026-07-13 | Graphify treated as derived; branded product `BLOCKED-OPTIONAL`; deterministic doc index used | Tooling | Master Source §66.7 | ADR 0003 | Active |
| D-006 | 2026-07-13 | Minimal, secret-free MCP allowlist; `.mcp.json` empty server set + governance | Security/Tooling | Master Source §66.8 | ADR 0004 | Active |
| D-007 | 2026-07-13 | Four project skills + six least-privilege review subagents | Tooling | Master Source §66.9 | ADR 0005 | Active |
| D-008 | 2026-07-13 | Limit Saver 1 not installed → documented fallback token-saving protocol | Tooling | Master Source §66.6 | `docs/tooling/LIMIT_SAVER.md` | Active |
| D-009 | 2026-07-13 | Master Source remains v2.1.1 (already records this foundation in §66); PRD normalized to v1.0.1 (metadata only) | Documentation | Master Source §5, GO Tag Prompt v1.0.1 | `VERSION_MATRIX.md` | Superseded by D-011 |
| D-010 | 2026-07-13 | Controlled empty-repository bootstrap of `main` (README+.gitignore), not a GO | Release | Master Source §66.2 | commit `516d1bd` | Active |
| D-011 | 2026-07-13 | Step 2 baseline: Master Source bumped to v2.2.0, PRD to v1.1.0, Persona & Pilot Use Cases v1.0.0 imported as living canonical docs; historical versions preserved | Documentation | Owner + Master Source §3–§5 | Master Source v2.2.0 §6, ADR 0008 | Active |
| D-012 | 2026-07-13 | First pilot tenant Klinik Gigi Daengtisia; recommended first branch Daengtisia Pusat (recommendation only, subject to readiness verification; may change via decision log without narrowing scope) | Product | Owner + Persona §2 | ADR 0008, `.claude/rules/16` | Active |
| D-013 | 2026-07-13 | Pilot operating rules fixed: invitation baseline, survey baseline, healthcare data boundary, mandatory human approval, no review gating, manual fallback, truthful external states | Product/Security | Master Source §16,§33,§43; PRD §13,§17 | `.claude/rules/17`,`18`, Persona §6–§12 | Active |
| D-014 | 2026-07-13 | Pilot operational targets are hypotheses (not results); hard safety gates mandatory; pilot GO/WATCH/NO-GO defined; Step 3 starts only after Step 2 release | Release | Master Source §54,§59; Persona §14,§16 | `.claude/rules/19`, `STEP_2_COVERAGE_MATRIX.md` | Active |
| D-015 | 2026-07-13 | Step 2 delivered via branch → PR → CI → merge → annotated GO tag `aish-agentic-ai-step-2-persona-pilot-v1.0.0-go`; documentation baseline only | Release | Master Source §66.10–§66.11 | `docs/release/STEP_2_*` | Active |
| D-016 | 2026-07-13 | Step 3 baseline: Master Source bumped to v2.3.0, PRD to v1.2.0; canonical application architecture fixed via ADRs 0009–0032 and AFR-001..072; historical versions preserved | Architecture/Documentation | Owner + Master Source §3–§5, §34 | Master Source v2.3.0 §6, ADRs 0009–0032 | Active |
| D-017 | 2026-07-13 | Architecture style = Laravel 12 modular monolith (microservices not default; extraction requires ADR 0020 evidence) | Architecture | Owner + Master Source §34 | ADR 0009, 0020 | Active |
| D-018 | 2026-07-13 | Tenancy = shared DB / shared schema / row-level ownership with tenant+branch context on every surface; RLS is future defense-in-depth (OD-01) | Architecture/Security | Master Source §17,§37 | ADR 0011, 0012, 0015 | Active |
| D-019 | 2026-07-13 | External effects use transactional outbox + idempotency + retry + dead-letter; no success before provider verification; truthful states | Architecture/Reliability | Master Source §35,§39 | ADR 0016, 0017 | Active |
| D-020 | 2026-07-13 | AI via provider abstraction with guardrails/redaction/human approval/kill switch/manual fallback; extraction criteria recorded (not triggered) | AI Governance | Master Source §23–§34,§44 | ADR 0019, 0020, 0023, 0028 | Active |
| D-021 | 2026-07-13 | Codex CLI not installed → `.codex/` config/rules/hooks authored + statically validated; `execpolicy`/hook runtime execution deferred (OD-07); branded Graphify host binary present but not governance-verified → deterministic index retained (OD-05); external Limit Saver not installed → project fallback (OD-06) | Tooling | Master Source §66.6–§66.9 | ADR 0031, `docs/evidence/step-3/inventory/tooling-inventory.txt` | Active |
| D-022 | 2026-07-13 | Step 3 delivered via branch → PR → CI → merge → annotated GO tag `aish-agentic-ai-step-3-application-architecture-adr-v1.0.0-go`; documentation/architecture baseline only | Release | Master Source §66.10–§66.11 | `docs/release/STEP_3_*` | Active |

## Notes
- No decision here changes MVP scope, security controls, or target market beyond what the canonical Master
  Source v2.3.0 records; Step 3 fixes the application-architecture contract without starting implementation.
- Application implementation, deployment, live integration, pilot readiness, pilot runtime, and production
  readiness remain NOT STARTED.
