<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Enums\FeedbackStatus;
use App\Enums\FeedbackTagStatus;
use App\Feedback\Exceptions\InvalidAssigneeException;
use App\Feedback\Exceptions\InvalidStatusTransitionException;
use App\Feedback\FeedbackAssignmentService;
use App\Feedback\FeedbackLifecycle;
use App\Feedback\FeedbackSummaryService;
use App\Feedback\Search\FeedbackSearchCriteria;
use App\Feedback\Search\FeedbackSearchService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\AssignFeedbackRequest;
use App\Http\Requests\Feedback\UpdateFeedbackStatusRequest;
use App\Models\FeedbackItem;
use App\Models\FeedbackTag;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Tenant Feedback Inbox + detail workspace. Every action authorizes server-side via
 * FeedbackItemPolicy (never UI hiding) and delegates to the feedback services (rule 33; Step 8 §20, §21).
 */
final class FeedbackController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly FeedbackSearchService $search,
        private readonly FeedbackSummaryService $summaries,
        private readonly FeedbackLifecycle $lifecycle,
        private readonly FeedbackAssignmentService $assignment,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', FeedbackItem::class);

        $criteria = new FeedbackSearchCriteria(
            query: $request->string('q')->value() !== '' ? $request->string('q')->value() : null,
            statuses: array_values(array_filter(array_map('strval', (array) $request->input('status', [])))),
            branchId: $request->integer('branch_id') ?: null,
            surveyId: $request->integer('survey_id') ?: null,
            campaignId: $request->integer('campaign_id') ?: null,
            assigneeId: $request->integer('assignee_id') ?: null,
            tagId: $request->integer('tag_id') ?: null,
            dateFrom: $request->date('date_from')?->toDateString(),
            dateTo: $request->date('date_to')?->toDateString(),
            metric: $request->string('metric')->value() ?: null,
            metricValue: $request->has('metric_value') ? $request->integer('metric_value') : null,
            sort: $request->string('sort')->value() ?: 'recent',
        );

        $items = $this->search->search($criteria, $request->user());
        $summary = $request->user()?->can('viewSummary', FeedbackItem::class) === true
            ? $this->summaries->summary()
            : null;

        return view('feedback.index', [
            'items' => $items,
            'summary' => $summary,
            'criteria' => $criteria,
        ]);
    }

    public function show(FeedbackItem $feedback, Request $request): View
    {
        $this->authorize('view', $feedback);

        $feedback->load([
            'assignee', 'survey', 'campaign', 'branch', 'tags', 'surveyResponse',
            'notes.author', 'attachments' => fn ($q) => $q->orderByDesc('id'),
            'assignmentHistory.newAssignee', 'events.actor',
        ]);

        return view('feedback.show', [
            'feedback' => $feedback,
            'canViewContent' => $request->user()?->can('viewContent', $feedback) === true,
            'availableTags' => FeedbackTag::query()
                ->where('status', FeedbackTagStatus::Active->value)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function updateStatus(UpdateFeedbackStatusRequest $request, FeedbackItem $feedback): RedirectResponse
    {
        $this->authorize('manageStatus', $feedback);

        try {
            $this->lifecycle->transition(
                $feedback,
                FeedbackStatus::from((string) $request->validated('status')),
                $this->actor($request),
                $request->validated('reason'),
            );
        } catch (InvalidStatusTransitionException $e) {
            return back()->withErrors(['status' => $e->getMessage()]);
        }

        return back()->with('status', __('Feedback status updated.'));
    }

    public function assign(AssignFeedbackRequest $request, FeedbackItem $feedback): RedirectResponse
    {
        $this->authorize('assign', $feedback);

        $assigneeId = $request->validated('assignee_id');
        $assignee = $assigneeId !== null ? User::find((int) $assigneeId) : null;

        try {
            $this->assignment->assign($feedback, $assignee, $this->actor($request), $request->validated('reason'));
        } catch (InvalidAssigneeException $e) {
            return back()->withErrors(['assignee_id' => $e->getMessage()]);
        }

        return back()->with('status', __('Assignment updated.'));
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_if($user === null, 403);

        return $user;
    }
}
