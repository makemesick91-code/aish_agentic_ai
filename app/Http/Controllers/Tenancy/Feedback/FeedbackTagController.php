<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Feedback\Exceptions\FeedbackTagException;
use App\Feedback\FeedbackTagService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\AttachFeedbackTagRequest;
use App\Http\Requests\Feedback\StoreFeedbackTagRequest;
use App\Models\FeedbackItem;
use App\Models\FeedbackTag;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manual tag catalog management and attach/detach on a feedback item (rule 33; Step 8 §12).
 */
final class FeedbackTagController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly FeedbackTagService $tags) {}

    public function store(StoreFeedbackTagRequest $request): RedirectResponse
    {
        $this->authorize('manageTags', FeedbackItem::class);

        try {
            $this->tags->createTag((string) $request->validated('name'), $this->actor($request), $request->validated('color'));
        } catch (FeedbackTagException $e) {
            return back()->withErrors(['name' => $e->getMessage()]);
        }

        return back()->with('status', __('Tag created.'));
    }

    public function attach(AttachFeedbackTagRequest $request, FeedbackItem $feedback): RedirectResponse
    {
        $this->authorize('tag', $feedback);

        $tag = FeedbackTag::query()->findOrFail((int) $request->validated('tag_id'));

        try {
            $this->tags->attach($feedback, $tag, $this->actor($request));
        } catch (FeedbackTagException $e) {
            return back()->withErrors(['tag' => $e->getMessage()]);
        }

        return back()->with('status', __('Tag attached.'));
    }

    public function detach(Request $request, FeedbackItem $feedback, FeedbackTag $tag): RedirectResponse
    {
        $this->authorize('tag', $feedback);

        $this->tags->remove($feedback, $tag, $this->actor($request));

        return back()->with('status', __('Tag removed.'));
    }

    private function actor(Request $request): User
    {
        $user = $request->user();
        abort_if($user === null, 403);

        return $user;
    }
}
