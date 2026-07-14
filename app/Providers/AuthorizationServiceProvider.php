<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\FeedbackItem;
use App\Models\NotificationDelivery;
use App\Models\Survey;
use App\Models\SurveyCampaign;
use App\Models\SurveyInvitation;
use App\Models\SurveyResponse;
use App\Models\Tenant;
use App\Models\TenantInvitation;
use App\Models\TenantMembership;
use App\Policies\AuditLogPolicy;
use App\Policies\BranchPolicy;
use App\Policies\FeedbackItemPolicy;
use App\Policies\NotificationDeliveryPolicy;
use App\Policies\SurveyCampaignPolicy;
use App\Policies\SurveyInvitationPolicy;
use App\Policies\SurveyPolicy;
use App\Policies\SurveyResponsePolicy;
use App\Policies\TenantInvitationPolicy;
use App\Policies\TenantMembershipPolicy;
use App\Policies\TenantPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

/**
 * Registers tenant-scoped authorization. Every policy runs against the current
 * TenantContext and the actor's tenant-scoped permissions; UI hiding is never the only
 * control (rule 30; ADR 0013).
 */
class AuthorizationServiceProvider extends ServiceProvider
{
    /** @var array<class-string, class-string> */
    private array $policies = [
        Tenant::class => TenantPolicy::class,
        Branch::class => BranchPolicy::class,
        TenantMembership::class => TenantMembershipPolicy::class,
        TenantInvitation::class => TenantInvitationPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
        NotificationDelivery::class => NotificationDeliveryPolicy::class,
        Survey::class => SurveyPolicy::class,
        SurveyCampaign::class => SurveyCampaignPolicy::class,
        SurveyInvitation::class => SurveyInvitationPolicy::class,
        SurveyResponse::class => SurveyResponsePolicy::class,
        FeedbackItem::class => FeedbackItemPolicy::class,
    ];

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
