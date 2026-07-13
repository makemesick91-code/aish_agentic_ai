# CICD-CTRL-1 — Release Governance Review (report-only subagent)

Verdict: topology + fail-closed gate sound and truthful; release NO-GO until execution completes + F1/F3 resolved.

## Findings + resolution
- F1 (HIGH): verify-immutable-tag.sh only WARNs on a lightweight tag; must fail closed. → FIXED (annotated required; fail=1).
- F2 (MEDIUM): prior-tag immutability is relative + skips missing tags. → FIXED (pinned to known-good peeled SHAs; missing = fail).
- F3 (MEDIUM): main ruleset must require exactly `pr-ci / Required Gate` and drop stale `documentation-foundation` context.
  → ADDRESSED: baseline has NO protection/rulesets (nothing stale to drop); the applied ruleset requires exactly `Required Gate`.
- F4 (LOW): topology push-check awk keeps o=1 for whole file (brittle). → FIXED (scoped to the on: block).
- F5 (LOW/SUGGESTION): gate doesn't cross-check run_workflow_security routing. → FIXED (pass WF_SEC_REQUIRED; require success when required).
- F6 (SUGGESTION): self-validating CI residual risk. → NOTED in GO record (compensating: ruleset + review + no admin bypass).
