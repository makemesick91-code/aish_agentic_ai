<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\NotificationCategory;
use App\Enums\NotificationChannel;

/**
 * Stable allowlist of foundation notification types. A type that is not in this registry
 * cannot be dispatched (the dispatcher fails closed). No type is defined for a business
 * module that does not exist yet (rule 02, rule 31; SPRINT-SF-05 §8.4).
 */
enum NotificationType: string
{
    case TenantInvitationCreated = 'tenant.invitation.created';
    case TenantInvitationAccepted = 'tenant.invitation.accepted';
    case MembershipActivated = 'membership.activated';
    case MembershipSuspended = 'membership.suspended';
    case MembershipRevoked = 'membership.revoked';
    case RoleAssignmentChanged = 'role.assignment.changed';
    case TenantSuspended = 'tenant.suspended';
    case TenantReactivated = 'tenant.reactivated';
    case SubscriptionTrialStarted = 'subscription.trial.started';
    case SubscriptionStatusChanged = 'subscription.status.changed';
    case SubscriptionEntitlementChanged = 'subscription.entitlement.changed';
    case SecurityAuthenticationAlert = 'security.authentication.alert';
    case SurveyResponseCompleted = 'survey.response.completed.internal';
    case FeedbackAssigned = 'feedback.assigned';
    case FeedbackUnassigned = 'feedback.unassigned';
    case FeedbackStatusChanged = 'feedback.status.changed';
    case FeedbackExportReady = 'feedback.export.ready';
    case FeedbackExportFailed = 'feedback.export.failed';

    public function category(): NotificationCategory
    {
        return match ($this) {
            self::SurveyResponseCompleted => NotificationCategory::Survey,

            self::FeedbackAssigned,
            self::FeedbackUnassigned,
            self::FeedbackStatusChanged,
            self::FeedbackExportReady,
            self::FeedbackExportFailed => NotificationCategory::Feedback,

            self::TenantInvitationCreated,
            self::TenantInvitationAccepted,
            self::MembershipActivated,
            self::MembershipSuspended,
            self::MembershipRevoked,
            self::RoleAssignmentChanged => NotificationCategory::Membership,

            self::TenantSuspended,
            self::TenantReactivated => NotificationCategory::Tenant,

            self::SubscriptionTrialStarted,
            self::SubscriptionStatusChanged,
            self::SubscriptionEntitlementChanged => NotificationCategory::Subscription,

            self::SecurityAuthenticationAlert => NotificationCategory::Security,
        };
    }

    /**
     * Critical notifications are mandatory: a preference MUST NOT silence them (rule 31).
     */
    public function isCritical(): bool
    {
        return match ($this) {
            self::SecurityAuthenticationAlert,
            self::TenantSuspended,
            self::MembershipSuspended,
            self::MembershipRevoked => true,
            default => false,
        };
    }

    /** @return list<NotificationChannel> */
    public function defaultChannels(): array
    {
        return match ($this) {
            // Entitlement changes and internal survey/feedback signals are low-signal; in-app only.
            self::SubscriptionEntitlementChanged,
            self::SurveyResponseCompleted,
            self::FeedbackAssigned,
            self::FeedbackUnassigned,
            self::FeedbackStatusChanged => [NotificationChannel::InApp],

            // Export completion carries an actionable link; email + in-app.
            self::FeedbackExportReady,
            self::FeedbackExportFailed => [NotificationChannel::InApp, NotificationChannel::Email],

            default => [NotificationChannel::InApp, NotificationChannel::Email],
        };
    }

    /** @return list<string> */
    public static function values(): array
    {
        return array_map(static fn (self $t): string => $t->value, self::cases());
    }
}
