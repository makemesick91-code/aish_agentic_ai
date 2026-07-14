<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Models\SurveyInvitation;

/**
 * Result of issuing an invitation. `plainToken` is the one-time transient secret used to build
 * the delivery link; it is present only when a NEW invitation was created and is never
 * persisted or logged. On an idempotent repeat it is null (rule 32; Step 7 §17.2).
 */
final readonly class IssuedInvitation
{
    public function __construct(
        public SurveyInvitation $invitation,
        public ?string $plainToken,
    ) {}

    public function isNew(): bool
    {
        return $this->plainToken !== null;
    }
}
