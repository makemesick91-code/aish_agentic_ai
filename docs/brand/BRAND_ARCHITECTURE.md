# Brand Architecture — Aish Agentic AI

| Field | Value |
|-------|-------|
| **Document** | Brand Architecture |
| **Product** | Aish Agentic AI |
| **Owner / House brand** | Aish Tech Solution |
| **Status** | PLANNING BASELINE — NOT IMPLEMENTED |
| **Rule ref** | `.claude/rules/22` |
| **Canonical** | Master Source v2.4.0 §68 (Branding); PRD v1.3.0 |
| **AFR refs** | AFR-081, AFR-082, AFR-083, AFR-084, AFR-085, AFR-086 |
| **Model** | Branded house |
| **Version** | 1.0.0 (Step 4 planning) |

> Step 4 brand planning. Not final creative approval. No final brand or logo is claimed.

---

## 1. Non-claims (read first)

- No final brand, logo, or design is claimed; this is a planning baseline.
- Planning tokens are not implemented in any UI.
- No misleading AI-autonomy claim; the product is not fully autonomous.
- No guaranteed-rating claim; branding MUST NOT guarantee reputation outcomes.

---

## 2. Purpose

Define the brand architecture model for **Aish Tech Solution** and its products, so that the product
**Aish Agentic AI** is named, positioned, and governed consistently, and so that future products extend
the portfolio without renaming or diluting the existing product.

---

## 3. Chosen model: Branded house

Aish Tech Solution uses a **branded-house** architecture: one master house brand endorses a family of
clearly named products that share visual, verbal, and governance systems.

```
Aish Tech Solution  (house brand — owner)
        |
        +-- Aish Agentic AI        (active product)
        +-- Aish POS               (future / planned)
        +-- Aish Finance           (future / planned)
        +-- Aish Agentic OS        (future / planned platform layer)
```

| Model considered | Decision | Rationale |
|------------------|----------|-----------|
| Branded house (chosen) | **ADOPTED** | Shared trust, shared design system, efficient governance, clear portfolio |
| House of brands | Rejected | Fragments trust, duplicates governance, no shared equity |
| Endorsed brands | Rejected | Weaker cohesion than the shared portfolio Aish Tech Solution wants |
| Hybrid | Deferred | Only if a future acquisition requires it, via ADR + Master Source update |

---

## 4. Naming system

| Layer | Rule | Example |
|-------|------|---------|
| House brand | Always **Aish Tech Solution**; never abbreviated to only "Aish" in legal/owner contexts | Aish Tech Solution |
| Product prefix | Every product name begins with **Aish** | Aish Agentic AI |
| Product descriptor | A short capability descriptor follows the prefix | "Agentic AI", "POS", "Finance", "Agentic OS" |
| Product name stability | A shipped product name **MUST NOT** be renamed without an owner decision + Master Source update | Aish Agentic AI stays Aish Agentic AI |
| Casing | Title case; "AI" and "OS" uppercase | Aish Agentic AI, Aish Agentic OS |

### 4.1 Naming rules for future products

- Future products **MUST** use the `Aish <Descriptor>` pattern and **MUST NOT** reuse the exact string
  "Aish Agentic AI" for a different product.
- A new product **MUST NOT** trigger a rename of Aish Agentic AI or reassign its equity.
- "Aish Agentic OS" is reserved as a potential platform layer; it does **not** supersede or rename
  Aish Agentic AI, which remains a distinct product.
- Descriptors **MUST** describe the actual product category and **MUST NOT** overclaim (e.g. no
  "Autonomous" descriptor that implies full autonomy).

---

## 5. Relationship and endorsement rules

| Surface | Endorsement expression |
|---------|------------------------|
| Marketing / landing | "Aish Agentic AI — by Aish Tech Solution" |
| Product UI header | Product name primary; house brand as subtle endorsement in footer/about |
| Legal / invoices / ToS | Aish Tech Solution as legal owner/entity |
| OAuth consent | Product name (Aish Agentic AI) with owner (Aish Tech Solution) disclosed |
| API docs | Product name primary; owner in metadata |

The house endorsement **MUST NOT** overpower the product identity in-product, and the product identity
**MUST NOT** hide the owner in legal/consent contexts.

---

## 6. Shared vs product-specific systems

| System | Shared across portfolio | Product-specific |
|--------|-------------------------|------------------|
| Logo master + wordmark grammar | Yes (house rules) | Product lockup |
| Color tokens | Shared base scale | Product accent within house palette |
| Typography (Inter) | Yes | — |
| Voice principles | Yes | Product domain nuances |
| Accessibility target (WCAG 2.2 AA) | Yes | — |
| Domain terminology | — | Yes (per product) |

Shared systems are defined in the sibling planning documents `VISUAL_IDENTITY_BASELINE.md`,
`BRAND_VOICE.md`, `ACCESSIBILITY_BASELINE.md`, and `LOGO_AND_ASSET_GOVERNANCE.md`, with tokens in
`tokens/brand-tokens.v1.json`.

---

## 7. Portfolio governance

| Governance item | Rule | Authority |
|-----------------|------|-----------|
| Adding a product to the portfolio | Requires owner decision + Master Source update | Aish Tech Solution |
| Renaming an existing product | Prohibited without owner decision + Master Source update | Aish Tech Solution |
| Cross-product asset reuse | Allowed only via house design system, checksum-tracked | See `LOGO_AND_ASSET_GOVERNANCE.md` |
| Trademark filing | Out of scope for this planning doc; flagged as a future legal step | Aish Tech Solution / legal |
| Conflicts | Resolved by canonical authority order | Master Source v2.4.0 |

---

## 8. Positioning consistency guardrails

- Every product page **MUST** reflect its true category and **MUST NOT** reduce Aish Agentic AI to a
  survey app, review generator, chatbot, or Google Review tool.
- Portfolio messaging **MUST NOT** imply that any Aish product is fully autonomous.
- Portfolio messaging **MUST NOT** guarantee outcomes for any product.

---

## 9. Decision record

| Decision | Status |
|----------|--------|
| Branded-house model adopted | APPROVED WORKING BASELINE (planning) |
| `Aish <Descriptor>` naming pattern | APPROVED WORKING BASELINE (planning) |
| Aish Agentic AI name is stable and permanent | Fixed; owner-decision-gated |
| Future products (Aish POS, Aish Finance, Aish Agentic OS) named as planning placeholders | Not yet products; naming reserved |

Material changes follow `.claude/rules/12`. See also `BRAND_FOUNDATION.md` for identity decisions this
architecture inherits.
