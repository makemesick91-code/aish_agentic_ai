# ADR 0008 — Step 2 Persona and Pilot Use Case Baseline

- **Status:** Accepted (2026-07-13, Asia/Makassar)
- **Rule:** `.claude/rules/16`, `17`, `18`, `19` · **Canonical:** Master Source v2.2.0 §6; PRD v1.1.0; Persona and Pilot Use Cases v1.0.0

## Context
Step 1 established the documentation and Claude-rules foundation (merged, GO tagged
`aish-agentic-ai-docs-foundation-v1.0.0-go`). Step 2 must define who the pilot serves and how the first pilot
operates, without starting application implementation. The product owner supplied canonical Step 2 sources:
Master Source v2.2.0, PRD v1.1.0, and Persona and Pilot Use Cases v1.0.0.

## Decision
Adopt the Step 2 persona and pilot baseline as canonical living documentation:

1. **Canonical import.** Set living copies Master Source v2.2.0, PRD v1.1.0, and Persona & Pilot Use Cases
   v1.0.0; preserve the originals byte-for-byte in `docs/canonical/source/` with updated checksums. Historical
   versions (Master Source v2.1.1/v2.0.0, PRD v1.0.1) are retained and marked superseded, never deleted.
2. **Pilot decisions.** First pilot tenant **Klinik Gigi Daengtisia**; recommended first branch **Daengtisia
   Pusat** (a recommendation subject to readiness verification, changeable via the decision log without
   narrowing product scope). Five primary personas plus supporting/external/system personas. Invitation and
   survey baselines, healthcare data boundary, mandatory human approval, no review gating, manual fallback,
   truthful external states, severity/SLA, and compensation authority are fixed per the persona document.
3. **Use cases.** P0 use cases UC-P0-01..16 are fully specified (actor, flows, data, scope, audit, truthful
   states, security/privacy constraints, acceptance, evidence, manual fallback) with no orphan critical
   requirement.
4. **Metrics & gates.** Operational targets are pilot **hypotheses**, not results; hard safety/correctness
   gates are mandatory; pilot GO/WATCH/NO-GO is defined and distinct from the documentation GO tag.
5. **Governance.** New enforceable rules `.claude/rules/16`–`19`, a Step 2 coverage matrix, a Step 2 coverage
   validation gate, extended version-consistency/query-smoke/CI, and Step 2 release evidence.

## Alternatives considered
- **Versioned canonical filenames** (`MASTER_SOURCE_...v2.2.0.md` as the working path) — rejected: the repo
  convention is a single living `MASTER_SOURCE.md` with preserved originals in `source/`.
- **Fold Step 2 into existing rules only** — rejected: dedicated rules 16–19 keep pilot decisions
  discoverable and enforceable without overloading foundation rules.
- **Start Step 3 architecture now** — rejected: reliability-before-autonomy and evidence-based sequencing;
  only a roadmap pointer to Step 3 is added.

## Consequences
Pilot planning is traceable to canonical sources and machine-checked. The core product stays generic; clinic
specifics live at the integration/configuration boundary. Step 3 (application architecture + ADRs) begins
only after the Step 2 release is merged and GO tagged.

## Security & privacy impact
Reinforces healthcare-data prohibition into AI prompts/public replies, untrusted-input handling, mandatory
human approval, anti-gating, tenant isolation on all surfaces, and truthful external states. No new
production or runtime claim is made.

## Supersession
Superseded only by a higher-version Master Source update. Pilot tenant/scope and safety constraints are
permanent; the recommended branch may change via a recorded decision after readiness verification.
