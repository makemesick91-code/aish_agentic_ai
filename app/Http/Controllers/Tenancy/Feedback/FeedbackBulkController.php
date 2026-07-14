<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Enums\FeedbackStatus;
use App\Feedback\Bulk\FeedbackBulkService;
use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Feedback\Exceptions\FeedbackBulkException;
use App\Feedback\Exceptions\FeedbackTagException;
use App\Feedback\Exceptions\InvalidAssigneeException;
use App\Feedback\FeedbackEntitlements;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\BulkFeedbackRequest;
use App\Models\FeedbackItem;
use App\Models\FeedbackTag;
use App\Models\User;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Applies a bounded, all-or-nothing bulk feedback operation (rule 33; Step 8 §17).
 */
final class FeedbackBulkController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly FeedbackBulkService $bulk,
        private readonly FeedbackEntitlements $entitlements,
        private readonly TenantContext $context,
    ) {}

    public function store(BulkFeedbackRequest $request): RedirectResponse
    {
        $this->authorize('bulkManage', FeedbackItem::class);

        $user = $request->user();
        abort_if($user === null, 403);

        /** @var array<string, mixed> $data */
        $data = $request->validated();
        /** @var list<int> $ids */
        $ids = array_map('intval', $data['ids']);

        try {
            $this->entitlements->assertBulkActionsEnabled($this->context->tenant());

            match ($data['action']) {
                'status' => $this->bulk->transition($ids, FeedbackStatus::from((string) $data['status']), $user),
                'assign' => $this->bulk->assign($ids, $this->resolveUser($data['assignee_id'] ?? null), $user),
                'attach-tag' => $this->bulk->attachTag($ids, FeedbackTag::query()->findOrFail((int) $data['tag_id']), $user),
                'remove-tag' => $this->bulk->removeTag($ids, FeedbackTag::query()->findOrFail((int) $data['tag_id']), $user),
                default => null,
            };
        } catch (FeedbackBulkException|InvalidAssigneeException|FeedbackTagException|EntitlementDeniedException $e) {
            return back()->withErrors(['bulk' => $e->getMessage()]);
        }

        return back()->with('status', __('Bulk action applied.'));
    }

    private function resolveUser(mixed $id): ?User
    {
        return $id !== null ? User::find((int) $id) : null;
    }
}
