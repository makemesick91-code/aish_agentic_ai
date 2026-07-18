<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Consent and communication-preference categories. Consent history is versioned and append-only;
 * completing a survey is NOT marketing consent (rule 36, rule 32; ADR 0064).
 */
enum CustomerConsentType: string
{
    case Marketing = 'marketing';
    case FollowUp = 'follow_up';
    case SurveyInvitation = 'survey_invitation';
    case DoNotContact = 'do_not_contact';

    /**
     * `DoNotContact` inverts the usual reading: accepted = the customer asked NOT to be contacted,
     * so it must suppress outreach rather than permit it.
     */
    public function isSuppression(): bool
    {
        return $this === self::DoNotContact;
    }

    public function label(): string
    {
        return match ($this) {
            self::Marketing => 'Marketing',
            self::FollowUp => 'Follow-up contact',
            self::SurveyInvitation => 'Survey invitation',
            self::DoNotContact => 'Do not contact',
        };
    }
}
