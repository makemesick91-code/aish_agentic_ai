# Event Catalog — Aish Agentic AI

Canonical: Master Source §19 (customer journey), §35 (events), §38 (Google workflows). Rule: `.claude/rules/08`.

## Canonical events (Master Source §35)
| Event | Emitted when | Typical downstream |
|-------|--------------|--------------------|
| `TransactionCompleted` / `VisitCompleted` / `ServiceCompleted` | A transaction or service finishes | Campaign engine selects survey |
| `SurveyInvitationCreated` / `SurveyInvitationSent` | Invitation created/dispatched | Delivery tracking, reminders |
| `SurveyResponseSubmitted` | Customer submits a response | Metric calc, feedback analysis |
| `FeedbackAnalyzed` | AI completes sentiment/topic/severity | Rule engine, recovery decision |
| `HighRiskFeedbackDetected` | Severity/Risk agent flags high risk | Human approval, escalation |
| `RecoveryTicketCreated` / `Assigned` / `Escalated` / `Resolved` | Recovery lifecycle transitions | SLA, notifications, analytics |
| `GoogleReviewSynced` | Review pulled from Google | AI analysis, reply draft |
| `GoogleReviewReplyDrafted` / `Approved` / `Published` | Reply lifecycle | Guardrail, approval, publish, audit |
| `AgentRunFailed` | An agent run fails | Dead-letter workflow, alert |
| `SLAApproaching` / `SLABreached` | SLA timers | Escalation, notifications |
| `SubscriptionLimitReached` | Usage crosses plan limit | Metering, billing, notifications |
| `GoogleConnectionExpired` | OAuth/token expiry | Reauthorization prompt, alert |

## Workflow rules
- Heavy work and external integrations MUST run on the queue with tenant context (`.claude/rules/03`, `08`).
- Google review sync uses incremental sync, idempotency, retry, rate-limit handling, sync cursor, and
  error logs (Master Source §38). Reply publication follows: review → AI analysis → draft → guardrail →
  staff review → approver → send → API response recorded → publication state monitored.
- High-risk workflow requires human approval before any public action (`.claude/rules/05`, `06`).

**Status:** event baseline documented. Implementation NOT STARTED.
