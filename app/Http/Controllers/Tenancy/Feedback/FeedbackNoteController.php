<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Feedback\FeedbackNoteService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreFeedbackNoteRequest;
use App\Models\FeedbackItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;

/**
 * Creates append-only internal notes on a feedback item (rule 33; Step 8 §13).
 */
final class FeedbackNoteController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly FeedbackNoteService $notes) {}

    public function store(StoreFeedbackNoteRequest $request, FeedbackItem $feedback): RedirectResponse
    {
        $this->authorize('addNote', $feedback);

        $user = $request->user();
        abort_if($user === null, 403);

        $this->notes->addNote($feedback, $user, (string) $request->validated('body'));

        return back()->with('status', __('Note added.'));
    }
}
