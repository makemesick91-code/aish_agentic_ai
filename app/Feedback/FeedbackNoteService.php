<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackEventType;
use App\Models\FeedbackItem;
use App\Models\FeedbackNote;
use App\Models\User;
use RuntimeException;

/**
 * Creates append-only internal staff notes. The body is untrusted free text — it is length-bounded
 * here and escaped on output, and it is NEVER written to the timeline, the audit log, or a
 * notification. A correction is a new note; notes cannot be edited or deleted (rule 33; Step 8 §13).
 */
final class FeedbackNoteService
{
    public function __construct(
        private readonly FeedbackTimeline $timeline,
        private readonly AuditRecorder $audit,
    ) {}

    public function addNote(FeedbackItem $item, User $author, string $body): FeedbackNote
    {
        $body = trim($body);
        if ($body === '') {
            throw new RuntimeException('A feedback note cannot be empty.');
        }
        if (mb_strlen($body) > FeedbackNote::MAX_BODY_LENGTH) {
            throw new RuntimeException('The feedback note exceeds the maximum length.');
        }

        $note = FeedbackNote::create([
            'feedback_item_id' => $item->id,
            'branch_id' => $item->branch_id,
            'author_id' => $author->id,
            'body' => $body,
        ]);

        $item->forceFill(['last_activity_at' => now()])->save();

        // The body is deliberately absent from both the timeline metadata and the audit metadata.
        $this->timeline->record($item, FeedbackEventType::NoteCreated, [
            'note_ulid' => $note->ulid,
        ], actorId: $author->id);
        $this->audit->record('feedback.note.created', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $author->id,
            'subject' => $item,
            'metadata' => ['note_ulid' => $note->ulid],
        ]);

        return $note;
    }
}
