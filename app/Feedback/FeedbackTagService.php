<?php

declare(strict_types=1);

namespace App\Feedback;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackEventType;
use App\Enums\FeedbackTagStatus;
use App\Feedback\Exceptions\FeedbackTagException;
use App\Models\FeedbackItem;
use App\Models\FeedbackItemTag;
use App\Models\FeedbackTag;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;

/**
 * Manages tenant-owned manual tags and their links to feedback items. Manual tags are operational
 * classification and are deliberately distinct from future AI-generated topics. A tag never crosses
 * tenants (the pivot's composite FK guarantees it); an archived tag cannot be newly attached but its
 * historical links remain. Attach/remove are audited and appear on the timeline (rule 33; Step 8 §12).
 */
final class FeedbackTagService
{
    public function __construct(
        private readonly FeedbackTimeline $timeline,
        private readonly AuditRecorder $audit,
    ) {}

    public function createTag(string $name, User $actor, ?string $color = null): FeedbackTag
    {
        $name = trim($name);

        try {
            $tag = FeedbackTag::create([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => FeedbackTagStatus::Active,
                'color' => $color,
                'created_by' => $actor->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            throw FeedbackTagException::duplicateName($name);
        }

        $this->audit->record('feedback.tag.created', [
            'subject' => $tag,
            'actor_id' => $actor->id,
            'metadata' => ['name' => $tag->name, 'slug' => $tag->slug],
        ]);

        return $tag;
    }

    public function archiveTag(FeedbackTag $tag, User $actor): FeedbackTag
    {
        $tag->status = FeedbackTagStatus::Archived;
        $tag->save();

        $this->audit->record('feedback.tag.archived', [
            'subject' => $tag,
            'actor_id' => $actor->id,
            'metadata' => ['slug' => $tag->slug],
        ]);

        return $tag;
    }

    public function attach(FeedbackItem $item, FeedbackTag $tag, User $actor): void
    {
        if ($tag->tenant_id !== $item->tenant_id) {
            throw FeedbackTagException::crossTenant();
        }
        if (! $tag->status->isAttachable()) {
            throw FeedbackTagException::archivedNotAttachable();
        }

        if ($this->linkExists($item, $tag)) {
            return;
        }

        try {
            FeedbackItemTag::create([
                'tenant_id' => $item->tenant_id,
                'feedback_item_id' => $item->id,
                'feedback_tag_id' => $tag->id,
                'attached_by' => $actor->id,
            ]);
        } catch (UniqueConstraintViolationException) {
            return; // already attached (race) — idempotent
        }

        $this->touch($item);
        $this->timeline->record($item, FeedbackEventType::TagAttached, [
            'tag_id' => $tag->id,
            'tag_slug' => $tag->slug,
        ], actorId: $actor->id);
        $this->audit->record('feedback.tag.attached', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $actor->id,
            'subject' => $item,
            'metadata' => ['tag_slug' => $tag->slug],
        ]);
    }

    public function remove(FeedbackItem $item, FeedbackTag $tag, User $actor): void
    {
        $link = FeedbackItemTag::query()
            ->where('feedback_item_id', $item->id)
            ->where('feedback_tag_id', $tag->id)
            ->first();

        if ($link === null) {
            return;
        }

        $link->delete();

        $this->touch($item);
        $this->timeline->record($item, FeedbackEventType::TagRemoved, [
            'tag_id' => $tag->id,
            'tag_slug' => $tag->slug,
        ], actorId: $actor->id);
        $this->audit->record('feedback.tag.removed', [
            'tenant_id' => $item->tenant_id,
            'branch_id' => $item->branch_id,
            'actor_id' => $actor->id,
            'subject' => $item,
            'metadata' => ['tag_slug' => $tag->slug],
        ]);
    }

    private function linkExists(FeedbackItem $item, FeedbackTag $tag): bool
    {
        return FeedbackItemTag::query()
            ->where('feedback_item_id', $item->id)
            ->where('feedback_tag_id', $tag->id)
            ->exists();
    }

    private function touch(FeedbackItem $item): void
    {
        $item->forceFill(['last_activity_at' => now()])->save();
    }
}
