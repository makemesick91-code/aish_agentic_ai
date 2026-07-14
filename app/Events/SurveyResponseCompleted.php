<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Emitted after a survey response is committed as completed. It carries identifiers only — never
 * answer content — and drives the queued, idempotent feedback projection. This is the first and
 * only domain event in the codebase; it decouples the survey completion path from the feedback
 * operations module (rule 33; Step 8 §9; rule 08).
 */
final class SurveyResponseCompleted
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly int $surveyResponseId,
        public readonly int $tenantId,
        public readonly ?int $branchId = null,
    ) {}
}
