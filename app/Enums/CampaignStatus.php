<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle state of a survey campaign. A campaign is always bound to one immutable published
 * survey version; only an active campaign accepts responses and issues invitations. It can
 * never silently switch survey version (rule 32; Step 7 §16).
 */
enum CampaignStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Paused = 'paused';
    case Ended = 'ended';
    case Archived = 'archived';

    /** Whether new invitations may be issued in this state. */
    public function canIssueInvitations(): bool
    {
        return $this === self::Active;
    }

    /** Whether a public response may be accepted in this state. */
    public function acceptsResponses(): bool
    {
        return $this === self::Active;
    }

    public function isReadOnly(): bool
    {
        return $this === self::Archived;
    }

    /** @return list<self> */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Active, self::Archived],
            self::Active => [self::Paused, self::Ended],
            self::Paused => [self::Active, self::Ended],
            self::Ended => [self::Archived],
            self::Archived => [],
        };
    }

    public function canTransitionTo(self $target): bool
    {
        return in_array($target, $this->allowedTransitions(), true);
    }

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Active => 'Active',
            self::Paused => 'Paused',
            self::Ended => 'Ended',
            self::Archived => 'Archived',
        };
    }
}
