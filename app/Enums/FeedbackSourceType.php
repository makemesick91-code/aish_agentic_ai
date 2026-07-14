<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * The kind of source a feedback item is projected from. Step 8 projects only from completed
 * survey responses; future sources (e.g. inbound channels) are added here as new cases without
 * changing the idempotency contract (tenant_id, source_type, source_id) (rule 33; Step 8 §9).
 */
enum FeedbackSourceType: string
{
    case SurveyResponse = 'survey_response';

    public function label(): string
    {
        return match ($this) {
            self::SurveyResponse => 'Survey response',
        };
    }
}
