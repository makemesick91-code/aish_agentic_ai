# Requirements Traceability Matrix — Aish Agentic AI

Rule: `.claude/rules/09`, `12`. Maps PRD requirements → rule / derived doc → validation. No critical
requirement may be orphan. Scope: this is a **documentation-foundation** traceability baseline; application
test evidence is produced when implementation begins (NOT STARTED).

| PRD § | Requirement area | Rule | Derived doc | Validation status |
|-------|------------------|------|-------------|-------------------|
| §5, §10 | MVP goals & functional scope | 02 | `product/MVP_SCOPE.md` | Baseline documented |
| §7 | Personas | 01 | `product/PERSONAS_BASELINE.md` | Baseline documented |
| §9 | Product principles & constraints | 03,04,05 | `security/TENANT_ISOLATION.md` | Baseline documented |
| §10.1 | Authentication & identity | 04 | `architecture/DOMAIN_MAP.md` | Future test |
| §10.2–§10.3 | Tenant/branch, user/role/permission | 03 | `security/TENANT_ISOLATION.md` | Future test |
| §10.4–§10.6 | Survey builder, campaign, metrics | 02 | `product/MVP_SCOPE.md`, `architecture/EVENT_CATALOG.md` | Future test |
| §10.7–§10.8 | Feedback inbox, recovery ticket | 05,07 | `ai/AGENTIC_ARCHITECTURE.md` | Future test |
| §10.9 | Basic AI analysis | 05 | `ai/AI_EVALUATION_BASELINE.md` | Future test |
| §10.10–§10.12 | Google connection, sync, AI reply+approval | 06 | `integrations/google/*` | Future test |
| §10.13 | Knowledge base | 07 | `architecture/DOMAIN_MAP.md` | Future test |
| §10.14 | Dashboard & analytics | 10 | `architecture/DOMAIN_MAP.md` | Future test |
| §10.15 | Notification center | 08 | `architecture/EVENT_CATALOG.md` | Future test |
| §10.16 | Subscription, entitlement, metering | 07 | `architecture/DOMAIN_MAP.md` | Future test |
| §10.17 | Platform admin console | 01,07 | `product/PERSONAS_BASELINE.md` | Future test |
| §10.18 | Audit & security event | 07 | `security/SECURITY_FOUNDATION.md` | Future test |
| §11 | Canonical workflows | 08 | `architecture/EVENT_CATALOG.md` | Baseline documented |
| §12 | Agentic AI requirements | 05 | `ai/AGENTIC_ARCHITECTURE.md` | Baseline documented |
| §13 | Human approval matrix | 05 | `ai/HUMAN_APPROVAL_MATRIX.md` | Baseline documented |
| §14 | Data requirements | 07 | `architecture/DOMAIN_MAP.md` | Baseline documented |
| §15.1 | Security (NFR) | 04 | `security/SECURITY_FOUNDATION.md` | Future test |
| §15.2 | Privacy (NFR) | 04 | `security/PRIVACY_AND_PII.md` | Baseline documented |
| §15.3–§15.5 | Reliability, performance, availability/recovery | 11 | `operations/*` | Future test |
| §16 | Truthful system states | 10 | `architecture/DOMAIN_MAP.md`; `CLAUDE.md` §5 | Baseline documented |
| §17 | Google Review policy | 06 | `integrations/google/GOOGLE_REVIEW_POLICY.md` | Baseline documented |
| §18 | Integration requirements | 08 | `integrations/google/INTEGRATION_READINESS.md` | Baseline documented |
| §19 | Analytics & success metrics | 09,10 | `ai/AI_EVALUATION_BASELINE.md` | Baseline documented |
| §20 | Subscription baseline | 02,07 | `product/MVP_SCOPE.md` | Baseline documented |
| §21 | UI/UX information architecture | 10 | `architecture/DOMAIN_MAP.md` | Baseline documented |
| §22 | Technical baseline | 08 | `architecture/SYSTEM_CONTEXT.md` | Baseline documented |
| §23 | Testing requirements | 09 | `quality/TEST_STRATEGY.md` | Future test |
| §24 | Release gates | 09,13 | `quality/RELEASE_GATES.md` | Baseline documented |
| §25 | Risks & mitigation | 04,06,08 | `security/THREAT_MODEL_BASELINE.md` | Baseline documented |
| §26 | Dependencies | 08,15 | `tooling/MCP.md`, `integrations/google/INTEGRATION_READINESS.md` | Baseline documented |
| §27 | Implementation phases | 02 | `product/ROADMAP.md` | Baseline documented |
| §28 | Open decisions | 12 | `product/OPEN_DECISIONS.md` | Tracked (OD-1…OD-10) |
| §29–§30 | PRD acceptance & Definition of Done | 09 | `quality/TEST_STRATEGY.md`, `RELEASE_GATES.md` | Baseline documented |

**Orphan critical requirements: none.** Every PRD area maps to a rule and a derived doc.
"Future test" items are honestly marked pending application implementation (NOT STARTED); they are not
claimed complete. Verified for completeness by the QA/traceability reviewer and `check-foundation-coverage.sh`.
