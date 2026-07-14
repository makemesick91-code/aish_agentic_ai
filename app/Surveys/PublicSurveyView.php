<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\SurveyVersion;

/**
 * A fully-eager-loaded, detached view of a public survey ready for rendering without any
 * further tenant-scoped queries. Carries only what the public page needs — never draft
 * content and never tenant-identifying data beyond the opaque public id (rule 32; Step 7 §15,
 * §18).
 */
final readonly class PublicSurveyView
{
    public function __construct(
        public SurveyCampaign $campaign,
        public SurveyVersion $version,
        public ?SurveyInvitation $invitation,
    ) {}
}
