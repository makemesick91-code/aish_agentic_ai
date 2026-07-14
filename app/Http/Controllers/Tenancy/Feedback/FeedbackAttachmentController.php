<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Enums\FeedbackAttachmentState;
use App\Feedback\Exceptions\AttachmentRejectedException;
use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Feedback\FeedbackAttachmentService;
use App\Feedback\FeedbackEntitlements;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\StoreFeedbackAttachmentRequest;
use App\Models\FeedbackAttachment;
use App\Models\FeedbackItem;
use App\Tenancy\TenantContext;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Uploads, downloads, and removes internal feedback attachments. Downloads are streamed from a
 * PRIVATE disk only after authorization and belonging checks; a public URL is never exposed
 * (rule 33; Step 8 §14).
 */
final class FeedbackAttachmentController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly FeedbackAttachmentService $attachments,
        private readonly FeedbackEntitlements $entitlements,
        private readonly TenantContext $context,
    ) {}

    public function store(StoreFeedbackAttachmentRequest $request, FeedbackItem $feedback): RedirectResponse
    {
        $this->authorize('manageAttachments', $feedback);

        $user = $request->user();
        abort_if($user === null, 403);

        $file = $request->file('file');
        if (! $file instanceof UploadedFile) {
            return back()->withErrors(['file' => __('No file was uploaded.')]);
        }

        try {
            $this->entitlements->assertAttachmentsEnabled($this->context->tenant());
            $this->attachments->upload($feedback, $file, $user);
        } catch (EntitlementDeniedException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        } catch (AttachmentRejectedException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        return back()->with('status', __('Attachment uploaded.'));
    }

    public function download(FeedbackItem $feedback, FeedbackAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $feedback);

        abort_unless($attachment->feedback_item_id === $feedback->id, 404);
        abort_unless($attachment->state === FeedbackAttachmentState::Available, 404);

        return Storage::disk($attachment->disk)->download($attachment->path, $attachment->original_filename);
    }

    public function destroy(Request $request, FeedbackItem $feedback, FeedbackAttachment $attachment): RedirectResponse
    {
        $this->authorize('manageAttachments', $feedback);

        abort_unless($attachment->feedback_item_id === $feedback->id, 404);

        $user = $request->user();
        abort_if($user === null, 403);

        $this->attachments->remove($attachment, $user);

        return back()->with('status', __('Attachment removed.'));
    }
}
