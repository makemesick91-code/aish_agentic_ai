# Visual Identity Baseline — Aish Agentic AI

| Field | Value |
|-------|-------|
| **Document** | Visual Identity Baseline |
| **Product** | Aish Agentic AI |
| **Owner** | Aish Tech Solution |
| **Status** | PLANNING BASELINE — NOT IMPLEMENTED |
| **Token status** | PLANNING TOKENS — NOT IMPLEMENTED IN UI |
| **Rule ref** | `.claude/rules/22` |
| **Canonical** | Master Source v2.4.0 §68 (Branding); PRD v1.3.0 |
| **AFR refs** | AFR-081, AFR-082, AFR-083, AFR-084, AFR-085, AFR-086 |
| **Token source** | `tokens/brand-tokens.v1.json` |
| **Version** | 1.0.0 (Step 4 planning) |

> Step 4 brand planning. Not final creative approval.

---

## 1. Non-claims (read first)

- No final brand, logo, or design is claimed; this is a planning baseline.
- **PLANNING TOKENS — NOT IMPLEMENTED IN UI.** No token here is wired into a running interface.
- Contrast ratios are candidates and **NOT VERIFIED** in a running UI; a design-review gate is required.
- No misleading AI-autonomy claim; no guaranteed-rating claim.

---

## 2. Purpose and source of truth

This document **summarizes** the machine-readable planning tokens. The authoritative token values live
in `tokens/brand-tokens.v1.json` and **MUST NOT** be duplicated or forked here — this file references
that token file by name and describes its groups. If the two ever disagree, the token file is the source
for values and this document is corrected.

All values are a **PLANNING BASELINE — NOT IMPLEMENTED**. They are candidates for a future design
system, not shipped styles.

---

## 3. Color token groups (summary)

The token file defines the following color groups. Hex values are planning candidates; see
`tokens/brand-tokens.v1.json` for exact values.

| Group | Role | Notes |
|-------|------|-------|
| `primary` | Primary brand / primary actions | Candidate contrast on white noted as ~4.7:1 (verify) |
| `secondary` | Secondary brand / supporting actions | Teal-family supporting color |
| `accent` | Highlights, sparing use | Use sparingly; not for large fills |
| `success` | Positive / success state | Paired with non-color signal |
| `warning` | Attention / warning state | Paired with non-color signal |
| `danger` | Destructive / failure state | Used for truthful failure states |
| `information` | Neutral notice / info | Distinct from primary action |
| `neutral` (50–900) | Text, borders, surfaces scale | 10-step neutral scale |
| `background` | Page background (light/dark) | Theme-aware |
| `surface` | Card / surface background (light/dark) | Theme-aware |
| `border` | Dividers / borders (light/dark) | Theme-aware |
| `text` | Text (light/dark/muted) | Muted used for secondary text |
| `focusRing` | Keyboard focus indicator | Target ≥3:1 vs adjacent colors |

### 3.1 Color usage rules

- Color **MUST NOT** be the sole signal for state; pair with icon, label, or text (see
  `ACCESSIBILITY_BASELINE.md`).
- Accent is for emphasis only and **MUST NOT** dominate a screen.
- Danger styling is reserved for genuine failure/destructive contexts and truthful failed states.
- All pairings **MUST** be re-verified for WCAG 2.2 AA contrast during design review before UI use.

---

## 4. Typography (summary)

| Aspect | Planning value | Notes |
|--------|----------------|-------|
| Primary UI font | **Inter** | Chosen for legibility and broad coverage |
| Fallback stack | Inter, system-ui, -apple-system, Segoe UI, Roboto, Helvetica Neue, Arial, Noto Sans, sans-serif | System fallback for resilience |
| Display use | Inter (Display optical size) | Only if a genuine display need is confirmed |
| Numerics | Inter with **tabular-nums** | For tables, metrics, and financial/billing data |
| Internationalization | Latin + Indonesian coverage; verify Noto Sans fallback for extended ranges | Indonesia → Global |
| Licensing | Prefer OFL / open-source; **self-host** fonts | No third-party font CDN calls, for privacy |
| Recommendation | APPROVED WORKING BASELINE — subject to design review; not final | — |

### 4.1 Typography rules

- Fonts **MUST** be self-hosted; the UI **MUST NOT** call a third-party font CDN (privacy requirement).
- Tabular numerics **MUST** be used for numeric tables, metrics, and billing figures for alignment and
  readability.
- Font licensing **MUST** be OFL/open-source compatible; any non-open font requires owner + legal
  approval and a Master Source update.

---

## 5. Deferred systems

Per the token file, the following are intentionally deferred to the design-system implementation phase:

| System | Status |
|--------|--------|
| Spacing scale | Deferred |
| Radius scale | Deferred |
| Elevation / shadow scale | Deferred |
| Component specs | Deferred |
| Motion / animation | Deferred |

These will be defined when the design system is implemented, not in this planning baseline.

---

## 6. Theming

| Theme | Background | Surface | Text |
|-------|-----------|---------|------|
| Light | Light background token | Light surface token | Dark text token |
| Dark | Dark background token | Dark surface token | Light text token |

Both themes **MUST** meet WCAG 2.2 AA contrast once implemented; current values are candidates only.

---

## 7. Verification gate before implementation

No token in this baseline may be shipped to a UI until all of the following pass in a **design review**:

- [ ] Every text/background pair meets ≥4.5:1 (body) / ≥3:1 (large text, non-text UI) in a real UI.
- [ ] Focus ring meets ≥3:1 against adjacent colors and is visible on keyboard focus.
- [ ] Color-plus-signal verified for every state (no color-only signaling).
- [ ] Fonts self-hosted with OFL/open licensing confirmed.
- [ ] Light and dark themes both verified.
- [ ] Design review recorded as evidence.

---

## 8. Governance

Token changes are versioned in `tokens/brand-tokens.v1.json` (semantic version). Material visual-identity
changes follow `.claude/rules/12`. Accessibility requirements are detailed in `ACCESSIBILITY_BASELINE.md`;
logo/asset handling is in `LOGO_AND_ASSET_GOVERNANCE.md`; identity basis is `BRAND_FOUNDATION.md`.

**Reminder:** this is a PLANNING BASELINE — NOT IMPLEMENTED, and the tokens are PLANNING TOKENS — NOT
IMPLEMENTED IN UI.
