<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Lifecycle state of a survey (the stable identity, not its versioned content). Only a draft
 * survey's current draft version may be edited; a published survey's published content is
 * immutable and lives in survey_versions. A published survey MUST NOT be hard-deleted
 * (rule 32; Step 7 §10.2).
 */
enum SurveyStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Paused = 'paused';
    case Archived = 'archived';

    /** Whether a campaign bound to this survey may issue new invitations. */
    public function canIssueInvitations(): bool
    {
        return $this === self::Published;
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    /**
     * Allowed forward transitions from this state. Publishing (draft -> published) is a
     * distinct guarded operation and is not listed here.
     *
     * @return list<self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Published, self::Archived],
            self::Published => [self::Paused, self::Archived],
            self::Paused => [self::Published, self::Archived],
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
            self::Published => 'Published',
            self::Paused => 'Paused',
            self::Archived => 'Archived',
        };
    }
}
