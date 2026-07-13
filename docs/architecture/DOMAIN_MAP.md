# Domain Map — Aish Agentic AI

Canonical: Master Source §17 (org structure), §36 (data tables), §53 (state models). Rule: `.claude/rules/07`, `08`, `10`.

## Organization hierarchy (Master Source §17)
```
Platform → Tenant → { Brand, Region, Branch, Department, Team, User, Customer,
                      Transaction, Survey, Feedback, Recovery Ticket,
                      Google Account, Google Location, Google Review }
```

## Core data domains (Master Source §36 — minimum tables)
- **Tenancy/billing:** tenants, tenant_settings, tenant_subscriptions, plans, plan_features, plan_entitlements, usage_records, billing_invoices
- **Org:** brands, regions, branches, departments, teams
- **Identity:** users, roles, permissions, model_has_roles, model_has_permissions
- **Customer:** customers, customer_consents, customer_identifiers, transactions, service_events
- **Survey:** surveys, survey_versions, survey_questions, survey_options, survey_campaigns, survey_invitations, survey_responses, survey_answers
- **Feedback:** feedback_items, feedback_topics, feedback_tags, feedback_sentiments, feedback_ai_analyses, feedback_attachments
- **Recovery:** recovery_tickets, ticket_assignments, ticket_comments, ticket_events, ticket_slas, ticket_resolutions, ticket_escalations
- **Google:** google_connections, google_accounts, google_locations, google_location_mappings, google_reviews, google_review_replies, google_sync_logs, google_webhook_events
- **Knowledge:** knowledge_bases, knowledge_documents, knowledge_chunks, knowledge_versions, knowledge_approvals
- **AI:** agent_runs, agent_steps, agent_tool_calls, agent_handoffs, agent_guardrail_results, agent_approvals, agent_failures, agent_cost_records
- **Notify/Integrate:** notifications, notification_rules, notification_deliveries, integrations, integration_credentials, integration_logs, webhooks, webhook_deliveries
- **Governance:** audit_logs, security_events, data_exports, data_deletion_requests

## Isolation & state invariants
Every business record carries `tenant_id` (and `branch_id` where relevant); isolation is mandatory on all
surfaces (`.claude/rules/03`). Truthful state models for connection, AI, reply, and ticket follow Master
Source §53 / PRD §16 (`.claude/rules/10`). Credentials encrypted, never plaintext (`.claude/rules/04`, `07`).

**Status:** domain baseline documented. Schema/migrations are OPEN (see `../product/OPEN_DECISIONS.md` OD-5).
