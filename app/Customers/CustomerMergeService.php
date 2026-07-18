<?php

declare(strict_types=1);

namespace App\Customers;

use App\Audit\AuditRecorder;
use App\Customers\Exceptions\CustomerMergeException;
use App\Customers\Support\CustomerBranchScope;
use App\Enums\CustomerStatus;
use App\Models\Customer;
use App\Models\CustomerIdentity;
use App\Models\CustomerMergeEvent;
use App\Models\FeedbackItem;
use App\Tenancy\TenantContext;
use Illuminate\Support\Facades\DB;

/**
 * Human-approved, fully reversible customer merge and split.
 *
 * The defining rule is that a merge NEVER deletes (ADR 0072). The non-surviving customer keeps its
 * row, its identities' provenance, and its consent history; only the links move. Because the exact
 * set of moved ids is recorded in an append-only snapshot, a split is a precise inverse rather than
 * a best-effort reconstruction — so an incorrect merge is always recoverable without a database
 * restore.
 *
 * Both customers are locked in deterministic id order, so two concurrent merges can neither
 * interleave into a half-merged state nor deadlock (rule 36).
 */
final class CustomerMergeService
{
    public function __construct(
        private readonly TenantContext $context,
        private readonly AuditRecorder $audit,
    ) {}

    /**
     * Merge `$merged` into `$survivor`.
     *
     * @throws CustomerMergeException
     */
    public function merge(Customer $survivor, Customer $merged, string $reason, ?int $actorUserId = null): CustomerMergeEvent
    {
        if ($survivor->id === $merged->id) {
            throw CustomerMergeException::sameCustomer();
        }

        // The actor must be able to reach BOTH customers — merging a customer you cannot see would
        // let a branch-restricted operator reshape identity outside their scope.
        if (! CustomerBranchScope::canReach($survivor, $this->context)
            || ! CustomerBranchScope::canReach($merged, $this->context)) {
            throw CustomerMergeException::branchOutOfScope();
        }

        return DB::transaction(function () use ($survivor, $merged, $reason, $actorUserId): CustomerMergeEvent {
            [$first, $second] = $this->lockPair($survivor->id, $merged->id);

            $survivor = $survivor->id === $first->id ? $first : $second;
            $merged = $merged->id === $first->id ? $first : $second;

            if (! $survivor->isLinkable()) {
                throw $survivor->isMerged()
                    ? CustomerMergeException::alreadyMerged()
                    : CustomerMergeException::notLinkable();
            }

            if (! $merged->isLinkable()) {
                throw $merged->isMerged()
                    ? CustomerMergeException::alreadyMerged()
                    : CustomerMergeException::notLinkable();
            }

            // Capture exactly what moves, BEFORE moving it — this list is what a split replays.
            $identityIds = CustomerIdentity::query()
                ->where('customer_id', $merged->id)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $feedbackIds = FeedbackItem::query()
                ->where('customer_id', $merged->id)
                ->orderBy('id')
                ->pluck('id')
                ->all();

            $snapshot = $this->buildSnapshot($survivor, $merged, $identityIds, $feedbackIds);

            if ($identityIds !== []) {
                CustomerIdentity::query()
                    ->whereIn('id', $identityIds)
                    ->update([
                        'customer_id' => $survivor->id,
                        'merged_from_customer_id' => $merged->id,
                        'updated_at' => now(),
                    ]);
            }

            if ($feedbackIds !== []) {
                FeedbackItem::query()
                    ->whereIn('id', $feedbackIds)
                    ->update(['customer_id' => $survivor->id]);
            }

            // Retained, not deleted — this is what makes the merge reversible.
            $merged->forceFill([
                'status' => CustomerStatus::Merged,
                'merged_into_customer_id' => $survivor->id,
            ])->save();

            $event = CustomerMergeEvent::create([
                'action' => CustomerMergeEvent::ACTION_MERGE,
                'survivor_customer_id' => $survivor->id,
                'merged_customer_id' => $merged->id,
                'actor_user_id' => $actorUserId,
                'reason' => $reason,
                'snapshot' => $snapshot,
                'created_at' => now(),
            ]);

            $this->audit->record('customer.merged', [
                'subject' => $survivor,
                'actor_id' => $actorUserId,
                'metadata' => [
                    'merge_event_id' => $event->id,
                    'survivor_customer_id' => $survivor->id,
                    'merged_customer_id' => $merged->id,
                    'identities_moved' => count($identityIds),
                    'feedback_items_moved' => count($feedbackIds),
                ],
            ]);

            return $event;
        });
    }

    /**
     * Reverse a merge, restoring exactly what that merge moved.
     *
     * @throws CustomerMergeException
     */
    public function split(CustomerMergeEvent $mergeEvent, string $reason, ?int $actorUserId = null): CustomerMergeEvent
    {
        if (! $mergeEvent->isMerge()) {
            throw CustomerMergeException::notAMergeEvent();
        }

        return DB::transaction(function () use ($mergeEvent, $reason, $actorUserId): CustomerMergeEvent {
            [$first, $second] = $this->lockPair(
                $mergeEvent->survivor_customer_id,
                $mergeEvent->merged_customer_id,
            );

            $survivor = $first->id === $mergeEvent->survivor_customer_id ? $first : $second;
            $merged = $first->id === $mergeEvent->merged_customer_id ? $first : $second;

            // Defence in depth, mirroring merge(): a split writes to BOTH customers, so an actor
            // who cannot reach either one must not be able to reverse it (rule 36; ADR 0072).
            if (! CustomerBranchScope::canReach($survivor, $this->context)
                || ! CustomerBranchScope::canReach($merged, $this->context)) {
                throw CustomerMergeException::branchOutOfScope();
            }

            // Checked AFTER the rows are locked: reading it first lets two concurrent reversals
            // both observe "not yet reversed" and append two split rows for one merge, which would
            // make the append-only ledger claim the merge was reversed twice.
            $alreadyReversed = CustomerMergeEvent::query()
                ->where('action', CustomerMergeEvent::ACTION_SPLIT)
                ->where('reverses_merge_event_id', $mergeEvent->id)
                ->exists();

            if ($alreadyReversed) {
                throw CustomerMergeException::alreadyReversed();
            }

            // Reversing out of order would restore links a later merge has since moved on.
            $laterMerge = CustomerMergeEvent::query()
                ->where('action', CustomerMergeEvent::ACTION_MERGE)
                ->where('merged_customer_id', $survivor->id)
                ->where('id', '>', $mergeEvent->id)
                ->exists();

            if ($laterMerge) {
                throw CustomerMergeException::supersededByLaterMerge();
            }

            $snapshot = $mergeEvent->snapshot;
            $identityIds = array_map('intval', $snapshot['moved_identity_ids'] ?? []);
            $feedbackIds = array_map('intval', $snapshot['moved_feedback_item_ids'] ?? []);

            if ($identityIds !== []) {
                CustomerIdentity::query()
                    ->whereIn('id', $identityIds)
                    ->update([
                        'customer_id' => $merged->id,
                        'merged_from_customer_id' => null,
                        'updated_at' => now(),
                    ]);
            }

            if ($feedbackIds !== []) {
                FeedbackItem::query()
                    ->whereIn('id', $feedbackIds)
                    ->update(['customer_id' => $merged->id]);
            }

            $merged->forceFill([
                'status' => $this->restoredStatus($snapshot),
                'merged_into_customer_id' => null,
            ])->save();

            // A split is a NEW event; the original merge row is never touched, so the ledger keeps
            // the mistake and the correction side by side (ADR 0072).
            $event = CustomerMergeEvent::create([
                'action' => CustomerMergeEvent::ACTION_SPLIT,
                'survivor_customer_id' => $survivor->id,
                'merged_customer_id' => $merged->id,
                'reverses_merge_event_id' => $mergeEvent->id,
                'actor_user_id' => $actorUserId,
                'reason' => $reason,
                'snapshot' => [
                    'reverses_merge_event_id' => $mergeEvent->id,
                    'restored_identity_ids' => $identityIds,
                    'restored_feedback_item_ids' => $feedbackIds,
                ],
                'created_at' => now(),
            ]);

            $this->audit->record('customer.split', [
                'subject' => $merged,
                'actor_id' => $actorUserId,
                'metadata' => [
                    'split_event_id' => $event->id,
                    'reverses_merge_event_id' => $mergeEvent->id,
                    'survivor_customer_id' => $survivor->id,
                    'restored_customer_id' => $merged->id,
                    'identities_restored' => count($identityIds),
                    'feedback_items_restored' => count($feedbackIds),
                ],
            ]);

            return $event;
        });
    }

    /**
     * Lock both rows in ascending id order so concurrent merges of the same pair serialize instead
     * of deadlocking.
     *
     * @return array{0: Customer, 1: Customer}
     */
    private function lockPair(int $a, int $b): array
    {
        $ids = [$a, $b];
        sort($ids);

        $locked = Customer::query()
            ->whereIn('id', $ids)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($locked->count() !== 2) {
            throw CustomerMergeException::notLinkable();
        }

        /** @var array{0: Customer, 1: Customer} $pair */
        $pair = [$locked[0], $locked[1]];

        return $pair;
    }

    /**
     * Sanitized snapshot: ids, counts, provenance, and status only. Never contact values, free
     * text, hashes, tokens, or medical data (rule 36).
     *
     * @param  list<int>  $identityIds
     * @param  list<int>  $feedbackIds
     * @return array<string, mixed>
     */
    private function buildSnapshot(Customer $survivor, Customer $merged, array $identityIds, array $feedbackIds): array
    {
        return [
            'version' => 1,
            'survivor' => [
                'id' => $survivor->id,
                'ulid' => $survivor->ulid,
                'status' => $survivor->status->value,
                'primary_branch_id' => $survivor->primary_branch_id,
                'has_contact_email' => $survivor->contact_email !== null,
                'has_contact_phone' => $survivor->contact_phone !== null,
            ],
            'merged' => [
                'id' => $merged->id,
                'ulid' => $merged->ulid,
                'status' => $merged->status->value,
                'primary_branch_id' => $merged->primary_branch_id,
                'has_contact_email' => $merged->contact_email !== null,
                'has_contact_phone' => $merged->contact_phone !== null,
            ],
            'moved_identity_ids' => $identityIds,
            'moved_feedback_item_ids' => $feedbackIds,
        ];
    }

    /**
     * Restore the status the customer actually had before the merge, not a hardcoded `active` —
     * an inactive customer must not silently become active through a merge reversal.
     *
     * @param  array<string, mixed>  $snapshot
     */
    private function restoredStatus(array $snapshot): CustomerStatus
    {
        $previous = $snapshot['merged']['status'] ?? null;

        if (is_string($previous)) {
            $status = CustomerStatus::tryFrom($previous);

            if ($status !== null && $status->isLinkable()) {
                return $status;
            }
        }

        return CustomerStatus::Active;
    }
}
