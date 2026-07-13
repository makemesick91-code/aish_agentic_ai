# Logo and Asset Governance — Aish Agentic AI

| Field | Value |
|-------|-------|
| **Document** | Logo and Asset Governance |
| **Product** | Aish Agentic AI |
| **Owner** | Aish Tech Solution |
| **Status** | PLANNING BASELINE — NOT IMPLEMENTED |
| **Rule ref** | `.claude/rules/22` |
| **Canonical** | Master Source v2.4.0 §68 (Branding); PRD v1.3.0 |
| **AFR refs** | AFR-081, AFR-082, AFR-083, AFR-084, AFR-085, AFR-086 |
| **Version** | 1.0.0 (Step 4 planning) |

> Step 4 brand planning. Not final creative approval.

---

## 1. Non-claims (read first)

- **No final logo exists and none is claimed.** This document governs how a logo will be produced,
  approved, and controlled; it does **not** assert that a completed, approved logo is available.
- Planning tokens are not implemented in any UI.
- No misleading AI-autonomy claim; no guaranteed-rating claim.
- Any asset described here is a **placeholder specification**, **not final** artwork.

---

## 2. Purpose

Define ownership, formats, construction rules, prohibited uses, versioning, integrity, and the approval
workflow for the Aish Agentic AI logo and brand assets, so that when a logo is created it is controlled
and consistent from day one.

---

## 3. Ownership

| Item | Owner |
|------|-------|
| Master logo and all derivatives | **Aish Tech Solution** |
| Approval authority | Aish Tech Solution (product owner) |
| Asset custody / repository | Aish Tech Solution, in the canonical repository under version control |

The master logo is a house-brand asset (branded-house model; see `BRAND_ARCHITECTURE.md`).

---

## 4. Planned source-file location

Final artwork does **not** yet exist. When produced and approved, assets are planned to live under a
brand assets path in the canonical repository, for example a planned `docs/brand/assets/` tree with a
`source/` master, exported `png/`, and a `checksums/` record. This is a planned location, not a claim
that files are present.

---

## 5. Approved formats

| Format | Use | Rule |
|--------|-----|------|
| **SVG (master)** | Source of truth for scalable rendering | Master vector; all exports derive from it |
| **PNG (exports)** | Raster use where SVG is unsupported | Exported at defined sizes; transparent background |
| ICO / PNG favicon | Browser tab / app icon | Derived from the icon mark |

Other formats require owner approval. Editable design-tool source is retained by the owner as the true
master alongside the exported SVG.

---

## 6. Logo variants

| Variant | Purpose |
|---------|---------|
| Full lockup (mark + wordmark) | Primary brand signature |
| Wordmark only | Text-first contexts |
| Icon mark | Compact contexts, app icon base |
| Favicon | Browser tab / bookmark |
| Light-background variant | For light surfaces |
| Dark-background variant | For dark surfaces |
| Monochrome (single color) | Faxes, stamps, engraving, single-ink printing |

Light/dark and monochrome variants **MUST** all derive from the same approved master.

---

## 7. Construction rules

| Rule | Requirement |
|------|-------------|
| Safe area / clear space | A defined minimum clear space (e.g. proportional to the mark height) MUST surround the logo; nothing intrudes |
| Minimum size | A minimum rendered size MUST be defined for full lockup and icon so the logo stays legible |
| Alignment | Mark and wordmark spacing/alignment fixed by the master; MUST NOT be re-spaced |
| Color | Only approved brand token colors and approved monochrome MUST be used |
| Background | MUST meet contrast/legibility on its intended background |

---

## 8. Prohibited uses

The logo **MUST NOT** be:

- Stretched, squashed, skewed, or otherwise distorted.
- Rotated, or given unapproved drop shadows, glows, gradients, or outlines.
- Recolored outside the approved palette or placed on low-contrast/busy backgrounds.
- Reconstructed, re-typed, or re-spaced from memory instead of using the master file.
- Cropped, partially obscured, or combined into a new composite mark.
- Used to imply endorsement, autonomy, or guaranteed outcomes.

---

## 9. Versioning and integrity

| Aspect | Rule |
|--------|------|
| Versioning | Each approved asset set carries a semantic version; superseded versions are retained, never deleted |
| Checksums | Every approved asset MUST have a recorded checksum (e.g. SHA-256) for integrity verification |
| Change history | Asset changes are logged with version, date, approver, and reason |
| Drift check | Distributed assets MUST match the checksum of the approved master |

---

## 10. AI-generated artwork policy

- AI-generated logo concepts **MAY** be used for exploration only.
- An AI-generated logo **MUST NOT** be declared final or shipped without explicit owner **approval**.
- Any final artwork **MUST** pass the approval workflow below; until then, **no final logo is claimed**
  and assets are **not final**.
- Licensing and originality of any AI-assisted concept **MUST** be reviewed before approval.

---

## 11. Approval workflow

| Step | Action | Gate |
|------|--------|------|
| 1 | Brief and concept exploration | Aligns with `BRAND_FOUNDATION.md` positioning |
| 2 | Draft master (SVG) prepared | Construction rules applied |
| 3 | Accessibility/contrast check | Meets `ACCESSIBILITY_BASELINE.md` on intended backgrounds |
| 4 | Owner review | Aish Tech Solution **approval** required and recorded |
| 5 | Version + checksum recorded | Integrity evidence stored |
| 6 | Publish to asset repository | Only after recorded approval |

No asset is "approved" or "final" without step 4 recorded owner approval and step 5 integrity evidence.
This document explicitly does **not** claim that any logo has completed this workflow — it is **not
final**.

---

## 12. Governance

Material asset/logo decisions follow `.claude/rules/12`. This document pairs with
`VISUAL_IDENTITY_BASELINE.md`, `ACCESSIBILITY_BASELINE.md`, `BRAND_ARCHITECTURE.md`, and the token file
`tokens/brand-tokens.v1.json`. Identity basis is `BRAND_FOUNDATION.md`.
