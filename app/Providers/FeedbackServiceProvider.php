<?php

declare(strict_types=1);

namespace App\Providers;

use App\Events\SurveyResponseCompleted;
use App\Feedback\Listeners\ProjectFeedbackOnSurveyResponseCompleted;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * Wires the Feedback Operations module: the completed-survey-response event drives the queued,
 * idempotent feedback projection. Policies are registered in AuthorizationServiceProvider alongside
 * the other tenant-scoped policies (rule 33; Step 8 §9).
 */
class FeedbackServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(
            SurveyResponseCompleted::class,
            ProjectFeedbackOnSurveyResponseCompleted::class,
        );
    }
}
