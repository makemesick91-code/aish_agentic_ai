# AI Evaluation Baseline — Aish Agentic AI

Canonical: Master Source §50 (AI evaluation), §54 (AI gate), §21 (AI ops metrics). Rule: `.claude/rules/09`, `05`. PRD §23.3, §19.5.

## Evaluation dataset (Master Source §50)
Must cover: positive, negative, mixed, sarcasm, typo, informal Indonesian, English, mixed language,
emoji-only, empty, spam, threat, legal allegation, fraud allegation, medical complaint, PII, **prompt
injection**, long, ambiguous, repeated, abusive, and irrelevant reviews.

## Metrics & thresholds (Master Source §50; PRD §19.5)
Sentiment accuracy · topic accuracy · severity recall & precision · **PII leakage (must be zero on suite)** ·
unsafe response rate (below limit) · hallucination rate · human edit rate · approval rate · tool accuracy ·
structured-output validity · cost · latency. Concrete numeric targets are an OPEN decision (OD-7) set with
provider/model selection; the AI gate cannot pass until targets are defined and met.

## AI gate (Master Source §54 — must pass with evidence)
Sentiment/topic/severity targets met · unsafe responses below limit · **no PII leakage on the test suite** ·
valid structured output · human approval active · cost limit active · kill switch active · retry does not
create duplicate actions.

## AI operations metrics (runtime — Master Source §21)
Agent runs, success/failure/retry rate, guardrail block rate, human approval/edit rate, confidence,
hallucination rate, PII leakage rate, unsafe response rate, latency, token usage, cost per run/tenant, tool
failure rate — surfaced on the AI Operations dashboard and observability (`.claude/rules/11`).

**Status:** evaluation baseline documented; numeric targets OPEN (OD-7). Implementation NOT STARTED.
