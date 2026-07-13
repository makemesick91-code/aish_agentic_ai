---
name: documentation-gate
description: Runs the documentation-as-code validation gates (markdown, internal links, version consistency, foundation coverage, requirement traceability, secret scan, generated-file drift, contradiction checks) and reports a single pass/fail with evidence. Read/validate only — no repository mutation.
---

# Skill: documentation-gate

Use before every commit/PR and whenever documentation changes. Implements the local gates in
`.claude/rules/09`/`13` and Master Source §66.10.

## Run order
```bash
scripts/docs/check-version-consistency.sh     # canonical versions + repo identity consistent
scripts/docs/check-links.sh                   # internal markdown links resolve
scripts/docs/check-rule-frontmatter.sh        # every .claude/rules/*.md has required frontmatter
scripts/docs/check-foundation-coverage.sh     # 100% coverage for permanent decisions + release gates
scripts/docs/secret-scan.sh                   # no secret patterns; sources excluded appropriately
scripts/docs/validate.sh                      # aggregates all of the above + markdown structure + drift
```

## Checks performed
- Markdown structure sanity and internal-link validity.
- Canonical version consistency (Master Source ≥ v2.1.1; PRD reference consistent).
- Foundation coverage 100% for permanent decisions and release-critical foundations.
- Requirement traceability: no orphan critical requirement.
- Contradiction detection for active decisions (e.g. no rule permits review gating or auto-publish outside §16.4).
- Secret scan and generated-artifact drift.

## Output
Emit per-check status, exit codes, and an overall `PASS`/`FAIL`. Write outputs under
`docs/evidence/validation/`. On any failure, report the exact file/line and stop before commit.

## Safety
Validation only. This skill MUST NOT edit content, commit, push, merge, or tag.
