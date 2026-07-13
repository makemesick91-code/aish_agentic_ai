# Accessibility Baseline — Aish Agentic AI

| Field | Value |
|-------|-------|
| **Document** | Accessibility Baseline |
| **Product** | Aish Agentic AI |
| **Owner** | Aish Tech Solution |
| **Status** | PLANNING BASELINE — NOT IMPLEMENTED |
| **Target** | WCAG 2.2 AA |
| **Rule ref** | `.claude/rules/22` |
| **Canonical** | Master Source v2.4.0 §68 (Branding); PRD v1.3.0 |
| **AFR refs** | AFR-081, AFR-082, AFR-083, AFR-084, AFR-085, AFR-086 |
| **Version** | 1.0.0 (Step 4 planning) |

> Step 4 brand planning. Not final creative approval.

---

## 1. Non-claims (read first)

- No final brand, logo, or design is claimed; this is a planning baseline.
- Planning tokens are not implemented in any UI; contrast values are unverified in a running UI.
- No misleading AI-autonomy claim; no guaranteed-rating claim.
- Meeting this baseline is a **design-time requirement**; conformance is only proven with evidence from a
  real, tested UI.

---

## 2. Purpose and target

Establish the accessibility target and rules that every Aish Agentic AI surface **MUST** meet before UI
implementation. The target is **WCAG 2.2 AA**. Accessibility is a first-class brand and product quality
requirement, not an afterthought.

---

## 3. Contrast requirements

| Element | Minimum contrast | WCAG reference |
|---------|------------------|----------------|
| Body / normal text | **≥ 4.5:1** | 1.4.3 Contrast (Minimum) |
| Large text (≥18.66px bold or ≥24px) | **≥ 3:1** | 1.4.3 |
| Non-text UI (icons, controls, borders that convey state) | **≥ 3:1** | 1.4.11 Non-text Contrast |
| Focus indicator vs adjacent colors | **≥ 3:1** | 1.4.11 / 2.4.13 |

All contrast values in `tokens/brand-tokens.v1.json` are **candidates** and **MUST** be re-verified with
a contrast checker in a real UI during design review. No contrast claim is proven until then.

---

## 4. Keyboard and focus

| Requirement | Rule |
|-------------|------|
| Keyboard operability | All interactive elements MUST be operable by keyboard (2.1.1) |
| Visible focus | Keyboard focus MUST be clearly visible on every focusable element (2.4.7) |
| Focus not obscured | Focused element MUST NOT be hidden by sticky headers/overlays (2.4.11, WCAG 2.2) |
| No keyboard trap | Focus MUST be able to move away from any component (2.1.2) |
| Logical order | Focus/reading order MUST be logical and predictable (2.4.3) |
| Target size | Interactive targets SHOULD meet WCAG 2.2 target-size guidance (2.5.8) |

---

## 5. Color is not the only signal

Color **MUST NOT** be the sole means of conveying information, state, or action (WCAG 1.4.1). Every
color-coded state **MUST** carry a second signal.

| State | Color (candidate) | Additional signal (required) |
|-------|-------------------|------------------------------|
| Success | Success token | Check icon + "Success" / status text |
| Warning | Warning token | Triangle icon + "Warning" text |
| Danger / failure | Danger token | Alert icon + explicit text (e.g. "Publication failed") |
| Information | Information token | Info icon + label |
| Required field | — | Text label / asterisk + programmatic marking |

---

## 6. Text, content, and media

| Area | Rule |
|------|------|
| Text resize | Content MUST remain usable at 200% zoom without loss of function (1.4.4) |
| Reflow | Content MUST reflow at 320px width without horizontal scroll (1.4.10) |
| Text spacing | No loss of content when users adjust text spacing (1.4.12) |
| Alt text | Non-decorative images MUST have meaningful alternative text (1.1.1) |
| Language | Page language MUST be programmatically set; supports Indonesian + English (3.1.1) |
| Motion | Avoid content that flashes more than 3x/sec; respect reduced-motion preferences (2.3.1) |

---

## 7. Forms and errors

| Area | Rule |
|------|------|
| Labels | Every input MUST have a programmatic label (1.3.1, 4.1.2) |
| Error identification | Errors MUST be identified in text, not color alone (3.3.1) |
| Error suggestion | Provide correction guidance where known (3.3.3) |
| Truthful state | Error and status messaging MUST reflect true system state (no false success) |
| Redundant entry | Avoid re-asking for information already provided (3.3.7, WCAG 2.2) |

---

## 8. Design-review gate (mandatory before UI)

Accessibility conformance is gated. UI implementation **MUST NOT** proceed until a recorded design review
confirms:

- [ ] All text/background pairs meet the required contrast in a real UI.
- [ ] Non-text UI and focus indicators meet ≥3:1.
- [ ] Keyboard operability and visible focus verified on every interactive element.
- [ ] No information conveyed by color alone.
- [ ] Zoom (200%) and reflow (320px) verified.
- [ ] Forms have labels and text-based error identification.
- [ ] Findings recorded as evidence and linked to the release gate.

---

## 9. Testing approach (when UI exists)

| Test type | Purpose |
|-----------|---------|
| Automated contrast/audit | Catch obvious contrast and ARIA issues |
| Keyboard-only pass | Verify full operability without a mouse |
| Screen-reader spot check | Verify labels, roles, and order |
| Zoom / reflow check | Verify 200% and 320px behavior |
| Manual review | Verify color-plus-signal and truthful states |

Automated tools are necessary but **not sufficient**; manual review is required.

---

## 10. Governance

The WCAG 2.2 AA target is a permanent baseline; strengthening it is allowed, weakening it requires an
owner-approved Master Source update per `.claude/rules/12`. This baseline pairs with
`VISUAL_IDENTITY_BASELINE.md` (tokens) and `tokens/brand-tokens.v1.json`, and supports the truthful-state
principles referenced in `BRAND_VOICE.md` and `BRAND_FOUNDATION.md`.
