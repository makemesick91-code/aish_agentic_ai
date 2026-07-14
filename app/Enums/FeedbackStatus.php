<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Operational lifecycle state of a feedback item. Transitions are explicit and validated;
 * invalid transitions fail closed. `Resolved`/`Closed` are OPERATIONAL states and never imply
 * customer recovery success (recovery is a later step). Historical status changes are immutable
 * (rule 33; Step 8 §10).
 */
enum FeedbackStatus: string
{
    case New = 'new';
    case Triaged = 'triaged';
    case Assigned = 'assigned';
    case InProgress = 'in_progress';
    case Resolved = 'resolved';
    case Closed = 'closed';
    case Archived = 'archived';

    /**
     * Allowed forward transitions. `Archived` is read-only under the normal workflow; leaving it
     * requires an explicit, privileged restore path (never listed here).
     */
    public function canTransitionTo(self $to): bool
    {
        return match ($this) {
            self::New => in_array($to, [self::Triaged, self::Assigned], true),
            self::Triaged => in_array($to, [self::Assigned, self::InProgress, self::Resolved], true),
            self::Assigned => in_array($to, [self::InProgress, self::Resolved], true),
            self::InProgress => in_array($to, [self::Resolved], true),
            self::Resolved => in_array($to, [self::Closed, self::InProgress], true),
            self::Closed => in_array($to, [self::Triaged, self::Archived], true),
            self::Archived => false,
        };
    }

    /** A reopen re-activates a resolved/closed item and always requires a reason (Step 8 §10). */
    public function isReopenInto(self $to): bool
    {
        return ($this === self::Resolved && $to === self::InProgress)
            || ($this === self::Closed && $to === self::Triaged);
    }

    /** Open items are actively actionable (assignment, notes, status work). */
    public function isOpen(): bool
    {
        return in_array($this, [self::New, self::Triaged, self::Assigned, self::InProgress], true);
    }

    public function isArchived(): bool
    {
        return $this === self::Archived;
    }

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Triaged => 'Triaged',
            self::Assigned => 'Assigned',
            self::InProgress => 'In progress',
            self::Resolved => 'Resolved',
            self::Closed => 'Closed',
            self::Archived => 'Archived',
        };
    }
}
