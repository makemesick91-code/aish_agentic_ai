# ADR 0059 — CSAT, NPS, and CES Scoring Contract

- **Status:** Accepted (2026-07-14, Asia/Makassar) — Step 7 Survey & CSAT Foundation; survey capability IN PROGRESS toward GO
- **Owner:** Principal Architect / Customer Experience Metrics Engineer
- **Rule:** `.claude/rules/32`, `.claude/rules/10` · **Canonical:** Master Source §47; PRD v1.3.0 §10.6 (FR-RES-002); rules 32, 10, 27

## Context
CSAT/NPS/CES are the product's headline metrics; if they are computed inconsistently, from UI labels, or with
undefined rounding, the numbers become untrustworthy and non-auditable. The failure modes to prevent: formulas
duplicated across controllers/views, metrics derived from presentation instead of stored answers, floating-point
ambiguity in percentages, and metrics that ignore the version/tenant/branch scope of the responses.

## Decision
- **Single deterministic calculator.** All metric computation goes through `App\Surveys\Scoring\MetricCalculator`
  over stored raw numeric answers of **completed** responses — never UI labels. No controller, view, or query may
  re-implement a formula (arch-test enforced).
- **Versioned configuration.** Thresholds and scales come from the answered `SurveyVersion`'s question
  `scoring_config` (scale min/max, satisfied threshold, direction) — deterministic and version-aware.
- **CSAT** = satisfied valid responses ÷ all valid CSAT responses × 100, where a value is valid within the
  configured scale and "satisfied" applies the configured threshold in the configured direction. Also returns valid
  count, satisfied count, average.
- **NPS** uses fixed categories (detractors 0–6, passives 7–8, promoters 9–10) over values in [0,10]; score =
  promoter% − detractor% in [−100, +100], derived from raw counts (no double rounding).
- **CES** returns the average of valid values with the configured direction as interpretation metadata only; a
  metric is never labelled "good/bad" without configured interpretation.
- **Rounding policy.** Raw counts are authoritative; percentages/averages are rounded to **2 decimals** at the
  boundary (`MetricCalculator::PRECISION`). Empty populations return null (truthful "no data"), never 0-as-result.
- **Scope.** Aggregation (`App\Surveys\SurveySummaryService`) is tenant-scoped (fail-closed), version-aware, and
  branch-filterable; there is no cross-tenant aggregation and no answer content in summaries.

## Alternatives
- **Per-controller/per-view formulas** — rejected: guarantees drift and inconsistent numbers.
- **Deriving metrics from labels/UI** — rejected: labels are presentation and can change; metrics must come from
  stored raw values.
- **Storing pre-rounded percentages only** — rejected: loses precision and auditability; store counts, round at the
  edge.
- **Returning 0 for an empty population** — rejected: misleading; null is the truthful "no data" state.

## Consequences
Every CSAT/NPS/CES figure is reproducible from stored answers, identical regardless of where it is displayed, rounded
by one explicit policy, and confined to the correct tenant/branch/version.

## Impacts
- **Security:** deterministic computation over stored values only; no formula duplication that could diverge or be
  manipulated via presentation.
- **Privacy:** summaries expose aggregates only, never individual answer content.
- **Tenant isolation:** aggregation is fail-closed tenant-scoped, branch-filterable, and version-aware; no
  cross-tenant aggregation.
- **Database:** none new; reads stored `survey_answers` numeric values of completed responses.
- **Operational:** one calculator, exhaustively boundary-tested; null for empty populations (truthful "no data");
  raw counts retained alongside derived values.
- **Cost:** negligible; pure computation.

## Verification / fitness function
`tests/Unit/Surveys/MetricCalculatorTest.php` (16 boundary cases), `tests/Feature/Surveys/SurveySummaryTest.php`,
`tests/Architecture/Sf07BoundariesTest.php` (scoring math only under `app/Surveys/Scoring/`). AFR-181..AFR-184.

## Related
Requirement: Master Source §47; PRD v1.3.0 §10.6. Rules: 32, 10, 27. ADRs: 0057.

## Evidence
`app/Surveys/Scoring/*`, `app/Surveys/SurveySummaryService.php`; `docs/governance/foundation-coverage-matrix.md`;
`docs/evidence/step-7/`.

## Non-claims
Does not implement AI insight, benchmarking, predictive analytics, or root-cause analysis (all NOT STARTED); does not
claim deployment/pilot/production readiness.

## Rollback
Single-calculator, versioned-config, deterministic formulas, explicit rounding, and tenant/branch/version scope are
permanent; changing a canonical formula requires an owner-approved Master Source update.
