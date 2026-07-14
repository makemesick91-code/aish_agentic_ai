<?php

declare(strict_types=1);

namespace App\Feedback\Listeners;

use App\Enums\ResponseStatus;
use App\Events\SurveyResponseCompleted;
use App\Feedback\FeedbackProjector;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Tenancy\TenantContext;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Queued, after-commit listener that projects a completed survey response into a feedback item. It
 * rehydrates the tenant context for the event's tenant, runs the idempotent projector, and always
 * clears the context afterwards so nothing leaks to the next job on the worker. Dependencies are
 * resolved inside handle() (not constructor-injected) so the queued listener serializes cleanly
 * (rule 33; Step 8 §9; rule 03).
 */
final class ProjectFeedbackOnSurveyResponseCompleted implements ShouldQueue
{
    use InteractsWithQueue;

    /** Only dispatch once the surrounding survey transaction has committed. */
    public bool $afterCommit = true;

    public int $tries = 5;

    public function handle(SurveyResponseCompleted $event): void
    {
        $context = app(TenantContext::class);
        $context->forget();

        $tenant = Tenant::find($event->tenantId);
        if ($tenant === null) {
            return;
        }

        $context->establish($tenant);

        try {
            $response = SurveyResponse::find($event->surveyResponseId);
            if ($response === null || $response->status !== ResponseStatus::Completed) {
                return;
            }

            app(FeedbackProjector::class)->projectFromSurveyResponse($response);
        } finally {
            $context->forget();
            Log::flushSharedContext();
        }
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [10, 30, 60, 120];
    }
}
