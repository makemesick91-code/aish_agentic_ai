# AGENTS.md — docs/quality/

Area rules for quality & release gates. See root [AGENTS.md](../../AGENTS.md) and `.claude/rules/09,13,19`.

- Gates MUST pass with evidence and MUST NOT be skipped, weakened, or faked (AFR-054,066,072).
- Traceability MUST have no orphan critical requirement (AFR-062; FF-DOC-01).
- 41 fitness functions guard boundaries/isolation/reliability; Step 3 checks docs, implementation adds code tests.
- Completion is evidence-based; status is truthful; GO tags are immutable (AFR-066,067,068).
- A documentation GO tag attests documentation/architecture readiness **only** — not implementation, deployment,
  pilot readiness, or production readiness. **Application implementation: NOT STARTED.**
