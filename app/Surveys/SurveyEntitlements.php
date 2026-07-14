<?php

declare(strict_types=1);

namespace App\Surveys;

use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Models\Tenant;
use App\Subscriptions\EntitlementKeys;
use App\Subscriptions\EntitlementResolver;
use App\Subscriptions\MeterKeys;
use App\Subscriptions\UsageMeter;
use App\Surveys\Exceptions\EntitlementDeniedException;
use Illuminate\Support\Carbon;

/**
 * The single place survey entitlement/limit decisions are made. It delegates entirely to the
 * authoritative EntitlementResolver (no duplicated plan logic) and to the tenant-scoped
 * UsageMeter for period counts. Every check fails closed — an ungranted or unknown key denies
 * (rule 31 §9.3, rule 32; Step 7 §23).
 */
final class SurveyEntitlements
{
    private const PERIOD_TIMEZONE = 'Asia/Makassar';

    public function __construct(
        private readonly EntitlementResolver $resolver,
        private readonly UsageMeter $meter,
    ) {}

    public function assertSurveysEnabled(Tenant $tenant): void
    {
        $decision = $this->resolver->resolve($tenant, EntitlementKeys::SURVEYS_ENABLED);
        if (! $decision->allowed) {
            throw EntitlementDeniedException::notGranted(EntitlementKeys::SURVEYS_ENABLED, $decision->reasonCode);
        }
    }

    public function assertCanCreateSurvey(Tenant $tenant): void
    {
        $this->assertSurveysEnabled($tenant);
        $this->assertUnderCountLimit($tenant, EntitlementKeys::SURVEYS_MAX, Survey::query()->count());
    }

    public function assertCanCreateCampaign(Tenant $tenant): void
    {
        $this->assertUnderCountLimit($tenant, EntitlementKeys::SURVEY_CAMPAIGNS_MAX, SurveyCampaign::query()->count());
    }

    public function assertCanIssueInvitation(Tenant $tenant): void
    {
        $used = $this->meter->total($tenant, MeterKeys::SURVEY_INVITATIONS_CREATED, $this->periodKey());
        $this->assertUnderPeriodLimit($tenant, EntitlementKeys::SURVEY_INVITATIONS_MONTHLY, $used);
    }

    public function assertCanAcceptResponse(Tenant $tenant): void
    {
        $used = $this->meter->total($tenant, MeterKeys::SURVEY_RESPONSES_COMPLETED, $this->periodKey());
        $this->assertUnderPeriodLimit($tenant, EntitlementKeys::SURVEY_RESPONSES_MONTHLY, $used);
    }

    private function assertUnderCountLimit(Tenant $tenant, string $key, int $current): void
    {
        $limit = $this->grantedLimit($tenant, $key);
        if ($limit !== EntitlementKeys::UNLIMITED && $current >= $limit) {
            throw EntitlementDeniedException::limitReached($key);
        }
    }

    private function assertUnderPeriodLimit(Tenant $tenant, string $key, int $used): void
    {
        $limit = $this->grantedLimit($tenant, $key);
        if ($limit !== EntitlementKeys::UNLIMITED && $used >= $limit) {
            throw EntitlementDeniedException::limitReached($key);
        }
    }

    /** Resolve an integer limit, failing closed if the feature is not granted. */
    private function grantedLimit(Tenant $tenant, string $key): int
    {
        $decision = $this->resolver->resolve($tenant, $key);
        if (! $decision->allowed) {
            throw EntitlementDeniedException::notGranted($key, $decision->reasonCode);
        }

        return (int) $decision->effectiveValue;
    }

    private function periodKey(): string
    {
        return Carbon::now()->setTimezone(self::PERIOD_TIMEZONE)->format('Y-m');
    }
}
