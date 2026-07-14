<?php

declare(strict_types=1);

namespace App\Feedback\Bulk;

use App\Audit\AuditRecorder;
use App\Enums\FeedbackStatus;
use App\Feedback\Exceptions\FeedbackBulkException;
use App\Feedback\FeedbackAssignmentService;
use App\Feedback\FeedbackLifecycle;
use App\Feedback\FeedbackTagService;
use App\Feedback\Support\FeedbackBranchScope;
use App\Models\FeedbackItem;
use App\Models\FeedbackTag;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Bounded, all-or-nothing bulk feedback operations. Every batch is validated in full — size, tenant
 * membership of every id, branch reachability, and operation validity — BEFORE any mutation. The
 * mutations then run in one transaction that delegates to the same single-item services, so each item
 * still gets its own timeline event and audit entry, and per-item notifications are suppressed to
 * avoid spam. No hidden partial success (rule 33; Step 8 §17).
 */
final class FeedbackBulkService
{
    public const MAX_BATCH = 100;

    public function __construct(
        private readonly FeedbackLifecycle $lifecycle,
        private readonly FeedbackAssignmentService $assignment,
        private readonly FeedbackTagService $tags,
        private readonly AuditRecorder $audit,
        private readonly TenantContext $context,
    ) {}

    /**
     * @param  list<int>  $itemIds
     */
    public function transition(array $itemIds, FeedbackStatus $to, User $actor): BulkResult
    {
        $items = $this->resolveAndAuthorize($itemIds, $actor);

        foreach ($items as $item) {
            if ($item->status->isReopenInto($to)) {
                throw FeedbackBulkException::reopenNotAllowed();
            }
            if (! $item->status->canTransitionTo($to)) {
                throw FeedbackBulkException::invalidForItem($item->ulid);
            }
        }

        DB::transaction(function () use ($items, $to, $actor): void {
            foreach ($items as $item) {
                $this->lifecycle->transition($item, $to, $actor, notify: false);
            }
        });

        return $this->finish('status', $items, $actor, ['to' => $to->value]);
    }

    /**
     * @param  list<int>  $itemIds
     */
    public function assign(array $itemIds, ?User $assignee, User $actor): BulkResult
    {
        $items = $this->resolveAndAuthorize($itemIds, $actor);

        DB::transaction(function () use ($items, $assignee, $actor): void {
            foreach ($items as $item) {
                // assignee validity (incl. per-item branch scope) is asserted inside; a failure
                // rolls back the whole batch.
                $this->assignment->assign($item, $assignee, $actor, notify: false);
            }
        });

        return $this->finish('assign', $items, $actor, ['assignee_id' => $assignee?->id]);
    }

    /**
     * @param  list<int>  $itemIds
     */
    public function attachTag(array $itemIds, FeedbackTag $tag, User $actor): BulkResult
    {
        $items = $this->resolveAndAuthorize($itemIds, $actor);

        DB::transaction(function () use ($items, $tag, $actor): void {
            foreach ($items as $item) {
                $this->tags->attach($item, $tag, $actor);
            }
        });

        return $this->finish('tag-attach', $items, $actor, ['tag_id' => $tag->id]);
    }

    /**
     * @param  list<int>  $itemIds
     */
    public function removeTag(array $itemIds, FeedbackTag $tag, User $actor): BulkResult
    {
        $items = $this->resolveAndAuthorize($itemIds, $actor);

        DB::transaction(function () use ($items, $tag, $actor): void {
            foreach ($items as $item) {
                $this->tags->remove($item, $tag, $actor);
            }
        });

        return $this->finish('tag-remove', $items, $actor, ['tag_id' => $tag->id]);
    }

    /**
     * @param  list<int>  $itemIds
     * @return list<FeedbackItem>
     */
    private function resolveAndAuthorize(array $itemIds, User $actor): array
    {
        $ids = array_values(array_unique(array_map('intval', $itemIds)));

        if ($ids === []) {
            throw FeedbackBulkException::emptyBatch();
        }
        if (count($ids) > self::MAX_BATCH) {
            throw FeedbackBulkException::tooLarge(self::MAX_BATCH);
        }

        // Tenant scoping is automatic; an id from another tenant simply will not resolve.
        $items = FeedbackItem::query()->whereIn('id', $ids)->get();
        if ($items->count() !== count($ids)) {
            throw FeedbackBulkException::unresolved();
        }

        foreach ($items as $item) {
            if (! FeedbackBranchScope::canReach($item, $this->context)) {
                throw FeedbackBulkException::forbiddenItem($item->ulid);
            }
        }

        return $items->all();
    }

    /**
     * @param  list<FeedbackItem>  $items
     * @param  array<string, mixed>  $metadata
     */
    private function finish(string $operation, array $items, User $actor, array $metadata): BulkResult
    {
        $this->audit->record('feedback.bulk-operation.executed', [
            'tenant_id' => $this->context->tenantId(),
            'actor_id' => $actor->id,
            'metadata' => array_merge($metadata, [
                'operation' => $operation,
                'count' => count($items),
                'item_ids' => array_map(fn (FeedbackItem $item) => $item->id, $items),
            ]),
        ]);

        return new BulkResult($operation, count($items));
    }
}
