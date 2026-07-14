<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenancy\Feedback;

use App\Authorization\Permissions;
use App\Enums\FeedbackExportStatus;
use App\Feedback\Exceptions\EntitlementDeniedException;
use App\Feedback\Export\FeedbackExportService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feedback\RequestFeedbackExportRequest;
use App\Models\FeedbackExport;
use App\Models\FeedbackItem;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Requests a queued feedback export and downloads a ready one. Content inclusion additionally requires
 * the content-view permission; downloads require a ready, unexpired export owned by the current tenant
 * (rule 33; Step 8 §18).
 */
final class FeedbackExportController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private readonly FeedbackExportService $exports) {}

    public function store(RequestFeedbackExportRequest $request): RedirectResponse
    {
        $this->authorize('export', FeedbackItem::class);

        $user = $request->user();
        abort_if($user === null, 403);

        $includeContent = $request->boolean('include_content') && $user->can(Permissions::FEEDBACK_VIEW_CONTENT);

        try {
            $this->exports->request($request->safe()->except(['include_content']), $user, $includeContent);
        } catch (EntitlementDeniedException $e) {
            return back()->withErrors(['export' => $e->getMessage()]);
        }

        return back()->with('status', __('Export requested. You will be notified when it is ready.'));
    }

    public function download(Request $request, FeedbackExport $export): StreamedResponse
    {
        $this->authorize('export', FeedbackItem::class);

        $user = $request->user();
        abort_if($user === null, 403);

        // Re-authorize the DOWNLOAD, not just the tenant-level permission: the export snapshotted the
        // REQUESTER's branch scope and content permission, so only the requester may consume it. This
        // prevents a branch-restricted (or non-content) member from downloading a broader member's
        // export via a shared/forwarded ULID (rule 33; Step 8 §18).
        abort_unless($export->requested_by === $user->id, 403);
        abort_if($export->includes_content && ! $user->can(Permissions::FEEDBACK_VIEW_CONTENT), 403);

        abort_unless($export->status === FeedbackExportStatus::Ready, 404);
        abort_if($export->isExpired(), 410);
        abort_if($export->path === null || $export->disk === null, 404);

        $export->forceFill(['downloaded_at' => now()])->save();

        return Storage::disk($export->disk)->download($export->path, 'feedback-export-'.$export->ulid.'.csv');
    }
}
