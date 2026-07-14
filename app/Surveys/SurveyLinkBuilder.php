<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;

/**
 * Builds public survey URLs from opaque public ids. The invitation URL contains the one-time
 * plaintext token (supplied by the caller at issue time); it is never read back from storage
 * (rule 32; Step 7 §17, §17.4).
 */
final class SurveyLinkBuilder
{
    public function campaignUrl(SurveyCampaign $campaign): string
    {
        return route('survey.public.campaign', ['campaign' => $campaign->public_id]);
    }

    public function campaignQrUrl(SurveyCampaign $campaign): string
    {
        return route('survey.public.qr', ['campaign' => $campaign->public_id]);
    }

    public function invitationUrl(SurveyInvitation $invitation, string $plainToken): string
    {
        return route('survey.public.invitation', [
            'invitation' => $invitation->public_id,
            'token' => $plainToken,
        ]);
    }
}
