<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Stable, append-only operational timeline event codes for a feedback item. The timeline is
 * distinct from the audit log; both are sanitized and carry no response free-text, note body,
 * attachment content, tokens, or storage paths (rule 33; Step 8 §15, §24).
 */
enum FeedbackEventType: string
{
    case Projected = 'feedback.projected';
    case StatusChanged = 'feedback.status.changed';
    case Assigned = 'feedback.assigned';
    case Unassigned = 'feedback.unassigned';
    case Reopened = 'feedback.reopened';
    case TagAttached = 'feedback.tag.attached';
    case TagRemoved = 'feedback.tag.removed';
    case NoteCreated = 'feedback.note.created';
    case AttachmentAdded = 'feedback.attachment.added';
    case AttachmentRemoved = 'feedback.attachment.removed';
    case ExportRequested = 'feedback.export.requested';

    public function label(): string
    {
        return match ($this) {
            self::Projected => 'Projected from survey response',
            self::StatusChanged => 'Status changed',
            self::Assigned => 'Assigned',
            self::Unassigned => 'Unassigned',
            self::Reopened => 'Reopened',
            self::TagAttached => 'Tag attached',
            self::TagRemoved => 'Tag removed',
            self::NoteCreated => 'Internal note added',
            self::AttachmentAdded => 'Attachment added',
            self::AttachmentRemoved => 'Attachment removed',
            self::ExportRequested => 'Export requested',
        };
    }
}
