# Domain Candidate Evaluation — Aish Agentic AI

**Status:** PLANNING BASELINE — NOT IMPLEMENTED. Domain ownership state: **NOT OWNED** — NOT CLAIMED.
**Rule refs:** `.claude/rules/21`; supporting `.claude/rules/01`, `04`.
**Canonical refs:** Master Source v2.4.0 §68 (Domain strategy); PRD v1.3.0.
**AFR refs:** AFR-073, AFR-074, AFR-078.
**Baseline date:** 2026-07-13 · **Availability method:** RDAP (point-in-time).

---

## 1. Purpose

Score the seven candidate domains for **Aish Agentic AI** across brand, technical, operational, and
non-legal-risk dimensions so the primary and defensive selections in `DOMAIN_STRATEGY.md` are traceable and
defensible. This is a **planning evaluation** — it selects nothing binding, buys nothing, and claims no ownership.

## 2. Non-claims

- **No domain is owned or purchased.** Ownership state: **NOT OWNED**.
- Availability was verified **2026-07-13** via **RDAP** and is **point-in-time**; it may have changed since.
  RDAP "not found" does not guarantee registerability (a name may be premium, registry-reserved, or on hold).
- This document is **not legal advice**. Trademark/confusion notes (§6) are **non-legal screening only**.
- No DNS is mutated and no TLS is issued. Registration is **OUT OF SCOPE** for Step 4 and **NOT STARTED**.

Point-in-time evidence: `../evidence/domain/DOMAIN_AVAILABILITY_VERIFICATION.md`.

## 3. Candidate set

| # | Candidate | Intended role |
|---|-----------|---------------|
| 1 | `aishagentic.ai` | Preferred primary |
| 2 | `aishagenticai.com` | First fallback / `.com` twin / email |
| 3 | `aishagentic.com` | Second fallback / short `.com` twin |
| 4 | `aishcx.ai` | Defensive (brand-adjacent CX, AI) |
| 5 | `aishcx.com` | Defensive (brand-adjacent CX, `.com`) |
| 6 | `getaish.ai` | Defensive (marketing / CTA) |
| 7 | `aishcustomer.ai` | Defensive (descriptive) |

## 4. Scoring method

- Each dimension is scored **High / Medium / Low** (qualitative) or with a concrete value (length, cost band).
- For brand/technical dimensions, **High = better**. For risk dimensions (typo risk, trademark/confusion risk),
  **Low = better** (less risk).
- Availability is the **point-in-time** RDAP result from 2026-07-13 and MUST be re-verified before registration.
- Scores are planning judgments, not guarantees; they inform the ordered decisions in `DOMAIN_STRATEGY.md`.

### 4.1 Dimension definitions

| Dimension | What it measures | Direction |
|-----------|------------------|-----------|
| Availability (point-in-time) | RDAP result at 2026-07-13; AVAILABLE vs REGISTERED | Informational |
| Length | Total characters including the TLD; shorter is easier to type/recall | Lower better |
| Spelling ease | Likelihood a listener spells it right first time | Higher better |
| Pronounceability | How cleanly the name can be spoken aloud (word-of-mouth) | Higher better |
| Typo risk | Chance of a common mistype (doubled letters, ambiguous strings) | Lower better |
| Product fit | Whether the string reads as the product "Aish Agentic AI" | Higher better |
| Global fit | Suitability across the target global market | Higher better |
| Indonesia fit | Suitability in the launch market (Indonesia) | Higher better |
| Email suitability | Trust/deliverability of email sent from the domain | Higher better |
| SEO clarity | How unambiguously the name maps to brand search intent | Higher better |
| Trademark/confusion risk (non-legal) | Informal likelihood of brand confusion; **not** a legal opinion | Lower better |
| OAuth suitability | Fit as a stable OAuth redirect host (see OAuth plan) | Higher better |
| Subdomain suitability | Fit as a base for `app.`, `api.`, etc. | Higher better |
| Defensive priority | Role in the ordered defensive set | Rank |
| Cost category | Registration/renewal cost band (`.ai` higher, `.com` standard) | Lower better |

### 4.2 Weighting rationale

For the **primary** decision, brand read (product fit), category signal, and long-term stability dominate; cost is
secondary because the primary is a strategic asset. For **email/defensive** roles, deliverability and `.com` trust
dominate. This is why `aishagentic.ai` wins the primary role despite higher cost, while `aishagenticai.com` and
`aishagentic.com` win the email/`.com`-twin roles despite a neutral category signal.

| Role | Dominant dimensions | Winner |
|------|---------------------|--------|
| Primary brand | Product fit, category signal, pronounceability, subdomain/OAuth suitability | `aishagentic.ai` |
| Email / `.com` twin | Email suitability, SEO clarity, low typo risk, cost | `aishagenticai.com`, `aishagentic.com` |
| Defensive | Confusion coverage, cost efficiency | `aishcx.ai`, `aishcx.com`, `getaish.ai`, `aishcustomer.ai` |

## 5. Candidate evaluation table

| Dimension | `aishagentic.ai` | `aishagenticai.com` | `aishagentic.com` | `aishcx.ai` | `aishcx.com` | `getaish.ai` | `aishcustomer.ai` |
|-----------|------------------|---------------------|-------------------|-------------|--------------|--------------|-------------------|
| Availability (point-in-time, 2026-07-13) | AVAILABLE | AVAILABLE | AVAILABLE | AVAILABLE | AVAILABLE | AVAILABLE | AVAILABLE |
| Length (chars incl. TLD) | 14 | 17 | 15 | 9 | 10 | 10 | 16 |
| Spelling ease | High | Medium | High | Medium | Medium | High | Medium |
| Pronounceability | High | Medium | High | Low (letters "cx") | Low ("cx") | High | Medium |
| Typo risk (Low=better) | Low | Medium (double "ai") | Low | Medium | Medium | Low | Medium |
| Product fit (reads as product) | High | High | High | Medium | Medium | Medium | Medium |
| Global fit | High | High | High | Medium | Medium | Medium | Medium |
| Indonesia fit | High | High | High | Medium | Medium | Medium | Medium |
| Email suitability | Medium (`.ai`) | High (`.com`) | High (`.com`) | Low | Medium | Low | Low |
| SEO clarity | High | High | High | Medium | Medium | Medium | High |
| Trademark/confusion risk — non-legal (Low=better) | Low | Low | Low | Medium | Medium | Medium | Medium |
| OAuth suitability | High | High | High | Medium | Medium | Medium | Medium |
| Subdomain suitability | High | High | High | High | High | Medium | Medium |
| Defensive priority | Primary | D1 | D2 | D3 | D4 | D5 | D6 |
| Cost category | Higher (`.ai`, 2-yr min) | Standard (`.com`) | Standard (`.com`) | Higher (`.ai`) | Standard (`.com`) | Higher (`.ai`) | Higher (`.ai`) |

### 5.1 Notes per candidate

| Candidate | Summary judgment |
|-----------|------------------|
| `aishagentic.ai` | Best brand read and clearest AI-category signal; higher cost and 2-year minimum term are the trade-off. Selected as preferred primary. |
| `aishagenticai.com` | Strongest email/deliverability profile; the doubled "…ai.com" slightly raises typo risk. First fallback and primary `.com` twin. |
| `aishagentic.com` | Clean short `.com`; strong email and SEO. Second fallback and short `.com` twin. |
| `aishcx.ai` | Short and memorable but "cx" is not universally pronounced; brand-adjacent defensive only. |
| `aishcx.com` | `.com` counterpart of the CX handle; defensive, redirect-only. |
| `getaish.ai` | Useful marketing/CTA variant; weaker as a primary because "get" dilutes the product read. |
| `aishcustomer.ai` | Descriptive and SEO-clear but longer; defensive, redirect-only. |

## 6. Non-legal trademark / confusion screening note

**This is NOT legal advice.** The following is a lightweight, non-legal screening intended only to flag obvious
risk before qualified counsel review:

- The string "Aish" is short and brand-adjacent; a formal trademark clearance search across relevant classes and
  jurisdictions (including Indonesia and target global markets) **MUST** be performed by qualified counsel before
  any binding registration or public launch.
- Candidates combining "aish" with generic terms ("cx", "customer", "get") carry **medium** non-legal confusion
  risk because the distinctive element is a single short mark plus a descriptor.
- No candidate should be treated as clear of third-party marks on the basis of this screening. RDAP availability
  says nothing about trademark status.
- Brand-impersonation and look-alike risk (e.g. homoglyph or hyphen variants) is addressed operationally by the
  small defensive set in `DOMAIN_STRATEGY.md`, not by exhaustive buying.

## 7. Recommendation

| Rank | Domain | Recommended role |
|------|--------|------------------|
| 1 | `aishagentic.ai` | Register as preferred primary (pending re-verified availability + counsel clearance). |
| 2 | `aishagenticai.com` | Register as `.com` twin / email / first fallback. |
| 3 | `aishagentic.com` | Register as short `.com` twin / second fallback. |
| 4–7 | `aishcx.ai`, `aishcx.com`, `getaish.ai`, `aishcustomer.ai` | Register defensively in priority order; redirect-only. |

All recommendations are **planning outputs**. They authorize no purchase. Availability **MUST** be re-checked via
RDAP immediately before any registration, and counsel clearance **MUST** precede binding commitment.

## 7a. Re-verification obligation

Because availability is **point-in-time**, this evaluation's AVAILABLE results **MUST** be re-confirmed via RDAP
immediately before any registration attempt. A candidate marked AVAILABLE on 2026-07-13 may since have become
registered, premium-priced, registry-reserved, or placed on hold. The registrar checkout is the only binding
confirmation of registerability, and counsel clearance (§6) must precede binding commitment.

| Trigger for re-verification | Action |
|-----------------------------|--------|
| Before registering the primary | Re-run RDAP for `aishagentic.ai`; if unavailable, follow the fallback order. |
| Before registering any fallback | Re-run RDAP for the fallback; record any change. |
| Before registering any defensive domain | Re-run RDAP for that domain in priority order. |
| At each renewal-planning cycle | Re-confirm any not-yet-registered planned domain. |

## 8. Status

| Item | State |
|------|-------|
| Candidate evaluation | **PLANNING BASELINE — NOT IMPLEMENTED** |
| Availability basis | Point-in-time RDAP, 2026-07-13 |
| Ownership | **NOT OWNED** — NOT CLAIMED |
| Registration | OUT OF SCOPE (Step 4) — NOT STARTED |
